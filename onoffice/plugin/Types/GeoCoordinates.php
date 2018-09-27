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


namespace onOffice\WPlugin\Types;

/**
 *
 * Type containing tuple of longitude and latitude
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class GeoCoordinates
{
	/** @var float */
	private $_latitude = .0;

	/** @var float */
	private $_longitude = .0;

	/** @var bool */
	private $_valid = false;


	/**
	 *
	 * @param float $latitude
	 * @param float $longitude
	 *
	 */

	public function __construct(float $latitude = null, float $longitude = null)
	{
		$validFloats = $latitude !== null && $longitude !== null;
		$this->_latitude = $latitude ?? .0;
		$this->_longitude = $longitude ?? .0;
		$this->_valid = $validFloats && $this->checkRangeOfLongLat();
	}


	/**
	 *
	 * @return bool
	 *
	 */

	private function checkRangeOfLongLat(): bool
	{
		return abs($this->_latitude) <= 90. &&
			abs($this->_longitude) <= 180.;
	}


	/**
	 *
	 * @return float
	 *
	 */

	public function getLatitude(): float
	{
		$this->exceptionOnInvalidity();
		return $this->_latitude;
	}


	/**
	 *
	 * @return float
	 *
	 */

	public function getLongitude(): float
	{
		$this->exceptionOnInvalidity();
		return $this->_longitude;
	}


	/**
	 *
	 * @throws InvalidGeoCoordinatesException
	 *
	 */

	private function exceptionOnInvalidity()
	{
		if (!$this->_valid) {
			throw new InvalidGeoCoordinatesException;
		}
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function isValid(): bool
	{
		return $this->_valid;
	}
}
