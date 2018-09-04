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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\GeoPosition;


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
	 */

	public function testTransform()
	{
		$pGeoPosition = $this->getGeoPosition();

		$values1 = [
			'laengengrad' => [
				'type' => 'float',
				'length' => null,
				'permittedvalues' => null,
				'default' => null,
				'label' => 'Längengrad',
				'tablename' => 'ObjGeo',
				'content' => 'Geografische-Angaben',
				'module' => onOfficeSDK::MODULE_ESTATE,
			],
			'breitengrad' => [
				'type' => 'float',
				'length' => null,
				'permittedvalues' => null,
				'default' => null,
				'label' => 'Breitengrad',
				'tablename' => 'ObjGeo',
				'content' => 'Geografische-Angaben',
				'module' => onOfficeSDK::MODULE_ESTATE,
			],
		];

		$values2 = [
			'range_land' => [
				'type' => FieldTypes::FIELD_TYPE_SINGLESELECT,
				'label' => 'Land',
				'default' => null,
				'permittedValues' => [],
				'content' => 'Search Criteria',
				'module' => 'searchcriteria',
			],
			'range_plz' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'label' => 'PLZ',
				'default' => null,
				'permittedValues' => [],
				'content' => 'Search Criteria',
				'module' => 'searchcriteria',
			],
			'range_strasse' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'label' => 'Straße',
				'default' => null,
				'permittedValues' => [],
				'content' => 'Search Criteria',
				'module' => 'searchcriteria',
			],
			'range' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'label' => 'Umkreis (km)',
				'default' => null,
				'permittedValues' => [],
				'content' => 'Search Criteria',
				'module' => 'searchcriteria',
			],
		];

		$result1 = $pGeoPosition->transform($values1, 'adminInterface');
		$expectedResult1 = [
			'geoPosition' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Geo Position',
				'content' => 'Geografische-Angaben',
			],
		];

		foreach ($expectedResult1 as $key => $values) {
			$this->assertArrayHasKey($key, $result1);
			$result1Values = $result1[$key];

			foreach ($values as $keyOfValue => $value) {
				$this->assertEquals($value, $result1Values[$keyOfValue]);
			}
		}


		$result2 = $pGeoPosition->transform($values2, 'adminSearchCriteria');
		$expectedResult2 = [
			'geoPosition' => [
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Geo Position',
				'content' => 'Search Criteria',
			],
		];


		foreach ($expectedResult2 as $key => $values) {
			$this->assertArrayHasKey($key, $result2);
			$result2Values = $result2[$key];

			foreach ($values as $keyOfValue => $value) {
				$this->assertEquals($value, $result2Values[$keyOfValue]);
			}
		}
	}


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