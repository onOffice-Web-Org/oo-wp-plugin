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
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Filter\GeoSearchBuilderFromInputVars;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassGeoSearchBuilderFromInputVars
	extends WP_UnitTestCase
{
	/** @var InputVariableReaderConfigTest */
	private $_pVariableReaderConfig = null;

	/** @var GeoSearchBuilderFromInputVars */
	private $_pGeoSearchBuilderFromInputVars = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pVariableReaderConfig = new InputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$this->_pVariableReaderConfig->setFieldTypeByModule('street', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pVariableReaderConfig->setFieldTypeByModule('radius', $module, FieldTypes::FIELD_TYPE_INTEGER);
		$this->_pVariableReaderConfig->setFieldTypeByModule('ort', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pVariableReaderConfig->setFieldTypeByModule('zip', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pVariableReaderConfig->setFieldTypeByModule('country', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$pEstateListVariableReader = new InputVariableReader(onOfficeSDK::MODULE_ESTATE,
			$this->_pVariableReaderConfig);
		$pDataView = new DataListView(13, 'test');

		$pGeoPositionFieldHandler = $this->getMockBuilder(GeoPositionFieldHandler::class)
			->setMethods(['readValues', 'getActiveFields', 'getActiveFieldsWithValue'])
			->getMock();
		$pGeoPositionFieldHandler->method('getActiveFields')->will($this->returnValue([
			GeoPosition::ESTATE_LIST_SEARCH_COUNTRY => InputModelDBFactoryConfigGeoFields::FIELDNAME_COUNTRY_ACTIVE,
			GeoPosition::ESTATE_LIST_SEARCH_STREET => InputModelDBFactoryConfigGeoFields::FIELDNAME_STREET_ACTIVE,
			GeoPosition::ESTATE_LIST_SEARCH_ZIP => InputModelDBFactoryConfigGeoFields::FIELDNAME_ZIP_ACTIVE,
			GeoPosition::ESTATE_LIST_SEARCH_RADIUS => InputModelDBFactoryConfigGeoFields::FIELDNAME_RADIUS_ACTIVE,
		]));
		$pGeoPositionFieldHandler->method('getActiveFieldsWithValue')->will($this->returnValue([
			GeoPosition::ESTATE_LIST_SEARCH_COUNTRY => 'DEU',
			GeoPosition::ESTATE_LIST_SEARCH_STREET => null,
			GeoPosition::ESTATE_LIST_SEARCH_ZIP => null,
			GeoPosition::ESTATE_LIST_SEARCH_RADIUS => 20,
		]));

		$this->_pGeoSearchBuilderFromInputVars = new GeoSearchBuilderFromInputVars($pEstateListVariableReader, $pGeoPositionFieldHandler);
		$this->_pGeoSearchBuilderFromInputVars->setViewProperty($pDataView);
	}


	/**
	 *
	 */

	public function testBuildParameters()
	{
		$this->_pVariableReaderConfig->setValue('street', 'Charlottenburger Allee');
		$this->_pVariableReaderConfig->setValue('radius', 20);
		$this->_pVariableReaderConfig->setValue('zip', '52068');
		$this->_pVariableReaderConfig->setValue('country', 'Deutschland');
		$parameters = $this->_pGeoSearchBuilderFromInputVars->buildParameters();
		$expectedParameters = [
			'country' => 'Deutschland',
			'zip' => '52068',
			'street' => 'Charlottenburger Allee',
			'radius' => 20
		];

		$this->assertEqualSets($expectedParameters, $parameters);
	}


	/**
	 *
	 */

	public function testBuildParametersIncomplete()
	{
		$this->_pVariableReaderConfig->setValue('country', '');
		$this->_pVariableReaderConfig->setValue('radius', 20);
		$this->_pVariableReaderConfig->setValue('zip', '52068');
		$parameters = $this->_pGeoSearchBuilderFromInputVars->buildParameters();
		$this->assertEquals([], $parameters);
	}


	/**
	 *
	 */

	public function testMissingRadiusParameter()
	{
		$this->_pVariableReaderConfig->setValue('street', 'Teststr');
		$this->_pVariableReaderConfig->setValue('radius', 10);
		$this->_pVariableReaderConfig->setValue('zip', '52072');
		$parameters = $this->_pGeoSearchBuilderFromInputVars->buildParameters();

		$this->assertEquals([
			'country' => 'DEU',
			'zip' => '52072',
			'street' => 'Teststr',
			'radius' => 10,
		], $parameters);
	}


	/**
	 *
	 * @expectedException \Exception
	 * @expectedExceptionMessage pView cannot be null
	 *
	 */

	public function testNoView()
	{
		$pGeoSearchBuilderFromInputVars = new GeoSearchBuilderFromInputVars();
		$pGeoSearchBuilderFromInputVars->buildParameters();
	}
}
