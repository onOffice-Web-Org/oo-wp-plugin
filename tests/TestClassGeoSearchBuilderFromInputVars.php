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

use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
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

	/** @var APIClientActionGeneric */
	private $_pAPIClientActionMock = null;

	/** @var GeoPositionFieldHandler */
	private $_pGeoPositionFieldHandler = null;

	/**
	 * @before
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

		$this->_pGeoPositionFieldHandler = $this->getMockBuilder(GeoPositionFieldHandler::class)
			->setMethods(['readValues', 'getActiveFields', 'getActiveFieldsWithValue', 'getRadiusValue'])
			->getMock();

		$this->_pAPIClientActionMock = $this->getMockBuilder(APIClientActionGeneric::class)
			->setConstructorArgs([new SDKWrapperMocker(), '', ''])
			->setMethods(['getResultRecords', 'withActionIdAndResourceType', 'addRequestToQueue'])
			->getMock();

		$this->_pGeoSearchBuilderFromInputVars = new GeoSearchBuilderFromInputVars
			($pEstateListVariableReader, $this->_pGeoPositionFieldHandler, $this->_pAPIClientActionMock);
		$this->_pGeoSearchBuilderFromInputVars->setViewProperty($pDataView);
	}

	public function testBuildParameters()
	{
		$this->setActiveFields();
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

	public function testBuildParametersIncomplete()
	{
		$this->setActiveFields();
		$this->_pVariableReaderConfig->setValue('country', '');
		$this->_pVariableReaderConfig->setValue('radius', 20);
		$this->_pVariableReaderConfig->setValue('zip', '52068');
		$parameters = $this->_pGeoSearchBuilderFromInputVars->buildParameters();
		$this->assertEquals([], $parameters);
	}

	public function testMissingRadiusParameter()
	{
		$this->setActiveFields();
		$this->_pVariableReaderConfig->setValue('street', 'Teststr');
		$this->_pVariableReaderConfig->setValue('zip', '52072');
		$parameters = $this->_pGeoSearchBuilderFromInputVars->buildParameters();

		$this->assertEquals([
			'country' => 'DEU',
			'zip' => '52072',
			'street' => 'Teststr',
			'radius' => 20,
		], $parameters);
	}

	public function testEmptyRadiusParameterUsingDefaultRadius()
	{
		$this->setActiveFields();
		$this->_pVariableReaderConfig->setValue('zip', '52070');
		$this->_pVariableReaderConfig->setValue('radius', '');
		$parameters = $this->_pGeoSearchBuilderFromInputVars->buildParameters();

		$this->assertEquals([
			'country' => 'DEU',
			'zip' => '52070',
			'radius' => 20,
			'street' => null,
		], $parameters);
	}

	public function testEmptyRadiusParameterUsingFallbackRadius()
	{
		$this->setActiveFields(['radius']);
		$this->_pVariableReaderConfig->setValue('zip', '52070');
		$this->_pVariableReaderConfig->setValue('radius', '');
		$parameters = $this->_pGeoSearchBuilderFromInputVars->buildParameters();

		$this->assertEquals([
			'country' => 'DEU',
			'zip' => '52070',
			'radius' => 10,
			'street' => null,
		], $parameters);
	}

	public function testMissingCountryParameter()
	{
		$this->_pAPIClientActionMock->expects($this->once())
				->method('withActionIdAndResourceType')
				->with(onOfficeSDK::ACTION_ID_READ, 'impressum')
				->will($this->returnSelf());
		$this->_pAPIClientActionMock->expects($this->once())
				->method('addRequestToQueue')
				->will($this->returnSelf());
		$this->setActiveFields(['country']);
		$this->_pAPIClientActionMock->method('getResultRecords')->will
			($this->returnValue([0 => ['elements' => ['country' => 'FRA']]]));
		$this->_pVariableReaderConfig->setValue('street', 'Teststr');
		$this->_pVariableReaderConfig->setValue('radius', 10);
		$this->_pVariableReaderConfig->setValue('zip', '52072');
		$parameters = $this->_pGeoSearchBuilderFromInputVars->buildParameters();

		$this->assertEquals([
			'country' => 'FRA',
			'zip' => '52072',
			'street' => 'Teststr',
			'radius' => 10,
		], $parameters);
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage pView cannot be null
	 */
	public function testNoView()
	{
		$pGeoSearchBuilderFromInputVars = new GeoSearchBuilderFromInputVars();
		$pGeoSearchBuilderFromInputVars->buildParameters();
	}

	/**
	 * @param array $blacklist
	 */
	private function setActiveFields(array $blacklist = [])
	{
		$activeFieldsDefault = [
			GeoPosition::ESTATE_LIST_SEARCH_COUNTRY => InputModelDBFactoryConfigGeoFields::FIELDNAME_COUNTRY_ACTIVE,
			GeoPosition::ESTATE_LIST_SEARCH_STREET => InputModelDBFactoryConfigGeoFields::FIELDNAME_STREET_ACTIVE,
			GeoPosition::ESTATE_LIST_SEARCH_ZIP => InputModelDBFactoryConfigGeoFields::FIELDNAME_ZIP_ACTIVE,
			GeoPosition::ESTATE_LIST_SEARCH_RADIUS => InputModelDBFactoryConfigGeoFields::FIELDNAME_RADIUS_ACTIVE,
		];

		$activeFieldsWithValuesDefault = [
			GeoPosition::ESTATE_LIST_SEARCH_COUNTRY => 'DEU',
			GeoPosition::ESTATE_LIST_SEARCH_STREET => null,
			GeoPosition::ESTATE_LIST_SEARCH_ZIP => null,
			GeoPosition::ESTATE_LIST_SEARCH_RADIUS => 20,
		];

		$activeFields = array_diff_key($activeFieldsDefault, array_flip($blacklist));
		$activeFieldsWithValues = array_diff_key($activeFieldsWithValuesDefault, array_flip($blacklist));

		$this->_pGeoPositionFieldHandler->method('getActiveFields')
			->will($this->returnValue($activeFields));
		$this->_pGeoPositionFieldHandler->method('getRadiusValue')
			->will($this->returnValue($activeFieldsWithValues['radius'] ?? 10));
		$this->_pGeoPositionFieldHandler->method('getActiveFieldsWithValue')
			->will($this->returnValue($activeFieldsWithValues));
	}
}
