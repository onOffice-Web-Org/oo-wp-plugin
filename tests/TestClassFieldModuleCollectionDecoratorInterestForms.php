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
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorInterestForms;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
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

class TestClassFieldModuleCollectionDecoratorInterestForms
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetAllFields()
	{
		$pFieldModuleDecorator = new FieldModuleCollectionDecoratorInterestForms
		(new FieldsCollection());
		$expectedResult = [
			$this->getExpectedFieldComment(),
		];
		$this->assertEquals($expectedResult, $pFieldModuleDecorator->getAllFields());
	}


	/**
	 *
	 */

	public function testContainsFieldByModule()
	{
		$pDecorator = new FieldModuleCollectionDecoratorInterestForms
		(new FieldModuleCollectionDecoratorReadAddress($this->getPrefilledCollection()));
		$this->assertTrue($pDecorator->containsFieldByModule('testModuleA', 'testFieldA'));
		$this->assertFalse($pDecorator->containsFieldByModule('testModuleF', 'testFieldA'));
		$this->assertTrue($pDecorator->containsFieldByModule(onOfficeSDK::MODULE_ADDRESS, 'newsletter'));
		$this->assertTrue($pDecorator->containsFieldByModule('', 'message'));
	}


	/**
	 *
	 */

	public function testGetFieldByModuleAndName()
	{
		$pDecorator = new FieldModuleCollectionDecoratorInterestForms
		($this->getPrefilledCollection());
		$this->assertInstanceOf(Field::class, $pDecorator->getFieldByModuleAndName
		('testModuleA', 'testFieldC'));
		$this->assertInstanceOf(Field::class, $pDecorator->getFieldByModuleAndName
		(onOfficeSDK::MODULE_ADDRESS, 'newsletter'));
		$this->assertInstanceOf(Field::class, $pDecorator->getFieldByModuleAndName('', 'message'));
	}


	public function testGetUnknownFieldByModuleAndName()
	{
		$this->expectException(\onOffice\WPlugin\Field\UnknownFieldException::class);
		$pDecorator = new FieldModuleCollectionDecoratorInterestForms
		($this->getPrefilledCollection());
		$pDecorator->getFieldByModuleAndName('testModuleF', 'testFieldC');
	}


	/**
	 *
	 * @return Field
	 *
	 */

	private function getExpectedFieldNewsletter(): Field
	{
		$pFieldNewsletter = new Field('newsletter', onOfficeSDK::MODULE_ADDRESS, 'Newsletter');
		$pFieldNewsletter->setCategory('');
		$pFieldNewsletter->setDefault(false);
		$pFieldNewsletter->setLength(0);
		$pFieldNewsletter->setPermittedvalues([]);
		$pFieldNewsletter->setType(FieldTypes::FIELD_TYPE_BOOLEAN);

		return $pFieldNewsletter;
	}


	/**
	 *
	 * @return Field
	 *
	 */

	private function getExpectedFieldMessage(): Field
	{
		$pFieldMessage = new Field('message', '', 'Message');
		$pFieldMessage->setCategory('');
		$pFieldMessage->setDefault(null);
		$pFieldMessage->setLength(0);
		$pFieldMessage->setPermittedvalues([]);
		$pFieldMessage->setType(FieldTypes::FIELD_TYPE_TEXT);

		return $pFieldMessage;
	}

	/**
	 *
	 * @return Field
	 *
	 */

	private function getExpectedFieldComment(): Field
	{
		$pFieldMessage = new Field('krit_bemerkung_oeffentlich', onOfficeSDK::MODULE_ESTATE, 'Comment');
		$pFieldMessage->setCategory('');
		$pFieldMessage->setDefault(null);
		$pFieldMessage->setLength(0);
		$pFieldMessage->setPermittedvalues([]);
		$pFieldMessage->setType(FieldTypes::FIELD_TYPE_TEXT);

		return $pFieldMessage;
	}


	/**
	 *
	 * @return FieldsCollection
	 *
	 */

	private function getPrefilledCollection(): FieldsCollection
	{
		$pCollection = new FieldsCollection();
		$pCollection->addField(new Field('testFieldA', 'testModuleA'));
		$pCollection->addField(new Field('testFieldB', 'testModuleA'));
		$pCollection->addField(new Field('testFieldC', 'testModuleA'));
		$pCollection->addField(new Field('testFieldB', 'testModuleB'));
		return $pCollection;
	}
}
