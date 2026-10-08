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
 */
class EstateCityValuesMapper
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	/** @var string */
	private $_pShowReferenceEstate = '';

	/** @var int */
	private $_filterId = 0;

	/** @var string */
	private $_activeLanguage = '';

	public function __construct(
		SDKWrapper $pSDKWrapper = null,
		string $pShowReferenceEstate = '',
		int $filterId = 0,
		string $activeLanguage = ''
	) {
		$this->_pSDKWrapper = $pSDKWrapper ?? new SDKWrapper();
		$this->_pShowReferenceEstate = $pShowReferenceEstate;
		$this->_filterId = $filterId;
		$this->_activeLanguage = $activeLanguage;
	}

	public function getMainLanguageCityValues(array $localizedCities): array
	{
		if ($localizedCities === []) {
			return [];
		}

		$mapping = $this->buildMapping($localizedCities);
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

	private function buildMapping(array $localizedCities): array
	{
		$currentLanguageRecords = $this->fetchEstatesByCity($localizedCities, $this->getActiveLanguage());

		if ($currentLanguageRecords === []) {
			return [];
		}

		$mainIdsToFetch = [];
		$localizedOrtById = [];

		foreach ($currentLanguageRecords as $record) {
			$localizedOrt = $record['elements']['ort'] ?? '';
			if ($localizedOrt === '') {
				continue;
			}

			$mainId = $record['elements']['mainLangId'] ?? $record['id'];
			$mainIdsToFetch[$mainId] = true;
			$localizedOrtById[$mainId][] = $localizedOrt;
		}

		if ($mainIdsToFetch === []) {
			return [];
		}

		$mainLanguageRecords = $this->fetchEstatesById(array_keys($mainIdsToFetch), null);

		$mapping = [];
		foreach ($mainLanguageRecords as $record) {
			if (isset($record['id'], $record['elements']['ort'])) {
				$mainOrt = $record['elements']['ort'];
				$mainId = $record['id'];

				if (isset($localizedOrtById[$mainId])) {
					foreach ($localizedOrtById[$mainId] as $locOrt) {
						$mapping[$locOrt][$mainOrt] = $mainOrt;
					}
				}
			}
		}

		return array_map('array_values', $mapping);
	}

	private function fetchEstatesByCity(array $cities, ?string $language): array
	{
		$filter = ['ort' => [['op' => 'in', 'val' => $cities]]];
		return $this->executeReadAction($language, $filter);
	}

	private function fetchEstatesById(array $ids, ?string $language): array
	{
		$filter = ['Id' => [['op' => 'in', 'val' => $ids]]];
		return $this->executeReadAction($language, $filter);
	}

	private function executeReadAction(?string $language, array $additionalFilter): array
	{
		$pAction = $this->queueReadEstatesAction($language, 0, $additionalFilter);
		$this->_pSDKWrapper->sendRequests();

		$records = $pAction->getResultRecords();
		$total = $pAction->getResultMeta()['cntabsolute'] ?? count($records);
		$total = is_array($total) ? ($total[0] ?? count($records)) : $total;

		$allRecords = $records;
		$additionalActions = [];

		for ($offset = 500; $offset < (int)$total; $offset += 500) {
			$additionalActions[] = $this->queueReadEstatesAction($language, $offset, $additionalFilter);
		}

		if ($additionalActions !== []) {
			$this->_pSDKWrapper->sendRequests();
			foreach ($additionalActions as $pPageAction) {
				$allRecords = array_merge($allRecords, $pPageAction->getResultRecords());
			}
		}

		return $allRecords;
	}

	private function queueReadEstatesAction(?string $language, int $offset = 0, array $additionalFilter = []): APIClientActionGeneric
	{
		$requestParams = [
			'data' => ['ort', 'Id'],
			'listlimit' => 500,
			'addMainLangId' => true,
			'sortby' => 'Id',
			'sortorder' => 'ASC',
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

		if ($additionalFilter !== []) {
			foreach ($additionalFilter as $key => $filterData) {
				$requestParams['filter'][$key] = $filterData;
			}
		}

		if ($this->_filterId !== 0) {
			$requestParams['filterid'] = $this->_filterId;
		}
		if ($offset !== 0) {
			$requestParams['listoffset'] = $offset;
		}

		$pApiClientAction = new APIClientActionGeneric(
			$this->_pSDKWrapper, 
			onOfficeSDK::ACTION_ID_READ, 
			'estate'
		);
		
		$pApiClientAction->setParameters($requestParams);
		return $pApiClientAction->addRequestToQueue();
	}

	private function getActiveLanguage(): string
	{
		return $this->_activeLanguage !== '' ? $this->_activeLanguage : Language::getDefault();
	}
}