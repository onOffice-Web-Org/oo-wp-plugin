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
use onOffice\tests\WP_UnitTest_Localized;
use onOffice\WPlugin\Controller\InputVariableReaderConfigTest;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListView;
use onOffice\WPlugin\Filter\FilterBuilderInputVariables;
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
	extends WP_UnitTest_Localized
{
	/**
	 *
	 */

	public function setUp()
	{
		parent::setUp();
		$this->switchLocale('en_US');
	}


	/**
	 *
	 */

	public function testDefaultFilter()
	{
		$pDataListView = new DataListView(1, 'test');
		$pInputVariableReaderConfig = new InputVariableReaderConfigTest();
		$pFilterBuilderInputVariables = new FilterBuilderInputVariables
			(onOfficeSDK::MODULE_ESTATE, false, $pInputVariableReaderConfig);
		$pInstance = new DefaultFilterBuilderListView($pDataListView, $pFilterBuilderInputVariables);

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

		$pInputVariableReaderConfig = new InputVariableReaderConfigTest();
		$pFilterBuilderInputVariables = new FilterBuilderInputVariables
			(onOfficeSDK::MODULE_ESTATE, false, $pInputVariableReaderConfig);
		$pInstance = new DefaultFilterBuilderListView($pDataListView, $pFilterBuilderInputVariables);

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

		$pInputVariableReaderConfig = new InputVariableReaderConfigTest();
		$pFilterBuilderInputVariables = new FilterBuilderInputVariables
			(onOfficeSDK::MODULE_ESTATE, false, $pInputVariableReaderConfig);
		$pInstance = new DefaultFilterBuilderListView($pDataListView, $pFilterBuilderInputVariables);

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

	public function testInputVarsArray()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setFilterableFields(['testtext', 'othertest']);

		$module = onOfficeSDK::MODULE_ESTATE;
		$pInputVariableReaderConfig = new InputVariableReaderConfigTest();
		$pFilterBuilderInputVariables = new FilterBuilderInputVariables
			($module, false, $pInputVariableReaderConfig);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('testtext', $module, FieldTypes::FIELD_TYPE_MULTISELECT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('othertest', $module, FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pInputVariableReaderConfig->setValueArray('testtext', ['asd' , 'hello']);
		$pInputVariableReaderConfig->setValueArray('othertest', ['bonjour' , 'salve']);
		$pInstance = new DefaultFilterBuilderListView($pDataListView, $pFilterBuilderInputVariables);

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

		$module = onOfficeSDK::MODULE_ESTATE;
		$pInputVariableReaderConfig = new InputVariableReaderConfigTest();
		$pFilterBuilderInputVariables = new FilterBuilderInputVariables
			($module, false, $pInputVariableReaderConfig);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('testtext', $module, FieldTypes::FIELD_TYPE_MULTISELECT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('othertest', $module, FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('text', $module, FieldTypes::FIELD_TYPE_TEXT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('number_int', $module, FieldTypes::FIELD_TYPE_INTEGER);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('number_float', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('bool', $module, FieldTypes::FIELD_TYPE_BOOLEAN);
		$pInstance = new DefaultFilterBuilderListView($pDataListView, $pFilterBuilderInputVariables);

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
}
