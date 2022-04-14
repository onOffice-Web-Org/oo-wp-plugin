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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassFieldModuleCollectionDecoratorGeoPositionFrontend
	extends WP_UnitTestCase
{
	/** @var FieldModuleCollectionDecoratorGeoPositionFrontend */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pFieldModuleCollection = new FieldsCollection();
		$pField = new Field('testFieldABC', onOfficeSDK::MODULE_ESTATE);
		$pFieldModuleCollection->addField($pField);
		$this->_pSubject = new FieldModuleCollectionDecoratorGeoPositionFrontend($pFieldModuleCollection);
	}


	/**
	 *
	 */

	public function testGetAllFields()
	{
		$allFields = $this->_pSubject->getAllFields();
		$this->assertCount(6, $allFields);

		foreach ($allFields as $pField) {
			$this->assertInstanceOf(Field::class, $pField);
			if ($pField->getModule() === onOfficeSDK::MODULE_SEARCHCRITERIA) {
				$this->assertStringStartsWith('range', $pField->getName());
			}
		}
	}


	/**
	 *
	 */

	public function testContainsFieldByModule()
	{
		$module = onOfficeSDK::MODULE_ESTATE;
		$pSubject = $this->_pSubject;
		$this->assertFalse($pSubject->containsFieldByModule($module, GeoPosition::FIELD_GEO_POSITION));
		$this->assertTrue($pSubject->containsFieldByModule($module, GeoPosition::ESTATE_LIST_SEARCH_COUNTRY));
		$this->assertTrue($pSubject->containsFieldByModule($module, GeoPosition::ESTATE_LIST_SEARCH_RADIUS));
		$this->assertTrue($pSubject->containsFieldByModule($module, GeoPosition::ESTATE_LIST_SEARCH_CITY));
		$this->assertTrue($pSubject->containsFieldByModule($module, GeoPosition::ESTATE_LIST_SEARCH_STREET));
		$this->assertTrue($pSubject->containsFieldByModule($module, GeoPosition::ESTATE_LIST_SEARCH_ZIP));
	}


	/**
	 *
	 */

	public function testGetFieldByModuleAndName()
	{
		$module = onOfficeSDK::MODULE_ESTATE;
		$fields = [
			GeoPosition::ESTATE_LIST_SEARCH_COUNTRY,
			GeoPosition::ESTATE_LIST_SEARCH_RADIUS,
			GeoPosition::ESTATE_LIST_SEARCH_CITY,
			GeoPosition::ESTATE_LIST_SEARCH_STREET,
			GeoPosition::ESTATE_LIST_SEARCH_ZIP,
		];

		foreach ($fields as $fieldName) {
			$pField = $this->_pSubject->getFieldByModuleAndName($module, $fieldName);
			$this->assertInstanceOf(Field::class, $pField);
			$this->assertEquals($fieldName, $pField->getName());
		}
	}


	public function testGetFieldByModuleAndNameUnknown()
	{
		$this->expectException(\onOffice\WPlugin\Field\UnknownFieldException::class);
		$module = onOfficeSDK::MODULE_ESTATE;
		$this->_pSubject->getFieldByModuleAndName($module, 'testasdf');
	}


	/**
	 *
	 */

	public function testGetFieldByModuleAndNameParent()
	{
		$module = onOfficeSDK::MODULE_ESTATE;
		$pField = $this->_pSubject->getFieldByModuleAndName($module, 'testFieldABC');
		$this->assertInstanceOf(Field::class, $pField);
	}


	/**
	 *
	 */

	public function testAddField()
	{
		$pSubject = $this->_pSubject;
		$this->assertFalse($pSubject->containsFieldByModule(onOfficeSDK::MODULE_ESTATE, 'test'));
		$pField = new Field('test', onOfficeSDK::MODULE_ESTATE);
		$this->_pSubject->addField($pField);
		$this->assertTrue($pSubject->containsFieldByModule(onOfficeSDK::MODULE_ESTATE, 'test'));
	}
}
