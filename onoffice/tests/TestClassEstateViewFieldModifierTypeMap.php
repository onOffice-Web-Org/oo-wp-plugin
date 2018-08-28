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

use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeMap;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeMap
 *
 */

class TestClassEstateViewFieldModifierTypeMap
	extends WP_UnitTestCase
{
	/** @var array */
	private $_testRecord = [
		'virtualStreet' => '',
		'virtualHouseNumber' => 0,
		'laengengrad' => 12.1221222,
		'breitengrad' => 50.34343,
		'virtualAddress' => 0,
		'virtualLatitude' => 0,
		'virtualLongitude' => 0,
		'objektadresse_freigeben' => 1,
		'strasse' => 'Teststraße 1',
		'showGoogleMap' => 1,
		'hausnummer' => 5,
		'objekttitel' => 'Schöne Immobilie',
		'test1' => 'A text',
		'test2' => true,
	];


	/**
	 *
	 */

	public function testAPIFields()
	{
		$pEstateViewFieldModifierTypeMap = new EstateViewFieldModifierTypeMap(['test1', 'test2']);

		$apiFieldsExpect = [
			'test1',
			'test2',
			'virtualStreet',
			'virtualHouseNumber',
			'laengengrad',
			'breitengrad',
			'virtualAddress',
			'virtualLatitude',
			'virtualLongitude',
			'objektadresse_freigeben',
			'strasse',
			'showGoogleMap',
			'hausnummer',
			'objekttitel',
		];

		$this->assertEquals($apiFieldsExpect, $pEstateViewFieldModifierTypeMap->getAPIFields());
	}


	/**
	 *
	 */

	public function testVisibleFields()
	{
		$pEstateViewFieldModifierTypeMap = new EstateViewFieldModifierTypeMap(['test1', 'test2']);
		$this->assertEquals([
			'showGoogleMap',
			'laengengrad',
			'breitengrad',
			'virtualAddress',
			'objekttitel',
			'test1',
			'test2',
		], $pEstateViewFieldModifierTypeMap->getVisibleFields());
	}


	/**
	 *
	 */

	public function testReduceRecordEmpty()
	{
		$expectedRecord = [
			'virtualStreet' => '',
			'virtualHouseNumber' => 0,
			'laengengrad' => 12.1221222,
			'breitengrad' => 50.34343,
			'virtualAddress' => 0,
			'virtualLatitude' => 0,
			'virtualLongitude' => 0,
			'objektadresse_freigeben' => 1,
			'strasse' => 'Teststraße 1',
			'showGoogleMap' => 1,
			'hausnummer' => 5,
			'objekttitel' => 'Schöne Immobilie',
			'test1' => 'A text',
			'test2' => true,
		];

		$pEstateViewFieldModifierTypeMap = new EstateViewFieldModifierTypeMap(['test1', 'test2']);
		$actual = $pEstateViewFieldModifierTypeMap->reduceRecord($this->_testRecord);
		$this->assertEquals($expectedRecord, $actual);
	}
}
