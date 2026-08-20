<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\WPlugin\Filter;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;

/**
 * Maps localized city names (e.g. the German "Gufidaun") to the main-language
 * ort values the API can actually filter on (e.g. "Gudon").
 *
 * The onOffice API matches the `ort` filter against the MAIN-language record
 * of an estate only, regardless of the requested `estatelanguage`. The search
 * form dropdown is built from estates in the current language, so its values
 * may not match any main-language ort. This class resolves that mismatch.
 */
class EstateCityValuesMapper
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	/** @var string */
	private $_pShowReferenceEstate = '';

	public function __construct(SDKWrapper $pSDKWrapper = null, string $pShowReferenceEstate = '')
	{
		$this->_pSDKWrapper = $pSDKWrapper ?? new SDKWrapper();
		$this->_pShowReferenceEstate = $pShowReferenceEstate;
	}

	/**
	 * Expands submitted (localized) city names to the set of main-language
	 * orts that match them. Values that cannot be mapped are kept unchanged.
	 *
	 * @param string[] $localizedCities
	 * @return string[]
	 * @throws ApiClientException
	 */
	public function getMainLanguageCityValues(array $localizedCities): array
	{
		if ($localizedCities === []) {
			return [];
		}

		$mapping = $this->buildMapping();
		$result = [];

		foreach ($localizedCities as $city) {
			if (isset($mapping[$city])) {
				$result = array_merge($result, $mapping[$city]);
			} else {
				$result[] = $city;
			}
		}

		return array_values(array_unique($result));
	}

	/**
	 * @return array<string, string[]>  localized ort => [main-language orts]
	 * @throws ApiClientException
	 */
	private function buildMapping(): array
	{
		$currentLanguageRecords = $this->readEstates(Language::getDefault());
		$mainLanguageRecords = $this->readEstates(null);

		$mainOrtById = [];
		foreach ($mainLanguageRecords as $record) {
			$mainOrtById[$record['id']] = $record['elements']['ort'];
		}

		$mapping = [];
		foreach ($currentLanguageRecords as $record) {
			$localizedOrt = $record['elements']['ort'];
			if ($localizedOrt === '') {
				continue;
			}

			$mainId = $record['elements']['mainLangId'] ?? $record['id'];
			$mainOrt = $mainOrtById[$mainId] ?? $localizedOrt;
			$mapping[$localizedOrt][$mainOrt] = $mainOrt;
		}

		return array_map('array_values', $mapping);
	}

	/**
	 * @param string|null $language null = main language (no estatelanguage)
	 * @return array
	 * @throws ApiClientException
	 */
	private function readEstates(?string $language): array
	{
		$requestParams = [
			'data' => ['ort', 'Id'],
			'listlimit' => 500,
			'addMainLangId' => true,
		];

		if ($language !== null) {
			$requestParams['estatelanguage'] = $language;
		}

		if ($this->_pShowReferenceEstate === DataListView::HIDE_REFERENCE_ESTATE) {
			$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 0];
		} elseif ($this->_pShowReferenceEstate === DataListView::SHOW_ONLY_REFERENCE_ESTATE) {
			$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 1];
		}
		$requestParams['filter']['veroeffentlichen'][] = ['op' => '=', 'val' => 1];

		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();

		return $pApiClientAction->getResultRecords();
	}
}