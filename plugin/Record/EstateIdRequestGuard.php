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
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\Utility\Redirector;

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
	private $activeLanguages = [];

	/**
	 *
	 * @param EstateListFactory $pEstateDetailFactory
	 *
	 */

	public function __construct(EstateListFactory $pEstateDetailFactory)
	{
		$this->_pEstateDetailFactory = $pEstateDetailFactory;
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
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
			$this->activeLanguages = apply_filters('wpml_active_languages', null);
			if ($estateId > 0 && !empty($this->_estateData) && !is_null($this->activeLanguages) && count($this->activeLanguages) > 1) {
				$this->getEstateDataForWPML($estateId);
			}
		}

		return $this->_estateData !== false;
	}


	/**
	 *
	 * @param  int  $estateId
	 * @param  Redirector  $pRedirector
	 * @param bool $pEstateRedirection
	 *
	 * @return void
	 */

	public function estateDetailUrlChecker( int $estateId, Redirector $pRedirector, bool $pEstateRedirection ) {
		$estateTitle = $this->_estateData->getValue( 'objekttitel' );
		$pRedirector->redirectDetailView($estateId, $estateTitle, $pEstateRedirection);
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
		if ($estateId > 0 && !empty($this->_estateData) && $switchLocale !== get_locale()) {
			switch_to_locale($switchLocale);
			$estateDetailTitle = $this->_estateDataWPML[$switchLocale];
		} elseif ($estateId > 0 && !empty($this->_estateData) && $switchLocale === get_locale()) {
			$estateDetailTitle = $this->_estateData->getValue('objekttitel');
		}
		$switchLanguageUrl = $pEstateDetailUrl->createEstateDetailLink($url, $estateId, $estateDetailTitle, $oldUrl, true);
		switch_to_locale($currentLocale);

		return $switchLanguageUrl;
	}

	/**
	 * @param array $languages
	 * @param int $estateId
	 * @return array
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	private function getEstateTitleByLanguage(array $languages, int $estateId): array
	{
		$pApiClientActionClone = null;
		$results = [];
		$listRequestInQueue = [];

		$pSDKWrapper = $this->_pContainer->get(SDKWrapper::class);
		$pApiClientAction = new APIClientActionGeneric
		($pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate');
		foreach ($languages as $language) {
			$isoLanguageCode = Language::LOCALE_MAPPING[$language] ?? 'DEU';
			$estateParameters = [
				'data' => ['objekttitel'],
				'estatelanguage' => $isoLanguageCode,
				'outputlanguage' => $isoLanguageCode,
			];
			$pApiClientActionClone = clone $pApiClientAction;
			$pApiClientActionClone->setResourceId((string)$estateId);
			$pApiClientActionClone->setParameters($estateParameters);
			$pApiClientActionClone->addRequestToQueue();
			$listRequestInQueue[$language] = $pApiClientActionClone;
		}
		$pApiClientActionClone->sendRequests();

		if (empty($pApiClientActionClone->getResultRecords())) {
			return [];
		}

		foreach($listRequestInQueue as $key => $pApiClientAction) {
			$results[$key] = $pApiClientAction->getResultRecords()[0]['elements']['objekttitel'];
		}

		return $results;
	}

	/**
	 * @param int $estateId
	 * @return void
	 */
	private function getEstateDataForWPML(int $estateId)
	{
		$defaultLocales = [];
		foreach ($this->activeLanguages as $language) {
			if (isset($language['default_locale']) && $language['default_locale'] !== get_locale()) {
				$defaultLocales[] = $language['default_locale'];
			}
		}

		$this->_estateDataWPML = $this->getEstateTitleByLanguage($defaultLocales, $estateId);
	}

	/**
	 * @return bool
	 */
	private function isActiveWPML(): bool
	{
		return is_plugin_active('sitepress-multilingual-cms/sitepress.php');
	}
}
