<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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
use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Controller\Redirector\EstateRedirector;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;

/**
 *
 * Checks if an estate ID exists
 *
 */

class EstateIdRequestGuard
{
	/** @var EstateListFactory */
	private $_pEstateDetailFactory;

	/** * @var ArrayContainerEscape */
	private $_estateData;

	/** @var array */
	private $_estateDataWPML = [];

	/** @var Container */
	private $_pContainer = null;

	/** @var array  */
	private $_activeLanguages = [];

	/**
	 *
	 * @param EstateListFactory $pEstateDetailFactory
	 * @param Container|null $pContainer
	 *
	 */

	public function __construct(EstateListFactory $pEstateDetailFactory, Container $pContainer = null)
	{
		$this->_pEstateDetailFactory = $pEstateDetailFactory;
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainer ?? $pContainerBuilder->build();
	}


	/**
	 *
	 * @param int $estateId
	 * @return bool
	 *
	 */

	public function isValid(int $estateId): bool
	{
		$pEstateDetail = $this->_pEstateDetailFactory->createEstateDetail($estateId);
		$pEstateDetail->loadEstates();
		$this->_estateData = $pEstateDetail->estateIterator(EstateViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT, true);

		if ($this->isActiveWPML()) {
			$this->_activeLanguages = apply_filters('wpml_active_languages', null);
			if ($estateId > 0 && !empty($this->_estateData) && count($this->_activeLanguages) > 1) {
				$this->estateDataForWPML($estateId);
			}
		}

		return $this->_estateData !== false;
	}


	/**
	 *
	 * @param  int  $estateId
	 * @param  EstateRedirector  $pEstateRedirector
	 * @param bool $pEstateRedirection
	 *
	 * @return void
	 */

	public function estateDetailUrlChecker(int $estateId, EstateRedirector $pEstateRedirector, bool $pEstateRedirection ) {
		$estateTitle = $this->_estateData->getValue( 'objekttitel' );
		$pEstateRedirector->redirectDetailView($estateId, $estateTitle, $pEstateRedirection);
	}

	/**
	 * @param string $url
	 * @param int $estateId
	 * @param EstateDetailUrl $pEstateDetailUrl
	 * @param string $oldUrl
	 * @param string $switchLocale
	 *
	 * @return string
	 */
	public function createEstateDetailLinkForSwitchLanguageWPML(string $url, int $estateId, EstateDetailUrl $pEstateDetailUrl, string $oldUrl, string $switchLocale): string
	{
		$estateDetailTitle = '';
		$currentLocale = get_locale();
		if ($estateId > 0 && !empty($this->_estateData)) {
			if ($switchLocale !== $currentLocale) {
				$this->addSwitchFilterToLocaleHook($switchLocale);
				$estateDetailTitle = $this->_estateDataWPML[$switchLocale];
			} else {
				$estateDetailTitle = $this->_estateData->getValue('objekttitel');
			}
		}
		$detailLinkForWPML = $pEstateDetailUrl->createEstateDetailLink($url, $estateId, $estateDetailTitle, $oldUrl, true, $switchLocale);
		$this->addSwitchFilterToLocaleHook($currentLocale);

		return $detailLinkForWPML;
	}

	/**
	 * @param array $locales
	 * @param int $estateId
	 * @return array
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	private function getEstateTitleByLocales(array $locales, int $estateId): array
	{
		$pApiClientActionClone = null;
		$estateTitleByLocales = [];
		$listRequestInQueue = [];

		$pSDKWrapper = $this->_pContainer->get(SDKWrapper::class);
		$pApiClientAction = new APIClientActionGeneric
		($pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate');
		foreach ($locales as $locale) {
			$isoLanguageCode = Language::LOCALE_MAPPING[$locale] ?? 'DEU';
			$estateParameters = [
				'data' => ['objekttitel'],
				'estatelanguage' => $isoLanguageCode,
				'outputlanguage' => $isoLanguageCode,
			];
			$pApiClientActionClone = clone $pApiClientAction;
			$pApiClientActionClone->setResourceId((string)$estateId);
			$pApiClientActionClone->setParameters($estateParameters);
			$pApiClientActionClone->addRequestToQueue();
			$listRequestInQueue[$locale] = $pApiClientActionClone;
		}
		$pApiClientActionClone->sendRequests();

		if (empty($pApiClientActionClone->getResultRecords())) {
			return [];
		}

		foreach($listRequestInQueue as $key => $pApiClientAction) {
			$estateTitleByLocales[$key] = $pApiClientAction->getResultRecords()[0]['elements']['objekttitel'];
		}

		return $estateTitleByLocales;
	}

	/**
	 * @param int $estateId
	 * @return void
	 */
	private function estateDataForWPML(int $estateId)
	{
		$defaultLocales = [];
		foreach ($this->_activeLanguages as $language) {
			if (isset($language['default_locale']) && $language['default_locale'] !== get_locale()) {
				$defaultLocales[] = $language['default_locale'];
			}
		}

		$this->_estateDataWPML = $this->getEstateTitleByLocales($defaultLocales, $estateId);
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
