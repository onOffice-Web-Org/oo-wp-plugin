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
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorSearchcriteria;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFieldModuleCollectionDecoratorSearchcriteria
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetAllFields()
	{
		$pDecorator = new FieldModuleCollectionDecoratorSearchcriteria(new FieldsCollection());
		$newFields = $pDecorator->getAllFields();

		$pNewField = new Field('krit_bemerkung_oeffentlich', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pNewField->setCategory('');
		$pNewField->setDefault('');
		$pNewField->setLabel('Comment');
		$pNewField->setLength(0);
		$pNewField->setType(FieldTypes::FIELD_TYPE_TEXT);

		$expectationNewFields = [$pNewField];
		$this->assertEquals($expectationNewFields, $newFields);
	}


	/**
	 *
	 */

	public function testGetFieldByModuleAndName()
	{
		$pDecorator = new FieldModuleCollectionDecoratorSearchcriteria(new FieldsCollection());
		$module = onOfficeSDK::MODULE_SEARCHCRITERIA;
		$this->assertInstanceOf(Field::class,
			$pDecorator->getFieldByModuleAndName($module, 'krit_bemerkung_oeffentlich'));
	}


	/**
	 *
	 */

	public function testContainsFieldByModule()
	{
		$pDecorator = new FieldModuleCollectionDecoratorSearchcriteria(new FieldsCollection());
		$module = onOfficeSDK::MODULE_SEARCHCRITERIA;
		$this->assertTrue($pDecorator->containsFieldByModule($module, 'krit_bemerkung_oeffentlich'));
		$this->assertFalse($pDecorator->containsFieldByModule('test', 'test'));
	}


	/**
	 *
	 */

	public function testCombined()
	{
		$pFieldsCollection = new FieldsCollection();
		$pFieldsCollection->addField(new Field('testField', 'testModule'));
		$pDecorator = new FieldModuleCollectionDecoratorSearchcriteria($pFieldsCollection);
		$this->assertEquals(2, count($pDecorator->getAllFields()));
		$this->assertEquals('testField',
			$pDecorator->getFieldByModuleAndName('testModule', 'testField')->getName());
		$this->assertTrue($pDecorator->containsFieldByModule
			(onOfficeSDK::MODULE_SEARCHCRITERIA, 'krit_bemerkung_oeffentlich'));
		$this->assertTrue($pDecorator->containsFieldByModule('testModule', 'testField'));
		$this->assertFalse($pDecorator->containsFieldByModule('X', 'Y'));
	}
}
