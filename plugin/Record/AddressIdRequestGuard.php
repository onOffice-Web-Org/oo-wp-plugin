<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare (strict_types=1);

namespace onOffice\WPlugin\Record;

use DI\Container;
use DI\ContainerBuilder;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Controller\AddressDetailUrl;
use onOffice\WPlugin\Factory\AddressListFactory;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Controller\Redirector\AddressRedirector;
use onOffice\WPlugin\AddressList;

/**
 *
 * Checks if an address ID exists
 *
 */

class AddressIdRequestGuard
{
	/** @var AddressListFactory */
	private $_pAddressDetailFactory;

	/** * @var ArrayContainerEscape */
	private $_addressData;

	/** @var array */
	private $_addressDataWPML = [];

	/** @var Container */
	private $_pContainer = null;

	/** @var array */
	private $_activeLanguages = [];

	/**
	 *
	 * @param AddressListFactory $pAddressDetailFactory
	 * @param Container|null $pContainer
	 *
	 */

	public function __construct(AddressListFactory $pAddressDetailFactory, Container $pContainer = null)
	{
		$this->_pAddressDetailFactory = $pAddressDetailFactory;
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainer ?? $pContainerBuilder->build();
	}


	/**
	 * @param int $addressId
	 * @return bool
	 */

	public function isValid(int $addressId): bool
	{
		$pAddressDetail = $this->_pAddressDetailFactory->createAddressDetail($addressId);
		$pAddressDetail->loadSingleAddress($addressId);

		$this->_addressData = $pAddressDetail->getRows()[ $addressId ];

		if ($this->isActiveWPML()) {
			$this->_activeLanguages = apply_filters('wpml_active_languages', null);
			if ($addressId > 0 && !empty($this->_addressData) && count($this->_activeLanguages) > 1) {
				$this->addressDataForWPML($addressId);
			}
		}

		return $this->_addressData !== false;
	}


	/**
	 *
	 * @param int $addressId
	 * @param AddressRedirector $pRedirector
	 * @param bool $pAddressRedirection
	 *
	 * @return void
	 */

	public function addressDetailUrlChecker(int $addressId, AddressRedirector $pRedirector, bool $pAddressRedirection)
	{
		$firstName = $this->_addressData->getValue('Vorname');
		$lastName = $this->_addressData->getValue('Name');
		$company = $this->_addressData->getValue('Zusatz1');
		$addressTitle = AddressList::createAddressTitle($firstName, $lastName, $company);
		$pRedirector->redirectDetailView($addressId, $addressTitle, $pAddressRedirection);
	}

	/**
	 * @param string $url
	 * @param int $addressId
	 * @param AddressDetailUrl $pAddressDetailUrl
	 * @param string $oldUrl
	 * @param string $switchLocale
	 *
	 * @return string
	 */
	public function createAddressDetailLinkForSwitchLanguageWPML(string $url, int $addressId, AddressDetailUrl $pAddressDetailUrl, string $oldUrl, string $switchLocale): string
	{
		$addressDetailTitle = '';
		$currentLocale = get_locale();
		if ($addressId > 0 && !empty($this->_addressData)) {
			if ($switchLocale !== $currentLocale) {
				$this->addSwitchFilterToLocaleHook($switchLocale);
				$addressDetailTitle = $this->_addressDataWPML[ $switchLocale ];
			} else {
				$firstName = $this->_addressData->getValue('Vorname');
				$lastName = $this->_addressData->getValue('Name');
				$company = $this->_addressData->getValue('Zusatz1');
				$addressDetailTitle = AddressList::createAddressTitle($firstName, $lastName, $company);
			}
		}
		$detailLinkForWPML = $pAddressDetailUrl->createAddressDetailLink($url, $addressId, $addressDetailTitle, $oldUrl, true);
		$this->addSwitchFilterToLocaleHook($currentLocale);

		return $detailLinkForWPML;
	}

	/**
	 * @param array $locales
	 * @param int $addressId
	 * @return array
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	private function getAddressTitleByLocales(array $locales, int $addressId): array
	{
		$pApiClientActionClone = null;
		$addressTitleByLocales = [];
		$listRequestInQueue = [];

		$pSDKWrapper = $this->_pContainer->get(SDKWrapper::class);
		$pApiClientAction = new APIClientActionGeneric
		($pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'address');
		foreach ($locales as $locale) {
			$isoLanguageCode = Language::LOCALE_MAPPING[ $locale ] ?? 'DEU';
			$addressParameters = [
				'data' => ['Vorname', 'Name', 'Zusatz1'],
				'outputlanguage' => $isoLanguageCode
			];
			$pApiClientActionClone = clone $pApiClientAction;
			$pApiClientActionClone->setResourceId((string) $addressId);
			$pApiClientActionClone->setParameters($addressParameters);
			$pApiClientActionClone->addRequestToQueue();
			$listRequestInQueue[ $locale ] = $pApiClientActionClone;
		}
		$pApiClientActionClone->sendRequests();

		if (empty($pApiClientActionClone->getResultRecords())) {
			return [];
		}

		foreach ($listRequestInQueue as $key => $pApiClientAction) {
			$firstName = $pApiClientAction->getResultRecords()[0]['elements']['Vorname'];
			$lastName = $pApiClientAction->getResultRecords()[0]['elements']['Name'];
			$company = $pApiClientAction->getResultRecords()[0]['elements']['Zusatz1'];
			$addressTitleByLocales[ $key ] = AddressList::createAddressTitle($firstName, $lastName, $company);
		}

		return $addressTitleByLocales;
	}

	/**
	 * @param int $addressId
	 * @return void
	 */
	private function addressDataForWPML(int $addressId)
	{
		$defaultLocales = [];
		foreach ($this->_activeLanguages as $language) {
			if (isset($language['default_locale']) && $language['default_locale'] !== get_locale()) {
				$defaultLocales[] = $language['default_locale'];
			}
		}

		$this->_addressDataWPML = $this->getAddressTitleByLocales($defaultLocales, $addressId);
	}

	/**
	 * @return bool
	 */
	private function isActiveWPML(): bool
	{
		return in_array('sitepress-multilingual-cms/sitepress.php', get_option('active_plugins'));
	}

	/**
	 * @param string $locale
	 */
	private function addSwitchFilterToLocaleHook(string $locale)
	{
		add_filter('locale', function () use ($locale) {
			return $locale;
		});
	}
}
