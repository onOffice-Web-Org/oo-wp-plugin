<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */

class FieldLoaderEstateCityValues
	implements FieldLoader
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	private $_pShowReferenceEstate = '';

	/**
	 * @param SDKWrapper $pSDKWrapper
	 * @param string $pShowReferenceEstate
	 */

	public function __construct(SDKWrapper $pSDKWrapper, string $pShowReferenceEstate) {
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->_pShowReferenceEstate = $pShowReferenceEstate;
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
			if ($fieldName === 'ort') {
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
		$requestParams = [
			'data' => ['ort'],
			'listlimit' => 500,
		];

		if ($this->_pShowReferenceEstate === DataListView::HIDE_REFERENCE_ESTATE) {
			$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 0];
		} elseif ($this->_pShowReferenceEstate === DataListView::SHOW_ONLY_REFERENCE_ESTATE) {
			$requestParams['filter']['referenz'][] = ['op' => '=', 'val' => 1];
		}

		$pApiClientAction = new APIClientActionGeneric
		($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'estate');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$result = $pApiClientAction->getResultRecords();

		if (empty($result)) {
			return [];
		}

		$listCityName = [];
		foreach ($result as $value) {
			if(!empty($value['elements']['ort'])){
				$listCityName[] = $value['elements']['ort'];
			}
		}

		return array_unique($listCityName);
	}


	/**
	 * @return array
	 * @throws ApiClientException
	 */

	private function sendRequest(): array
	{
		$parametersGetFieldList = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'fieldList' => ['ort'],
			'language' => Language::getDefault(),
			'modules' => [onOfficeSDK::MODULE_ESTATE],
			'realDataTypes' => true
		];

		$pApiClientAction = new APIClientActionGeneric
		($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'fields');
		$pApiClientAction->setParameters($parametersGetFieldList);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$result = $pApiClientAction->getResultRecords();

		if (empty($result[0]['elements'])) {
			return [];
		}
		return $result[0]['elements'];
	}
}