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

use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;

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
		$pCollection = new FieldsCollection('testmodule');
		$this->assertEquals('testmodule', $pCollection->getModule());
	}


	/**
	 *
	 */

	public function testAddGetFields()
	{
		$pCollection = new FieldsCollection('testmodule');
		$this->assertEquals([], $pCollection->getAllFields());
		$pField1 = new Field('testField1');
		$pField2 = new Field('testField2');

		$pCollection->addField($pField1);
		$pCollection->addField($pField2);

		$this->assertEquals(['testField1' => $pField1, 'testField2' => $pField2],
			$pCollection->getAllFields());
	}


	/**
	 *
	 */

	public function testGetByName()
	{
		$pCollection = new FieldsCollection('testmodule');
		$pField1 = new Field('John');
		$pField2 = new Field('Eric');
		$pField3 = new Field('Sylvia');

		$pCollection->addField($pField1);
		$pCollection->addField($pField2);
		$pCollection->addField($pField3);
		$this->assertEquals($pField2, $pCollection->getByName('Eric'));
	}


	/**
	 *
	 * @expectedException Exception
	 * @expectedExceptionMessage Field not in collection
	 *
	 */

	public function testGetNonExistentField()
	{
		$pCollection = new FieldsCollection('testmodule');
		$pCollection->addField(new Field('John'));
		$pCollection->getByName('Eric');
	}
}
