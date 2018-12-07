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
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Controller\InputVariableReaderConfigTest;
use onOffice\WPlugin\Filter\GeoSearchBuilderFromInputVars;
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
	 */

	public function setUp()
	{
		parent::setUp();
		$this->_pVariableReaderConfig = new InputVariableReaderConfigTest();
		$module = onOfficeSDK::MODULE_ESTATE;
		$this->_pVariableReaderConfig->setFieldTypeByModule('street', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pVariableReaderConfig->setFieldTypeByModule('radius', $module, FieldTypes::FIELD_TYPE_INTEGER);
		$this->_pVariableReaderConfig->setFieldTypeByModule('ort', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pVariableReaderConfig->setFieldTypeByModule('zip', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pVariableReaderConfig->setFieldTypeByModule('country', $module, FieldTypes::FIELD_TYPE_VARCHAR);
		$pEstateListVariableReader = new InputVariableReader(onOfficeSDK::MODULE_ESTATE,
			$this->_pVariableReaderConfig);
		$this->_pGeoSearchBuilderFromInputVars = new GeoSearchBuilderFromInputVars($pEstateListVariableReader);
	}


	/**
	 *
	 */

	public function testBuildParameters()
	{
		$this->_pVariableReaderConfig->setValue('street', 'Charlottenburger Allee');
		$this->_pVariableReaderConfig->setValue('radius', 10);
		$this->_pVariableReaderConfig->setValue('zip', '52068');
		$this->_pVariableReaderConfig->setValue('country', 'Deutschland');
		$parameters = $this->_pGeoSearchBuilderFromInputVars->buildParameters();
		$expectedParameters = [
			'country' => 'Deutschland',
			'zip' => '52068',
			'street' => 'Charlottenburger Allee',
			'radius' => 10
		];

		$this->assertEqualSets($expectedParameters, $parameters);
	}


	/**
	 *
	 */

	public function testMissingRequiredParameter()
	{
		$this->_pVariableReaderConfig->setValue('street', 'Charlottenburger Allee');
		$this->_pVariableReaderConfig->setValue('radius', 10);
		$this->_pVariableReaderConfig->setValue('zip', '52068');
		$parameters = $this->_pGeoSearchBuilderFromInputVars->buildParameters();

		$this->assertEquals([], $parameters);
	}
}
