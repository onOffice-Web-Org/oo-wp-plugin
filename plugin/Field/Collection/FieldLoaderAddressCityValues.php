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

namespace onOffice\WPlugin\Field\Collection;

use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\API\APIEmptyResultException;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */

class FieldLoaderAddressCityValues
	implements FieldLoader
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	/**
	 * @param SDKWrapper $pSDKWrapper
	 */

	public function __construct(SDKWrapper $pSDKWrapper) {
		$this->_pSDKWrapper = $pSDKWrapper;
	}


	/**
	 * @return Generator
	 * @throws APIEmptyResultException
	 * @throws ApiClientException
	 */

	public function load(): Generator
	{
		$cityField = $this->sendRequest();
		$listCityName = $this->getListNameCity();

		foreach ($cityField as $fieldName => $fieldProperties) {
			if ($fieldName === 'Ort') {
				$fieldProperties['type'] = FieldTypes::FIELD_TYPE_MULTISELECT;
				$fieldProperties['permittedvalues'] = $listCityName;
				yield $fieldName => $fieldProperties;
			}
		}
	}


	/**
	 * @return array
	 * @throws ApiClientException
	 */

	private function getListNameCity(): array
	{
		$aggregatedData = [];
		do {
			$requestParams = [
				'data' => ['Ort'],
				'listlimit' => 500,
			];
			$requestParams['filter']['homepage_veroeffentlichen'][] = ['op' => '=', 'val' => 1];

			$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, onOfficeSDK::MODULE_ADDRESS);
			$pApiClientAction->setParameters($requestParams);
			$pApiClientAction->addRequestToQueue()->sendRequests();
			$result = $pApiClientAction->getResultRecords();

			$aggregatedData = array_merge($aggregatedData, $result);
		} while (count($result) == 500);

		if (empty($result)) {
			return [];
		}

		$listCityName = [];
		foreach ($aggregatedData as $value) {
			if(!empty($value['elements']['Ort'])){
				$listCityName[$value['elements']['Ort']] = $value['elements']['Ort'];
			}
		}

		if (empty($listCityName)) {
			return [];
		}

		return ($listCityName);
	}


	/**
	 * @return array
	 * @throws ApiClientException
	 */

	private function sendRequest(): array
	{
		$fieldListParameters = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'language' => Language::getDefault(),
			'modules' => [onOfficeSDK::MODULE_ADDRESS],
			'realDataTypes' => true
		];


		$pApiClientAction = new APIClientActionGeneric
		($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'fields');
		$pApiClientAction->setParameters($fieldListParameters);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$result = $pApiClientAction->getResultRecords();

		if (empty($result)) {
			return [];
		}

		return !empty($result[0]['elements']) ? $result[0]['elements'] : [];
	}
}