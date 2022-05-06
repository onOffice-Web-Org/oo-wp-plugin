<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

use onOffice\WPlugin\Model\InputModelDB;
use WP_UnitTestCase;

/**
 *
 */

class TestClassInputModelDB
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testDefaultValues(): InputModelDB
	{
		$pInputModelDB = new InputModelDB('testInput', 'testLabel');
		$this->assertEquals(null, $pInputModelDB->getTable());
		$this->assertEquals(null, $pInputModelDB->getField());
		$this->assertEquals(0, $pInputModelDB->getMainRecordId());
		$this->assertEquals('', $pInputModelDB->getModule());
		$this->assertFalse($pInputModelDB->getIgnore());
		return $pInputModelDB;
	}


	/**
	 *
	 * @depends testDefaultValues
	 *
	 */

	public function testSetter(InputModelDB $pInputModelDB): InputModelDB
	{
		$pInputModelDB->setTable('testTable');
		$this->assertEquals('testTable', $pInputModelDB->getTable());
		$pInputModelDB->setField('testField');
		$this->assertEquals('testField', $pInputModelDB->getField());
		$pInputModelDB->setMainRecordId(3);
		$this->assertEquals(3, $pInputModelDB->getMainRecordId());
		$pInputModelDB->setModule('testModule');
		$this->assertEquals('testModule', $pInputModelDB->getModule());
		$pInputModelDB->setIgnore(true);
		$this->assertTrue($pInputModelDB->getIgnore());
		return $pInputModelDB;
	}


	/**
	 *
	 * @depends testSetter
	 *
	 */

	public function testGetIdentifier(InputModelDB $pInputModelDB)
	{
		$this->assertEquals('testTable-testField', $pInputModelDB->getIdentifier());
	}


	public function testGetIdentifierFailing()
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Table and field must be set');
		$pInputModelDB = new InputModelDB('testInput', 'testLabel');
		$pInputModelDB->getIdentifier();
	}
}