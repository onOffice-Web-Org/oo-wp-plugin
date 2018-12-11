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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\GeoPosition;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class GeoSearchBuilderFromInputVars
	implements GeoSearchBuilder
{
	/** @var InputVariableReader */
	private $_pEstateListInputVariableReader = null;


	/**
	 *
	 * @param InputVariableReader $pEstateListInputVariableReader
	 *
	 */

	public function __construct(InputVariableReader $pEstateListInputVariableReader = null)
	{
		$this->_pEstateListInputVariableReader = $pEstateListInputVariableReader ??
			new InputVariableReader(onOfficeSDK::MODULE_ESTATE);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function buildParameters(): array
	{
		$pGeoPosition = new GeoPosition();
		$geoInputValues = $this->getGeoSearchValues();

		$requestGeoSearchParameters = $pGeoPosition->createGeoRangeSearchParameterRequest
			($geoInputValues);

		return $requestGeoSearchParameters;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getGeoSearchValues(): array
	{
		$inputValues = [];
		$pGeoPosition = new GeoPosition();

		foreach ($pGeoPosition->getEstateSearchFields() as $key) {
			$inputValues[$key] = $this->_pEstateListInputVariableReader->getFieldValue
				($key, FILTER_SANITIZE_STRING, FILTER_FORCE_ARRAY);
		}

		return $inputValues;
	}
}
