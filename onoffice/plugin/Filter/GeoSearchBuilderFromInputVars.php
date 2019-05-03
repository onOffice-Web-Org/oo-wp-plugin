<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Controller\GeoPositionFieldHandler;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Controller\ViewProperty;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class GeoSearchBuilderFromInputVars
	implements GeoSearchBuilder
{
	/** @var GeoPositionFieldHandler */
	private $_pGeoPositionFieldHandler = null;

	/** @var InputVariableReader */
	private $_pEstateListInputVariableReader = null;

	/** @var APIClientActionGeneric */
	private $_pAPIClientActionGeneric = null;

	/** @var ViewProperty */
	private $_pView = null;


	/**
	 *
	 * @param InputVariableReader $pEstateListInputVariableReader
	 *
	 */

	public function __construct(
		InputVariableReader $pEstateListInputVariableReader = null,
		GeoPositionFieldHandler $pGeoPositionFieldHandler = null,
		APIClientActionGeneric $pAPIClientActionGeneric = null)
	{
		$this->_pGeoPositionFieldHandler = $pGeoPositionFieldHandler;
		$this->_pEstateListInputVariableReader = $pEstateListInputVariableReader ??
			new InputVariableReader(onOfficeSDK::MODULE_ESTATE);
		$this->_pGeoPositionFieldHandler = $pGeoPositionFieldHandler ?? new GeoPositionFieldHandler();
		$this->_pAPIClientActionGeneric = $pAPIClientActionGeneric ?? new APIClientActionGeneric
			(new SDKWrapper(), '', '');
	}


	/**
	 *
	 * @param array $inputs
	 * @return array
	 *
	 */

	private function createGeoRangeSearchParameterRequest(array $inputs): array
	{
		$radius = $inputs[GeoPosition::ESTATE_LIST_SEARCH_RADIUS] ??
			$this->_pGeoPositionFieldHandler->getRadiusValue();
		$inputs[GeoPosition::ESTATE_LIST_SEARCH_RADIUS] = $radius;
		$country = $inputs[GeoPosition::ESTATE_LIST_SEARCH_COUNTRY] ??
			$this->readDefaultCountryValue();
		$inputs[GeoPosition::ESTATE_LIST_SEARCH_COUNTRY] = $country;

		if (empty($inputs[GeoPosition::ESTATE_LIST_SEARCH_COUNTRY]) ||
			(empty($inputs[GeoPosition::ESTATE_LIST_SEARCH_CITY]) &&
			 empty($inputs[GeoPosition::ESTATE_LIST_SEARCH_ZIP]))) {
			return [];
		}

		return $inputs;
	}


	/**
	 *
	 * @return array
	 * @throws Exception
	 *
	 */

	public function buildParameters(): array
	{
		if ($this->_pView === null) {
			throw new Exception('pView cannot be null');
		}

		$this->_pGeoPositionFieldHandler->readValues($this->_pView);
		$geoInputValues = $this->getGeoSearchValues();
		$requestGeoSearchParameters = $this->createGeoRangeSearchParameterRequest($geoInputValues);

		return $requestGeoSearchParameters;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getGeoSearchValues(): array
	{
		$inputValues = $this->_pGeoPositionFieldHandler->getActiveFieldsWithValue();
		array_walk($inputValues, function(&$value, $key) {
			$value = $this->_pEstateListInputVariableReader->getFieldValue($key) ?? $value;
		});

		return $inputValues;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function readDefaultCountryValue(): string
	{
		$pApiClientAction = $this->_pAPIClientActionGeneric->withActionIdAndResourceType
			(onOfficeSDK::ACTION_ID_READ, 'impressum');
		$pApiClientAction->setParameters([
			'formatoutput' => false,
			'data' => ['country'],
		]);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		return $pApiClientAction->getResultRecords()[0]['elements']['country'];
	}


	/** @param ViewProperty $pViewProperty */
	public function setViewProperty(ViewProperty $pViewProperty)
		{ $this->_pView = $pViewProperty; }
}