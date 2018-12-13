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

use onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypeDefault;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassAddressViewFieldModifierTypeDefault
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testNormal()
	{
		$fieldList = [
			'testfield1',
			'test_field2',
			'ind_348field_23782',
		];
		$pAddressViewFieldModifier = new AddressViewFieldModifierTypeDefault($fieldList);

		$apiFields = $pAddressViewFieldModifier->getAPIFields();
		$this->assertEquals($fieldList, $apiFields);

		$visibleFields = $pAddressViewFieldModifier->getVisibleFields();
		$this->assertEquals($fieldList, $visibleFields);
	}


	/**
	 *
	 */

	public function testEmpty()
	{
		$pAddressViewFieldModifier = new AddressViewFieldModifierTypeDefault([]);
		$apiFields = $pAddressViewFieldModifier->getAPIFields();
		$this->assertEquals([], $apiFields);

		$visibleFields = $pAddressViewFieldModifier->getVisibleFields();
		$this->assertEquals([], $visibleFields);
	}


	/**
	 *
	 * Tests if compounding fields get removed and split into their entities
	 *
	 */

	public function testCompoundingFields()
	{
		$fieldListInput = [
			'testfield1',
			'test_field2',
			'ind_348field_23782',
			'PLZ-Ort',
			'LKZ-PLZ-Ort',
		];

		$fieldListOutput = [
			'testfield1',
			'test_field2',
			'ind_348field_23782',
			'PLZ',
			'Ort',
			'LKZ',
		];

		$pAddressViewFieldModifier = new AddressViewFieldModifierTypeDefault($fieldListInput);

		$apiFields = $pAddressViewFieldModifier->getAPIFields();
		$this->assertEquals($fieldListOutput, $apiFields);

		$visibleFields = $pAddressViewFieldModifier->getVisibleFields();
		$this->assertEquals($fieldListInput, $visibleFields);
	}


	/**
	 *
	 */

	public function testReduceRecord()
	{
		$record = [
			'testfield1' => 'asdf',
			'test_field2' => 13,
			'ind_348field_23782' => ['asd', 'fghj'],
			'PLZ' => '52068',
			'Ort' => 'Aachen',
		];

		$fieldListInput = [
			'testfield1',
			'test_field2',
			'ind_348field_23782',
			'PLZ-Ort',
		];

		$pAddressViewFieldModifier = new AddressViewFieldModifierTypeDefault($fieldListInput);
		$result = $pAddressViewFieldModifier->reduceRecord($record);

		$expectedResult = [
			'testfield1' => 'asdf',
			'test_field2' => 13,
			'ind_348field_23782' => ['asd', 'fghj'],
			'PLZ-Ort' => ['52068', 'Aachen'],
		];
		$this->assertEquals($expectedResult, $result);
	}
}
