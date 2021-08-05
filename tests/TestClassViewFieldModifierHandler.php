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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeMap;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeTitle;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierFactory;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;
use WP_UnitTestCase;

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
		'ort' => 'Aachen',
		'objektnr_extern' => 'JJ1337',
		'reserviert' => '1',
		'verkauft' => '1'
	];


	/**
	 *
	 */

	public function testNoModifier()
	{
		$expectation = [
			'test1',
			'test2',
			'breitengrad',
			'laengengrad',
			'virtualAddress',
			'vermarktungsart',
			'objektadresse_freigeben',
			'virtualStreet',
			'virtualHouseNumber',
			'virtualLatitude',
			'virtualLongitude',
			'strasse',
			'showGoogleMap',
			'hausnummer',
			'objekttitel',
			'objektart',
			'ort',
			'objektnr_extern',
		];
		$pViewFieldModifierHandler = $this->getPreconfiguredHandler
			(['test1', 'test2', 'laengengrad', 'geoPosition']);
		$apiFields = $pViewFieldModifierHandler->getAllAPIFields();
		$this->assertEqualSets($expectation, $apiFields);
	}


	/**
	 *
	 * @expectedException \Exception
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
			'showGoogleMap' => 1,
			'laengengrad' => 10,
			'breitengrad' => 50,
			'virtualAddress' => 0,
			'objekttitel' => 'Nice Estate',
			'test1' => 'testvalue 1',
			'test2' => [
				'testValueMulti 1',
				'testValueMulti 2',
			],
		];

		$pViewFieldModifierHandler = $this->getPreconfiguredHandler(['test1', 'test2', 'laengengrad'],
			EstateViewFieldModifierTypes::MODIFIER_TYPE_MAP);
		$newRecord = $pViewFieldModifierHandler->processRecord($this->_exampleRecord);

		$this->assertSame($expectation, $newRecord);
	}


	/**
	 *
	 * @param array $viewFields
	 * @return ViewFieldModifierHandler
	 *
	 */

	private function getPreconfiguredHandler(array $viewFields, string $modifier = ''): ViewFieldModifierHandler
	{
		$pFactory = $this->getMockBuilder(ViewFieldModifierFactory::class)
			->setMethods(['getMapping'])
			->setConstructorArgs([onOfficeSDK::MODULE_ESTATE])
			->getMock();
		$pFactory->expects($this->any())->method('getMapping')->will($this->returnValue([
			EstateViewFieldModifierTypeMap::class,
			EstateViewFieldModifierTypeTitle::class,
		]));

		$pHandler = new ViewFieldModifierHandler($viewFields, onOfficeSDK::MODULE_ESTATE, $modifier);
		$pHandler->setViewFieldModifierFactory($pFactory);

		return $pHandler;
	}

}
