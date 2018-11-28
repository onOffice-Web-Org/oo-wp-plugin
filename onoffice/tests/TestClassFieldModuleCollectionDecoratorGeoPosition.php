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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPosition;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFieldModuleCollectionDecoratorGeoPosition
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetAllFields()
	{
		$pDecorator = new FieldModuleCollectionDecoratorGeoPosition(new FieldsCollection());
		$this->assertEquals(6, count($pDecorator->getAllFields()));

		$pCollectionFilled = new FieldsCollection();
		$pCollectionFilled->addField(new Field('testField', 'testModule'));
		$pCollectionFilled->addField(new Field('testField2', 'testModule'));
		$pCollectionFilled->addField(new Field('testField3', 'testModule'));

		$pDecoratorNew = new FieldModuleCollectionDecoratorGeoPosition($pCollectionFilled);
		$this->assertEquals(9, count($pDecoratorNew->getAllFields()));
	}


	/**
	 *
	 */

	public function testContainsFieldByModule()
	{
		$pDecorator = new FieldModuleCollectionDecoratorGeoPosition($this->getPrefilledCollection());

		$this->assertTrue($pDecorator->containsFieldByModule('testModuleA', 'testFieldB'));
		$this->assertTrue($pDecorator->containsFieldByModule('testModuleB', 'testFieldB'));
		$this->assertTrue($pDecorator->containsFieldByModule
			(onOfficeSDK::MODULE_SEARCHCRITERIA, GeoPosition::FIELD_GEO_POSITION));
		$this->assertTrue($pDecorator->containsFieldByModule
			(onOfficeSDK::MODULE_ESTATE, GeoPosition::FIELD_GEO_POSITION));
		$this->assertTrue($pDecorator->containsFieldByModule
			(onOfficeSDK::MODULE_ESTATE, GeoPosition::ESTATE_LIST_SEARCH_COUNTRY));
		$this->assertTrue($pDecorator->containsFieldByModule
			(onOfficeSDK::MODULE_ESTATE, GeoPosition::ESTATE_LIST_SEARCH_RADIUS));
		$this->assertTrue($pDecorator->containsFieldByModule
			(onOfficeSDK::MODULE_ESTATE, GeoPosition::ESTATE_LIST_SEARCH_STREET));
		$this->assertTrue($pDecorator->containsFieldByModule
			(onOfficeSDK::MODULE_ESTATE, GeoPosition::ESTATE_LIST_SEARCH_ZIP));
		$this->assertFalse($pDecorator->containsFieldByModule('testModuleB', 'testFieldC'));
	}


	/**
	 *
	 */

	public function testGetFieldByModuleAndName()
	{
		$pDecorator = new FieldModuleCollectionDecoratorGeoPosition($this->getPrefilledCollection());
		$this->assertInstanceOf(Field::class, $pDecorator->getFieldByModuleAndName
			(onOfficeSDK::MODULE_SEARCHCRITERIA, GeoPosition::FIELD_GEO_POSITION));
		$this->assertInstanceOf(Field::class, $pDecorator->getFieldByModuleAndName
			(onOfficeSDK::MODULE_ESTATE, GeoPosition::FIELD_GEO_POSITION));
		$this->assertInstanceOf(Field::class, $pDecorator->getFieldByModuleAndName
			(onOfficeSDK::MODULE_ESTATE, GeoPosition::ESTATE_LIST_SEARCH_COUNTRY));
		$this->assertInstanceOf(Field::class, $pDecorator->getFieldByModuleAndName
			(onOfficeSDK::MODULE_ESTATE, GeoPosition::ESTATE_LIST_SEARCH_RADIUS));
		$this->assertInstanceOf(Field::class, $pDecorator->getFieldByModuleAndName
			(onOfficeSDK::MODULE_ESTATE, GeoPosition::ESTATE_LIST_SEARCH_STREET));
		$this->assertInstanceOf(Field::class, $pDecorator->getFieldByModuleAndName
			(onOfficeSDK::MODULE_ESTATE, GeoPosition::ESTATE_LIST_SEARCH_ZIP));
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
