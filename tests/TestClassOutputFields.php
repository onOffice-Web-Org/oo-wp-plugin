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

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\GeoPositionFieldHandler;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Controller\InputVariableReaderConfigTest;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Field\CompoundFieldsFilter;
use onOffice\WPlugin\Types\FieldsCollection;
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

	/** @var InputVariableReaderConfigTest */
	private $_pInputVariableReaderConfigTest;

	/** @var Container */
	private $_pContainer;


	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pDataListView = new DataListViewAddress(1, 'test');
		$this->_pDataListView->setFields(['testField1', 'testField2', 'testField3', 'geoPosition']);
		$this->_pDataListView->setFilterableFields(['testField1', 'testField2', 'geoPosition']);
		$this->_pDataListView->setHiddenFields(['testField2']);

		$module = onOfficeSDK::MODULE_ESTATE;

		$this->_pInputVariableReaderConfigTest = new InputVariableReaderConfigTest();
		$this->_pInputVariableReaderConfigTest->setFieldTypeByModule
			('testField1', $module, FieldTypes::FIELD_TYPE_INTEGER);
		$this->_pInputVariableReaderConfigTest->setFieldTypeByModule
			('testField3', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pInputVariableReaderConfigTest->setFieldTypeByModule
			('country', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pInputVariableReaderConfigTest->setFieldTypeByModule
			('zip', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pInputVariableReaderConfigTest->setFieldTypeByModule
			('city', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pInputVariableReaderConfigTest->setFieldTypeByModule
			('street', $module, FieldTypes::FIELD_TYPE_TEXT);
		$this->_pInputVariableReaderConfigTest->setFieldTypeByModule
			('radius', $module, FieldTypes::FIELD_TYPE_FLOAT);
		$this->_pInputVariableReader = new InputVariableReader($module, $this->_pInputVariableReaderConfigTest);

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
	}

	/**
	 * @covers \onOffice\WPlugin\Field\OutputFields::getVisibleFilterableFields
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

		$this->_pInputVariableReaderConfigTest->setValue('testField1', '2');
		$this->_pInputVariableReaderConfigTest->setValue('testField3', 'test value');
		$this->_pInputVariableReaderConfigTest->setValue('country', 'DEU');
		$this->_pInputVariableReaderConfigTest->setValue('zip', '52068');
		$this->_pInputVariableReaderConfigTest->setValue('street', 'Charlottenburger Allee');

		$activeGeoFields = [
			'country_active' => 'country',
			'zip_active' => 'zip',
			'radius_active' => 'radius',
			'street_active' => 'street',
		];

		$this->performTest($activeGeoFields, $expectation);
	}

	public function testGetVisibleFilterableFieldsWithEmptyGeo()
	{
		$expectation = ['testField1' => 2];
		$this->_pInputVariableReaderConfigTest->setValue('testField1', '2');
		$this->_pInputVariableReaderConfigTest->setValue('testField3', 'test value');
		$this->_pInputVariableReaderConfigTest->setValue('country', '');
		$this->_pInputVariableReaderConfigTest->setValue('zip', '');
		$this->_pInputVariableReaderConfigTest->setValue('street', '');

		$activeGeoFields = [
			'country_active' => 'country',
			'zip_active' => 'zip',
			'city_active' => 'city',
			'radius_active' => 'radius',
		];

		$this->performTest($activeGeoFields, $expectation);
	}

	/**
	 * @param array $activeGeoFields
	 * @param array $expectation
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function performTest(array $activeGeoFields, array $expectation)
	{
		$pMockRecordManager = $this->getMockBuilder(RecordManagerReadListViewEstate::class)
			->disableOriginalConstructor()
			->getMock();
		$pMockRecordManagerFactory = $this->getMockBuilder(RecordManagerFactory::class)
			->setMethods(['create'])
			->getMock();
		$pMockRecordManagerFactory->method('create')
			->with(onOfficeSDK::MODULE_ADDRESS, RecordManagerFactory::ACTION_READ, $this->anything())
			->will($this->returnValue($pMockRecordManager));

		$pGeoPosition = $this->getMockBuilder(GeoPositionFieldHandler::class)
			->setMethods(['getActiveFields'])
			->setConstructorArgs([$pMockRecordManagerFactory])
			->getMock();
		$pGeoPosition->method('getActiveFields')->will($this->returnValue($activeGeoFields));

		$pCompoundFieldsMocker = $this->getMockBuilder(CompoundFieldsFilter::class)
			->setMethods(['mergeListFilterableFields'])
			->getMock();
		$pCompoundFieldsMocker->method('mergeListFilterableFields')->will($this->returnValue(
			$expectation));

		$this->_pContainer->set(CompoundFieldsFilter::class, $pCompoundFieldsMocker);

		$pFieldsCollection = new FieldsCollection;
		$pOutputFields = $this->_pContainer->get(OutputFields::class);
		$this->assertEquals($expectation, $pOutputFields->getVisibleFilterableFields
			($this->_pDataListView, $pFieldsCollection, $pGeoPosition, $this->_pInputVariableReader));
	}
}
