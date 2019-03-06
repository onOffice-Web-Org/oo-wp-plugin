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

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\GeoPosition;
use WP_UnitTestCase;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassGeoPosition
	extends WP_UnitTestCase
{
	/**
	 *
	 * @return GeoPosition
	 *
	 */

	public function getGeoPosition()
	{
		return new GeoPosition();
	}


	/**
	 *
	 * @covers onOffice\WPlugin\GeoPosition::getSettingsGeoPositionFields
	 *
	 */

	public function testGetSettingsGeoPositionFields()
	{
		$pGeoPosition = $this->getGeoPosition();

		$value1 = onOfficeSDK::MODULE_ESTATE;
		$result1 = $pGeoPosition->getSettingsGeoPositionFields($value1);
		$expectedValue1 = array
			(
				'laengengrad',
				'breitengrad',
			);

		foreach ($expectedValue1 as $key => $value) {
			$this->assertEquals($value, $result1[$key]);
		}


		$value2 = onOfficeSDK::MODULE_SEARCHCRITERIA;
		$result2 = $pGeoPosition->getSettingsGeoPositionFields($value2);
		$expectedValue2 = array
			(
				'range_land',
				'range_plz',
				'range_strasse',
				'range',
			);

		foreach ($expectedValue2 as $key => $value)	{
			$this->assertEquals($value, $result2[$key]);
		}
	}


	/**
	 *
	 * @covers onOffice\WPlugin\GeoPosition::getEstateSearchFields
	 *
	 */

	public function testGetEstateSearchFields()
	{
		$pGeoPosition = $this->getGeoPosition();
		$result = $pGeoPosition->getEstateSearchFields();

		$expectedValue = [
			'country',
			'zip',
			'street',
			'radius',
		];

		foreach ($expectedValue as $key => $value) {
			$this->assertEquals($value, $result[$key]);
		}
	}


	/**
	 *
	 * @covers onOffice\WPlugin\GeoPosition::createGeoRangeSearchParameterRequest
	 *
	 */

	public function testCreateGeoRangeSearchParameterRequest()
	{
		$values = [
			'country' => 'DEU',
			'zip' => '52068',
			'street' => 'Charlottenburger Allee',
			'radius' => 25,
		];

		$expectedValues = $values;

		$pGeoPosition = new GeoPosition();
		$result = $pGeoPosition->createGeoRangeSearchParameterRequest($values);

		foreach ($expectedValues as $key => $value)	{
			$this->assertEquals($value, $result[$key]);
		}
	}
}