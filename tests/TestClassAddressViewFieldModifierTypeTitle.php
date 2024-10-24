<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

use onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypeTitle;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypeTitle
 *
 */

class TestClassAddressViewFieldModifierTypeTitle
	extends WP_UnitTestCase
{
	/** @var array */
	private $_exampleRecord = [
		'test1' => 'test',
		'test2' => 'test 2',
		'Vorname' => 'test Vorname 1',
		'Name' => 'test Name',
		'Zusatz1' => 'test Zusatz1'
	];


	/**
	 *
	 */

	public function testApiFields()
	{
		$expectation = [
			'test1',
			'test2',
			'Vorname',
			'Name',
			'Zusatz1'
		];

		$pAddressViewFieldModifierTypeTitle = new AddressViewFieldModifierTypeTitle(['test1', 'test2']);
		$apiFields = $pAddressViewFieldModifierTypeTitle->getAPIFields();
		$this->assertEquals($expectation, $apiFields);
	}


	/**
	 *
	 */

	public function testVisibleFields()
	{
		$expectation = [
			'test1',
			'test2',
			'Vorname',
			'Name',
			'Zusatz1'
		];

		$pAddressViewFieldModifierTypeTitle = new AddressViewFieldModifierTypeTitle(['test1', 'test2']);
		$visibleFields = $pAddressViewFieldModifierTypeTitle->getVisibleFields();
		$this->assertEquals($expectation, $visibleFields);
	}


	/**
	 *
	 */

	public function testReduceRecord()
	{
		$pAddressViewFieldModifierTypeTitle = new AddressViewFieldModifierTypeTitle(['test1', 'test2']);
		$record = $pAddressViewFieldModifierTypeTitle->reduceRecord($this->_exampleRecord);
		// this won't do any change
		$this->assertEquals($this->_exampleRecord, $record);
	}
	
	/**
	 * @covers onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypeTitle::getAPICustomFields
	 */
	public function testApiCustomFields()
	{
		$expectation = [
			'Anrede',
			'Vorname',
			'Name',
			'Zusatz1',
			'Email',
			'Telefon1',
			'Telefax1',
		];
		
		$pAddressViewFieldModifierTypeTitle = new AddressViewFieldModifierTypeTitle(['test1', 'test2']);
		$apiCustomFields = $pAddressViewFieldModifierTypeTitle->getAPICustomFields();
		$this->assertEquals($expectation, $apiCustomFields);
	}
	
	/**
	 * @covers onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypeTitle::getVisibleCustomFields
	 */
	
	public function testVisibleCustomFields()
	{
		$expectation = [
			'Anrede',
			'Vorname',
			'Name',
			'Zusatz1',
			'Email',
			'Telefon1',
			'Telefax1',
		];
		
		$pAddressViewFieldModifierTypeTitle = new AddressViewFieldModifierTypeTitle(['test1', 'test2']);
		$visibleCustomFields = $pAddressViewFieldModifierTypeTitle->getVisibleCustomFields();
		$this->assertEquals($expectation, $visibleCustomFields);
	}
}
