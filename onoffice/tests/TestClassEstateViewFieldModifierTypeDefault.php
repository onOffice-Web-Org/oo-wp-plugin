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

use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeDefault;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeDefault
 *
 */

class TestClassEstateViewFieldModifierTypeDefault
	extends WP_UnitTestCase
{
	/** @var array */
	private $_exampleRecord = [
		'testField1' => true,
		'test_field2' => 'Example string',
		'multiselectfield' => [
			'Value 1',
			'Value 2',
			'Value 3',
		],
		'virtualAddress' => 0,
		'objektadresse_freigeben' => 0,
		'laengengrad' => 12.9458485,
		'breitengrad' => 53.12323,
	];


	/**
	 *
	 */

	public function testGetAPIFields()
	{
		$pEstateViewFieldModifierTypeDefault = new EstateViewFieldModifierTypeDefault([]);

		$defaultApiFields = [
			'virtualAddress',
			'objektadresse_freigeben',
		];

		$this->assertEquals($defaultApiFields, $pEstateViewFieldModifierTypeDefault->getAPIFields());
	}


	/**
	 *
	 */

	public function testReduceRecord()
	{
		$pEstateViewFieldModifierTypeDefault = new EstateViewFieldModifierTypeDefault([]);

		$expectedResult = [
			'testField1' => true,
			'test_field2' => 'Example string',
			'multiselectfield' => ['Value 1', 'Value 2', 'Value 3'],
			'virtualAddress' => 0,
			'objektadresse_freigeben' => 0,
			'laengengrad' => 0,
			'breitengrad' => 0,
		];

		$newRow = $pEstateViewFieldModifierTypeDefault->reduceRecord($this->_exampleRecord);
		$this->assertEquals($expectedResult, $newRow);
	}


	/**
	 *
	 */

	public function testFieldListEmpty()
	{
		$pEstateViewFieldModifierTypeDefault = new EstateViewFieldModifierTypeDefault([]);
		$this->assertEquals([], $pEstateViewFieldModifierTypeDefault->getVisibleFields());
	}


	/**
	 *
	 */

	public function testFieldList()
	{
		$fieldList = [
			'testField1',
			'anotherField',
			'moreFields',
			'Underscore_field11',
		];

		$pEstateViewFieldModifierTypeDefault = new EstateViewFieldModifierTypeDefault($fieldList);
		$this->assertEquals($fieldList, $pEstateViewFieldModifierTypeDefault->getVisibleFields());


		$apiFieldsExpect = $fieldList;
		$apiFieldsExpect []= 'virtualAddress';
		$apiFieldsExpect []= 'objektadresse_freigeben';

		$this->assertEquals($apiFieldsExpect, $pEstateViewFieldModifierTypeDefault->getAPIFields());
	}
}
