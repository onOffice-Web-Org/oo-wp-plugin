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

use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFieldsCollection
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetModule()
	{
		$pCollection = new FieldsCollection();
		$this->assertEquals([], $pCollection->getAllFields());
	}


	/**
	 *
	 */

	public function testAddGetFields()
	{
		$pCollection = new FieldsCollection();
		$this->assertEquals([], $pCollection->getAllFields());
		$pField1 = new Field('testField1', 'testModule');
		$pField2 = new Field('testField2', 'testModule');

		$pCollection->addField($pField1);
		$pCollection->addField($pField2);

		$this->assertEquals([$pField1, $pField2],
			$pCollection->getAllFields());
	}


	/**
	 *
	 */

	public function testContainsFieldByModule()
	{
		$pCollection = new FieldsCollection();
		$pCollection->addField(new Field('John', 'testModuleA'));
		$this->assertFalse($pCollection->containsFieldByModule('testModuleB', 'John'));
		$this->assertTrue($pCollection->containsFieldByModule('testModuleA', 'John'));
		$this->assertFalse($pCollection->containsFieldByModule('other', 'Fred'));
	}
}
