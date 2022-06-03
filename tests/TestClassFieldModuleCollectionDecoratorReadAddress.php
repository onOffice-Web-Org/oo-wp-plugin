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
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorFormContact;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;
use function __;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFieldModuleCollectionDecoratorReadAddress
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetAllFields()
	{
		$pDecoratorReadAddress = new FieldModuleCollectionDecoratorReadAddress
			(new FieldsCollection());
		$expectedResult = $this->buildExpectedResult($pDecoratorReadAddress);
		$this->assertEquals($expectedResult, $pDecoratorReadAddress->getAllFields());
	}


	/**
	 *
	 */

	public function testCombined()
	{
		$pDecoratorReadAddress = new FieldModuleCollectionDecoratorReadAddress
			(new FieldsCollection());
		$countOverall = count($pDecoratorReadAddress::getNewAddressFields()) + 2;
		$pDecoratorContactForm = new FieldModuleCollectionDecoratorFormContact($pDecoratorReadAddress);
		$this->assertEquals($countOverall, count($pDecoratorContactForm->getAllFields()));
	}


	/**
	 *
	 */

	public function testGetFieldByModuleAndName()
	{
		$pDecorator = new FieldModuleCollectionDecoratorReadAddress(new FieldsCollection());
		$module = onOfficeSDK::MODULE_ADDRESS;
		$this->assertInstanceOf(Field::class, $pDecorator->getFieldByModuleAndName
			($module, 'imageUrl'));
	}


	/**
	 *
	 */

	public function testContainsFieldByModule()
	{
		$pDecorator = new FieldModuleCollectionDecoratorReadAddress(new FieldsCollection());
		$module = onOfficeSDK::MODULE_ADDRESS;
		$this->assertTrue($pDecorator->containsFieldByModule($module, 'imageUrl'));
		$this->assertFalse($pDecorator->containsFieldByModule($module, 'testUnknown'));
	}


	/**
	 *
	 * @param FieldModuleCollectionDecoratorReadAddress $pDecorator
	 * @return array
	 *
	 */

	private function buildExpectedResult(FieldModuleCollectionDecoratorReadAddress $pDecorator): array
	{
		$newAddressFields = $pDecorator::getNewAddressFields();
		$result = [];

		foreach ($newAddressFields as $fieldName => $data) {
			$pField = new Field($fieldName, onOfficeSDK::MODULE_ADDRESS, __($data['label'], 'onoffice-for-wp-websites'));
			$pField->setCategory(__($data['content'] ?? '', 'onoffice-for-wp-websites'));
			$pField->setDefault($data['default'] ?? null);
			$pField->setLength($data['length'] ?? 0);
			$pField->setPermittedvalues($data['permittedvalues'] ?? []);
			$pField->setType($data['type']);
			$result []= $pField;
		}

		return $result;
	}
}
