<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\GeoPosition;


class TestClassGeoPosition
	extends WP_UnitTestCase
{


	/**
	 *
	 */

	public function testTransform()
	{
		$pGeoPosition = $this->getGeoPosition();

		$values1 = array
				(
					'laengengrad' => array
					(
						"type" =>  "float",
						"length" =>  NULL,
						"permittedvalues" =>  NULL,
						"default" =>  NULL,
						"label" =>  "Längengrad",
						"tablename" =>  "ObjGeo",
						"content" =>   "Geografische-Angaben",
						"module" => "estate",
					),
					'breitengrad' => array
					(
						"type" =>  "float",
						"length" =>  NULL,
						"permittedvalues" =>  NULL,
						"default" =>  NULL,
						"label" =>  "Breitengrad",
						"tablename" =>  "ObjGeo",
						"content" =>   "Geografische-Angaben",
						"module" => "estate",
					),
				);

		$values2 = array
				(
					'range_land' => array
						(
							"type" => FieldTypes::FIELD_TYPE_SINGLESELECT,
							"label" => "Land",
							"default" => NULL,
							"permittedValues" => [],
							"content" => "Search Criteria",
							"module" => "searchcriteria",
						),
					'range_plz' => array
						(
							"type" => FieldTypes::FIELD_TYPE_VARCHAR,
							"label" => "PLZ",
							"default" => NULL,
							"permittedValues" => [],
							"content" => "Search Criteria",
							"module" => "searchcriteria",
						),
					'range_strasse' => array
						(
							"type" => FieldTypes::FIELD_TYPE_VARCHAR,
							"label" => "Straße",
							"default" => NULL,
							"permittedValues" => [],
							"content" => "Search Criteria",
							"module" => "searchcriteria",
						),
					'range' => array
						(
							"type" => FieldTypes::FIELD_TYPE_VARCHAR,
							"label" => "Umkreis (km)",
							"default" => NULL,
							"permittedValues" => [],
							"content" => "Search Criteria",
							"module" => "searchcriteria",
						),
				);

		$result1 = $pGeoPosition->transform($values1, 'adminInterface');
		$expectedResult1 = array('geoPosition' => array
			(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Geo Position',
				'content' => 'Geografische-Angaben',
			));

		foreach ($expectedResult1 as $key => $values) {
			$this->assertArrayHasKey($key, $result1);
			$result1Values = $result1[$key];

			foreach ($values as $keyOfValue => $value) {
				$this->assertEquals($value, $result1Values[$keyOfValue]);
			}
		}


		$result2 = $pGeoPosition->transform($values2, 'adminSearchCriteria');
		$expectedResult2 = array('geoPosition' => array
			(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => [],
				'default' => null,
				'label' => 'Geo Position',
				'content' => 'Search Criteria',
			));


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

		$expectedValue = array(
			'country',
			'zip',
			'street',
			'radius',
		);

		foreach ($expectedValue as $key => $value) {
			$this->assertEquals($value, $result[$key]);
		}
	}


	/**
	 *
	 */

	public function testCreateGeoRangeSearchParameterRequest()
	{
		$values =
		[
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