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
use onOffice\WPlugin\Controller\EstateListInputVariableReaderConfigTest;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers \onOffice\WPlugin\Filter\DefaultFilterBuilderListView
 *
 */

class TestClassDefaultFilterBuilderListView
	extends WP_UnitTestCase
{
	/** @var string */
	private $_localeBackup = null;


	/**
	 *
	 */

	public function setUp()
	{
		parent::setUp();
		$this->_localeBackup = get_locale();
		$pLocaleSwitcher = new WP_Locale_Switcher();
		$pLocaleSwitcher->init();
		$pLocaleSwitcher->switch_to_locale('en_US');
	}


	/**
	 *
	 */

	public function testDefaultFilter()
	{
		$pDataListView = new DataListView(1, 'test');
		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$pInstance = new DefaultFilterBuilderListView($pDataListView,
			$pEstateListInputVariableReaderConfig);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
		];

		$this->assertEquals($expected, $pInstance->buildFilter());
	}


	/**
	 *
	 */

	public function testReferenceViewFilter()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setListType(DataListView::LISTVIEW_TYPE_REFERENCE);

		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$pInstance = new DefaultFilterBuilderListView($pDataListView,
			$pEstateListInputVariableReaderConfig);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
			'referenz' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
		];


		$this->assertEquals($expected, $pInstance->buildFilter());
	}


	/**
	 *
	 */

	public function testFavoritesViewFilter()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setListType(DataListView::LISTVIEW_TYPE_FAVORITES);

		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$pInstance = new DefaultFilterBuilderListView($pDataListView,
			$pEstateListInputVariableReaderConfig);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
			'Id' => [
				[
					'op' => 'in',
					'val' => [0],
				],
			],
		];
		$this->assertEquals($expected, $pInstance->buildFilter());
	}


	/**
	 *
	 */

	public function testInputVarsScalar()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setFilterableFields(['kaufpreis', 'mietpreis', 'testtext']);

		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('kaufpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('mietpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('testtext', $module, FieldTypes::FIELD_TYPE_TEXT);

		$pEstateListInputVariableReaderConfig->setValue('kaufpreis', '999.99');
		$pEstateListInputVariableReaderConfig->setValue('mietpreis', '350.50');
		$pEstateListInputVariableReaderConfig->setValue('testtext', 'hello');
		$pInstance = new DefaultFilterBuilderListView($pDataListView,
			$pEstateListInputVariableReaderConfig);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
			'kaufpreis' => [
				[
					'op' => '=',
					'val' => 999.99,
				],
			],
			'mietpreis' => [
				[
					'op' => '=',
					'val' => 350.50,
				],
			],
			'testtext' => [
				[
					'op' => 'like',
					'val' => '%hello%',
				],
			],
		];

		$this->assertEquals($expected, $pInstance->buildFilter());
	}


	/**
	 *
	 */

	public function testInputVarsRange()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setFilterableFields(['kaufpreis', 'mietpreis', 'anzahl_zimmer']);

		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('kaufpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('mietpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('anzahl_zimmer', $module, FieldTypes::FIELD_TYPE_INTEGER);

		$pEstateListInputVariableReaderConfig->setValue('kaufpreis__von', '100000');
		$pEstateListInputVariableReaderConfig->setValue('mietpreis__bis', '350.50');
		$pEstateListInputVariableReaderConfig->setValue('anzahl_zimmer__von', '3');
		$pEstateListInputVariableReaderConfig->setValue('anzahl_zimmer__bis', '10');
		$pInstance = new DefaultFilterBuilderListView($pDataListView,
			$pEstateListInputVariableReaderConfig);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
			'kaufpreis' => [
				[
					'op' => '>=',
					'val' => 100000.,
				],
			],
			'mietpreis' => [
				[
					'op' => '<=',
					'val' => 350.5,
				],
			],
			'anzahl_zimmer' => [
				[
					'op' => '>=',
					'val' => 3,
				],
				[
					'op' => '<=',
					'val' => 10,
				],
			],
		];
		$this->assertEquals($expected, $pInstance->buildFilter());
	}


	/**
	 *
	 */

	public function testInputVarsArray()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setFilterableFields(['testtext', 'othertest']);

		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('testtext', $module, FieldTypes::FIELD_TYPE_MULTISELECT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('othertest', $module, FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pEstateListInputVariableReaderConfig->setValueArray('testtext', ['asd' , 'hello']);
		$pEstateListInputVariableReaderConfig->setValueArray('othertest', ['bonjour' , 'salve']);
		$pInstance = new DefaultFilterBuilderListView($pDataListView,
			$pEstateListInputVariableReaderConfig);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
			'testtext' => [
				[
					'op' => 'in',
					'val' => ['asd', 'hello'],
				],
			],
			'othertest' => [
				[
					'op' => 'in',
					'val' => ['bonjour', 'salve'],
				],
			],
		];
		$this->assertEquals($expected, $pInstance->buildFilter());
	}



	/**
	 *
	 */

	public function testEmptyInput()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setFilterableFields([
			'testtext', 'othertest', 'text', 'number_int', 'number_float', 'bool']);

		$pEstateListInputVariableReaderConfig = new EstateListInputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('testtext', $module, FieldTypes::FIELD_TYPE_MULTISELECT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('othertest', $module, FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('text', $module, FieldTypes::FIELD_TYPE_TEXT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('number_int', $module, FieldTypes::FIELD_TYPE_INTEGER);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('number_float', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('bool', $module, FieldTypes::FIELD_TYPE_BOOLEAN);
		$pInstance = new DefaultFilterBuilderListView($pDataListView,
			$pEstateListInputVariableReaderConfig);

		$expected = [
			'veroeffentlichen' => [
				[
					'op' => '=',
					'val' => 1,
				],
			],
		];
		$this->assertEquals($expected, $pInstance->buildFilter());
	}


	/**
	 *
	 */

	public function tearDown()
	{
		parent::tearDown();
		$pLocaleSwitcher = new WP_Locale_Switcher();
		$pLocaleSwitcher->switch_to_locale($this->_localeBackup);
	}
}
