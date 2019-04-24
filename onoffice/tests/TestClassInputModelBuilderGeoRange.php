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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\GeoPositionFieldHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderGeoRange;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelLabel;
use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassInputModelBuilderGeoRange
	extends WP_UnitTestCase
{
	/** @var InputModelBuilderGeoRange */
	private $_pSubject = null;


	/**
	 *
	 */

	public function testBuild()
	{
		$pView = new DataListView(13, 'test');
		$pGenerator = $this->_pSubject->build($pView);
		$expectedFields = $this->getExpectedFields();
		$amountBooleanFields = $this->getAmountOfBooleanGeoFields();

		foreach ($pGenerator as $index => $pInputModel) {
			if ($index % 2 === 0 && $index < ($amountBooleanFields * 2)) {
				$this->assertInstanceOf(InputModelLabel::class, $pInputModel);
			} else {
				$this->assertInstanceOf(InputModelDB::class, $pInputModel);
				$field = $pInputModel->getField();
				$this->assertContains($field, $expectedFields);
				$arrayIndex = array_search($field, $expectedFields, true);
				$this->assertNotFalse($arrayIndex);
				unset($expectedFields[$arrayIndex]);
			}
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getExpectedFields(): array
	{
		$pReflectionClass = new ReflectionClass(InputModelDBFactoryConfigGeoFields::class);
		$allConstants = $pReflectionClass->getConstants();
		$fieldnameConstants = array_filter($allConstants, function($constant) {
			return __String::getNew($constant)->startsWith('FIELDNAME_');
		}, ARRAY_FILTER_USE_KEY);

		return $fieldnameConstants;
	}


	/**
	 *
	 * @return int
	 *
	 */

	private function getAmountOfBooleanGeoFields(): int
	{
		$pGeoFieldFactory = new InputModelDBFactoryConfigGeoFields(onOfficeSDK::MODULE_ESTATE);
		return count($pGeoFieldFactory->getBooleanFields());
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$values = [
			InputModelDBFactoryConfigGeoFields::FIELDNAME_COUNTRY_ACTIVE => '1',
			InputModelDBFactoryConfigGeoFields::FIELDNAME_STREET_ACTIVE => '1',
		];

		$pMockGeoPositionFieldHandler = $this->getMockBuilder(GeoPositionFieldHandler::class)
			->setMethods(['getRadiusValue', 'getActiveFields', 'readValues'])
			->getMock();
		$pMockGeoPositionFieldHandler
			->method('getRadiusValue')
			->will($this->returnValue(10));
		$pMockGeoPositionFieldHandler
			->method('getActiveFields')
			->will($this->returnValue($values));

		$this->_pSubject = new InputModelBuilderGeoRange(onOfficeSDK::MODULE_ESTATE, $pMockGeoPositionFieldHandler);
	}
}
