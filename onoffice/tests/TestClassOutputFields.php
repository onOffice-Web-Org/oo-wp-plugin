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

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\GeoPositionFieldHandler;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Controller\InputVariableReaderConfigTest;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @use onOffice\WPlugin\DataView\DataViewFilterableFields
 * @use InputVariableReader
 *
 */

class TestClassOutputFields
	extends WP_UnitTestCase
{
	/** @var DataListViewAddress */
	private $_pDataListView = null;

	/** @var InputVariableReader */
	private $_pInputVariableReader = null;


	/**
	 *
	 */

	public function setUp()
	{
		parent::setUp();

		$this->_pDataListView = new DataListViewAddress(1, 'test');
		$this->_pDataListView->setFields(['testField1', 'testField2', 'testField3', 'geoPosition']);
		$this->_pDataListView->setFilterableFields(['testField1', 'testField2', 'geoPosition']);
		$this->_pDataListView->setHiddenFields(['testField2']);

		$module = onOfficeSDK::MODULE_ADDRESS;

		$pInputVariableReaderConfig = new InputVariableReaderConfigTest();
		$pInputVariableReaderConfig->setFieldTypeByModule
			('testField1', $module, FieldTypes::FIELD_TYPE_INTEGER);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('testField3', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('country', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('zip', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('street', $module, FieldTypes::FIELD_TYPE_TEXT);
		$pInputVariableReaderConfig->setFieldTypeByModule
			('radius', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$this->_pInputVariableReader = new InputVariableReader($module, $pInputVariableReaderConfig);
		$this->setValues($pInputVariableReaderConfig);
	}


	/**
	 *
	 * @param InputVariableReaderConfigTest $pInputVariableReaderConfig
	 *
	 */

	private function setValues(InputVariableReaderConfigTest $pInputVariableReaderConfig)
	{
		$pInputVariableReaderConfig->setValue('testField1', '2');
		$pInputVariableReaderConfig->setValue('testField3', 'test value');
		$pInputVariableReaderConfig->setValue('country', 'DEU');
		$pInputVariableReaderConfig->setValue('zip', '52068');
		$pInputVariableReaderConfig->setValue('street', 'Charlottenburger Allee');
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\OutputFields::__construct
	 * @covers onOffice\WPlugin\Field\OutputFields::getDataView
	 * @covers onOffice\WPlugin\Field\OutputFields::getInputVariableReader
	 *
	 */

	public function testConstruct()
	{
		$pGeoPosition = $this->getMock(GeoPositionFieldHandler::class, [], [], '', false);
		$pOutputFields = new OutputFields($this->_pDataListView, $pGeoPosition, $this->_pInputVariableReader);
		$this->assertEquals($this->_pDataListView, $pOutputFields->getDataView());
		$this->assertEquals($this->_pInputVariableReader, $pOutputFields->getInputVariableReader());

	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\OutputFields::getVisibleFilterableFields
	 *
	 */

	public function testGetVisibleFilterableFields()
	{
		$expectation = [
			'testField1' => 2,
			'country' => 'DEU',
			'zip' => '52068',
			'street' => 'Charlottenburger Allee',
			'radius' => null,
		];

		$pMockRecordManager = $this->getMock(RecordManagerReadListViewEstate::class, [], [], '', false);
		$pInputModelDBFactoryConfigGeoFields = new InputModelDBFactoryConfigGeoFields(onOfficeSDK::MODULE_ESTATE);
		$pGeoPosition = $this->getMock(GeoPositionFieldHandler::class, ['getActiveFields'],
			[3, $pMockRecordManager, $pInputModelDBFactoryConfigGeoFields]);
		$pGeoPosition->method('getActiveFields')->will($this->returnValue([
			'country_active' => 'country',
			'zip_active' => 'zip',
			'radius_active' => 'radius',
			'street_active' => 'street',
		]));
		$pOutputFields = new OutputFields($this->_pDataListView, $pGeoPosition, $this->_pInputVariableReader);
		$this->assertEquals($expectation, $pOutputFields->getVisibleFilterableFields());
	}
}
