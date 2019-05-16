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
use onOffice\WPlugin\Controller\InputVariableReaderConfigTest;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Filter\FilterBuilderInputVariables;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFilterBuilderInputVariables
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testInputVarsScalar()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setFilterableFields(['kaufpreis', 'mietpreis', 'testtext', 'bezugsfrei']);

		$module = onOfficeSDK::MODULE_ESTATE;
		$pInputVariableReaderConfig = new InputVariableReaderConfigTest();
		$pFilterBuilderInputVariables = new FilterBuilderInputVariables
			($module, false, $pInputVariableReaderConfig);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('kaufpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('mietpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('testtext', $module, FieldTypes::FIELD_TYPE_TEXT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('bezugsfrei', $module, FieldTypes::FIELD_TYPE_DATETIME);

		$pInputVariableReaderConfig->setValue('kaufpreis', '999,99');
		$pInputVariableReaderConfig->setValue('mietpreis', '350,50');
		$pInputVariableReaderConfig->setValue('testtext', 'hello');
		$pInputVariableReaderConfig->setValue('bezugsfrei', '27.03.1998 12:47:00');
		$filterableFields = $pDataListView->getFilterableFields();

		$expected = [
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
			'bezugsfrei' => [
				[
					'op' => '=',
					'val' => '1998-03-27 12:47:00',
				],
			],
		];

		$result = $pFilterBuilderInputVariables->getPostFieldsFilter($filterableFields);
		$this->assertEquals($expected, $result);
	}


	/**
	 *
	 */

	public function testInputVarsRange()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setFilterableFields([
			'kaufpreis', 'mietpreis', 'anzahl_zimmer', 'bezugsfrei', 'ort', 'objektart', 'testvarchar',
		]);

		$module = onOfficeSDK::MODULE_ESTATE;
		$pInputVariableReaderConfig = new InputVariableReaderConfigTest();
		$pFilterBuilderInputVariables = new FilterBuilderInputVariables
			($module, false, $pInputVariableReaderConfig);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('kaufpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('mietpreis', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('anzahl_zimmer', $module, FieldTypes::FIELD_TYPE_INTEGER);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('bezugsfrei', $module, FieldTypes::FIELD_TYPE_DATETIME);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('ort', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('testvarchar', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('objektart', $module, FieldTypes::FIELD_TYPE_SINGLESELECT);

		$pInputVariableReaderConfig->setValue('kaufpreis__von', '100000');
		$pInputVariableReaderConfig->setValue('mietpreis__bis', '350,50');
		$pInputVariableReaderConfig->setValue('anzahl_zimmer__von', '3');
		$pInputVariableReaderConfig->setValue('anzahl_zimmer__bis', '10');
		$pInputVariableReaderConfig->setValue('anzahl_zimmer__bis', '10');
		$pInputVariableReaderConfig->setValue('bezugsfrei__von', '01.01.2017 00:00:00');
		$pInputVariableReaderConfig->setValue('bezugsfrei__bis', '01.02.2017 23:59:00');
		$pInputVariableReaderConfig->setValue('ort', '');
		$pInputVariableReaderConfig->setValue('objektart', 'haus');
		$pInputVariableReaderConfig->setValue('testvarchar', 'hello');
		$filterableFields = $pDataListView->getFilterableFields();

		$expected = [
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
			'bezugsfrei' => [
				[
					'op' => '>=',
					'val' => '2017-01-01 00:00:00',
				],
				[
					'op' => '<=',
					'val' => '2017-02-01 23:59:00',
				],
			],
			'objektart' => [
				[
					'op' => 'in',
					'val' => 'haus',
				],
			],
			'testvarchar' => [
				[
					'op' => '=',
					'val' => 'hello',
				],
			],
		];

		$result = $pFilterBuilderInputVariables->getPostFieldsFilter($filterableFields);
		$this->assertEquals($expected, $result);
	}


	/**
	 *
	 */

	public function testFuzzy()
	{
		$pDataListView = new DataListView(1, 'test');
		$pDataListView->setFilterableFields([
			'Vorname', 'Name', 'impressum', 'Ort', 'test',
		]);

		$module = onOfficeSDK::MODULE_ADDRESS;
		$pInputVariableReaderConfig = new InputVariableReaderConfigTest();
		$pFilterBuilderInputVariables = new FilterBuilderInputVariables
			($module, true, $pInputVariableReaderConfig);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('Vorname', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('Name', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('impressum', $module, FieldTypes::FIELD_TYPE_TEXT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('Ort', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('test', $module, FieldTypes::FIELD_TYPE_INTEGER);

		$pInputVariableReaderConfig->setValue('Vorname', 'john');
		$pInputVariableReaderConfig->setValue('Name', 'doe');
		$pInputVariableReaderConfig->setValue('impressum', 'test');
		$pInputVariableReaderConfig->setValue('Ort', 'Aac');
		$pInputVariableReaderConfig->setValue('test', '10');
		$filterableFields = $pDataListView->getFilterableFields();

		$expected = [
			'Vorname' => [
				[
					'op' => 'like',
					'val' => '%john%',
				],
			],
			'Name' => [
				[
					'op' => 'like',
					'val' => '%doe%',
				],
			],
			'impressum' => [
				[
					'op' => 'like',
					'val' => '%test%',
				],
			],
			'Ort' => [
				[
					'op' => 'like',
					'val' => '%Aac%',
				],
			],
			'test' => [
				[
					'op' => '=',
					'val' => 10,
				],
			],
		];

		$result = $pFilterBuilderInputVariables->getPostFieldsFilter($filterableFields);
		$this->assertEquals($expected, $result);
	}
}
