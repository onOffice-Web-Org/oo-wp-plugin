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


	/**
	 *
	 */

	public function testMerge()
	{
		$pCollectionMain = $this->createPrefilledFieldsCollection();

		$pFieldSteve = new Field('Steve', 'testModuleA');
		$pFieldSteve->setCategory('Apple');

		$pCollectionSub = new FieldsCollection();
		$pCollectionSub->addField($pFieldSteve);
		$pCollectionSub->addField(new Field('Dennis', 'testModuleB'));
		$pCollectionSub->addField(new Field('Brian', 'testModuleB'));

		$pCollectionMain->merge($pCollectionSub, 'Bell');

		$this->assertCount(6, $pCollectionMain->getAllFields());
		$this->assertTrue($pCollectionMain->containsFieldByModule('testModuleA', 'Michael'));
		$this->assertTrue($pCollectionMain->containsFieldByModule('testModuleA', 'Elton'));
		$this->assertTrue($pCollectionMain->containsFieldByModule('testModuleB', 'Marilyn'));

		$this->assertEquals('Apple', $pCollectionMain->getFieldByModuleAndName('testModuleA', 'Steve')->getCategory());
		$this->assertEquals('Bell', $pCollectionMain->getFieldByModuleAndName('testModuleB', 'Dennis')->getCategory());
		$this->assertEquals('Bell', $pCollectionMain->getFieldByModuleAndName('testModuleB', 'Brian')->getCategory());
	}


	/**
	 *
	 */

	public function testGetFieldsByModule()
	{
		$pCollection = $this->createPrefilledFieldsCollection();
		$fieldsByModule = $pCollection->getFieldsByModule('testModuleA');
		$expectedResult = [
			'Michael' => new Field('Michael', 'testModuleA'),
			'Elton' => new Field('Elton', 'testModuleA'),
		];
		$this->assertEquals($expectedResult, $fieldsByModule);
	}


	/**
	 *
	 */

	public function testRemoveFieldByModuleAndName()
	{
		$pCollection = $this->createPrefilledFieldsCollection();
		$this->assertTrue($pCollection->containsFieldByModule('testModuleB', 'Marilyn'));
		$this->assertEquals([
			new Field('Michael', 'testModuleA'),
			new Field('Elton', 'testModuleA'),
			new Field('Marilyn', 'testModuleB'),
		], $pCollection->getAllFields());
		$this->assertEquals([
			'Marilyn' => new Field('Marilyn', 'testModuleB'),
		], $pCollection->getFieldsByModule('testModuleB'));

		$pCollection->removeFieldByModuleAndName('testModuleB', 'Marilyn');

		$this->assertFalse($pCollection->containsFieldByModule('testModuleB', 'Marilyn'));
		$this->assertEquals([
			new Field('Michael', 'testModuleA'),
			new Field('Elton', 'testModuleA'),
		], $pCollection->getAllFields());

		$this->assertEquals([], $pCollection->getFieldsByModule('testModuleB'));
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\Field\UnknownFieldException
	 *
	 */

	public function testRemoveFieldByModuleAndNameUnknownField()
	{
		$pCollection = $this->createPrefilledFieldsCollection();
		// Exception expected, because Marilyn is in testModuleB
		$pCollection->removeFieldByModuleAndName('testModuleA', 'Marilyn');
	}


	/**
	 *
	 * @return FieldsCollection
	 *
	 */

	private function createPrefilledFieldsCollection(): FieldsCollection
	{
		$pCollection = new FieldsCollection();
		$pCollection->addField(new Field('Michael', 'testModuleA'));
		$pCollection->addField(new Field('Elton', 'testModuleA'));
		$pCollection->addField(new Field('Marilyn', 'testModuleB'));
		return $pCollection;
	}
}
