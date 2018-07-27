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
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler
 *
 */

class TestClassViewFieldModifierHandler
	extends WP_UnitTestCase
{
	/** @var array */
	private $_exampleRecord = [
		'test1' => 'testvalue 1',
		'test2' => [
			'testValueMulti 1',
			'testValueMulti 2',
		],
		'laengengrad' => 10,
		'virtualAddress' => 0,
		'objektadresse_freigeben' => 1,
		'virtualStreet' => '',
		'virtualHouseNumber' => '',
		'breitengrad' => 50,
		'virtualLatitude' => '',
		'virtualLongitude' => '',
		'strasse' => 'Teststraße',
		'showGoogleMap' => 1,
		'hausnummer' => '5',
		'objekttitel' => 'Nice Estate',
		'objektart' => 'haus',
		'vermarktungsart' => 'kauf',
		'ort' => 'Aachen',
		'objektnr_extern' => 'JJ1337',
	];


	/**
	 *
	 */

	public function testNoModifier()
	{
		$expectation = [
			'test1',
			'test2',
			'laengengrad',
			'virtualAddress',
			'objektadresse_freigeben',
			'virtualStreet',
			'virtualHouseNumber',
			'breitengrad',
			'virtualLatitude',
			'virtualLongitude',
			'strasse',
			'showGoogleMap',
			'hausnummer',
			'objekttitel',
			'objektart',
			'vermarktungsart',
			'ort',
			'objektnr_extern',
			// add more in case more Modifiers get added
		];
		$pViewFieldModifierHandler = new ViewFieldModifierHandler
			(['test1', 'test2', 'laengengrad'], onOfficeSDK::MODULE_ESTATE);
		$apiFields = $pViewFieldModifierHandler->getAllAPIFields();
		$this->assertEquals($expectation, $apiFields);
	}


	/**
	 *
	 * @expectedException Exception
	 * @expectedExceptionMessage Unknown Modifier
	 *
	 */

	public function testNoModifierReduceRecord()
	{
		$pViewFieldModifierHandler = new ViewFieldModifierHandler
			(['test1', 'test2', 'laengengrad'], onOfficeSDK::MODULE_ESTATE);
		$pViewFieldModifierHandler->processRecord(['test1' => 'hello']);
	}


	/**
	 *
	 */

	public function testDefaultModifierReduceRecord()
	{
		$expectation = [
			'test1' => 'testvalue 1',
			'test2' => [
				'testValueMulti 1',
				'testValueMulti 2',
			],
			'laengengrad' => 10,
		];

		$pViewFieldModifierHandler = new ViewFieldModifierHandler(['test1', 'test2', 'laengengrad'],
			onOfficeSDK::MODULE_ESTATE, EstateViewFieldModifierTypes::MODIFIER_TYPE_DEFAULT);
		$newRecord = $pViewFieldModifierHandler->processRecord($this->_exampleRecord);

		$this->assertEquals($expectation, $newRecord);
	}

}
