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
	/** @var FieldModuleCollectionDecoratorReadAddress */
	private $_pFieldModule = null;
	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pFieldModule = $this->getMockBuilder(FieldModuleCollectionDecoratorReadAddress::class)
		                            ->setMethods(['getAddressEstateField', 'getFieldByModuleAndName', 'setAllAddressEstateField'])
		                            ->setConstructorArgs([new FieldsCollection()])
		                            ->getMock();
		$this->_pFieldModule->method('setAllAddressEstateField')->with([FieldModuleCollectionDecoratorReadAddress::getNewAddressFields()]);
	}


	/**
	 *
	 */

	public function testGetAllFields()
	{
		$expectedResult = $this->buildExpectedResult($this->_pFieldModule);
		$this->assertEquals($expectedResult, $this->_pFieldModule->getAllFields());
	}


	/**
	 *
	 */

	public function testCombined()
	{
		$countOverall = count($this->_pFieldModule::getNewAddressFields()) + 2;
		$pDecoratorContactForm = new FieldModuleCollectionDecoratorFormContact($this->_pFieldModule);
		$this->assertEquals($countOverall, count($pDecoratorContactForm->getAllFields()));
	}


	/**
	 *
	 */

	public function testGetFieldByModuleAndName()
	{
		$module = onOfficeSDK::MODULE_ADDRESS;
		$this->assertInstanceOf(Field::class, $this->_pFieldModule->getFieldByModuleAndName
			($module, 'imageUrl'));
	}


	/**
	 *
	 */

	public function testContainsFieldByModule()
	{
		$module = onOfficeSDK::MODULE_ADDRESS;
		$this->assertTrue($this->_pFieldModule->containsFieldByModule($module, 'imageUrl'));
		$this->assertFalse($this->_pFieldModule->containsFieldByModule($module, 'testUnknown'));
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
			$pField->setCategory(__($data['content'] ?? 'Form Specific Fields', 'onoffice-for-wp-websites'));
			$pField->setDefault($data['default'] ?? null);
			$pField->setLength($data['length'] ?? 0);
			$pField->setPermittedvalues($data['permittedvalues'] ?? []);
			$pField->setType($data['type']);
			$pField->setTableName($data['tablename'] ?? '');
			$result []= $pField;
		}

		return $result;
	}
}
