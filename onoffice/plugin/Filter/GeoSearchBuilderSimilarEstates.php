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

namespace onOffice\WPlugin\Filter;

use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\Types\GeoCoordinates;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class GeoSearchBuilderSimilarEstates
	implements GeoSearchBuilder
{
	/** @var FilterConfigurationSimilarEstates */
	private $_pDataViewSimilarEstates = null;


	/**
	 *
	 * @param FilterConfigurationSimilarEstates $pFilterConfiguration
	 *
	 */

	public function __construct(FilterConfigurationSimilarEstates $pFilterConfiguration)
	{
		$this->_pDataViewSimilarEstates = $pFilterConfiguration;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function buildParameters(): array
	{
		$pFilterConfiguration = $this->_pDataViewSimilarEstates;
		$pGeoCoordinates = $pFilterConfiguration->getGeoCoordinates();
		$parameters = [];

		if ($pFilterConfiguration->getRadius() > 0) {
			$street = $pFilterConfiguration->getStreet();
			$postalCode = $pFilterConfiguration->getPostalCode();

			if ($pGeoCoordinates->isValid()) {
				$parameters = $this->buildParametersByCoordinates($pGeoCoordinates);
			} elseif ($street !== '' && $postalCode !== '') {
				$parameters = $this->buildParametersByAddress();
			}
		}
		return $parameters;
	}


	/**
	 *
	 * @param GeoCoordinates $pGeoCoordinates
	 * @return array
	 *
	 */

	private function buildParametersByCoordinates(GeoCoordinates $pGeoCoordinates): array
	{
		return [
			'latitude' => $pGeoCoordinates->getLatitude(),
			'longitude' => $pGeoCoordinates->getLongitude(),
			'radius' => $this->_pDataViewSimilarEstates->getRadius(),
		];
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function buildParametersByAddress(): array
	{
		$pFilterConfiguration = $this->_pDataViewSimilarEstates;
		$parameters = [
			'street' => $pFilterConfiguration->getStreet(),
			'zip' => $pFilterConfiguration->getPostalCode(),
			'radius' => $pFilterConfiguration->getRadius(),
		];

		if ($pFilterConfiguration->getCountry() !== '') {
			$parameters['country'] = $pFilterConfiguration->getCountry();
		}

		return $parameters;
	}


	/** @return DataViewSimilarEstates */
	public function getDataViewSimilarEstates(): FilterConfigurationSimilarEstates
		{ return $this->_pDataViewSimilarEstates; }
}
