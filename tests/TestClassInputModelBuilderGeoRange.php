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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\GeoPositionFieldHandler;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionFrontend;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderGeoRange;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Utility\__String;
use ReflectionClass;
use WP_UnitTestCase;

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

	public function testConstruct()
	{
		$pInputModelBuilderGeoRange = new InputModelBuilderGeoRange(onOfficeSDK::MODULE_ESTATE);
		$this->assertInstanceOf(GeoPositionFieldHandler::class,
			$pInputModelBuilderGeoRange->getGeoPositionFieldHandler());
		$this->assertInstanceOf(InputModelDBFactory::class,
			$pInputModelBuilderGeoRange->getInputModelFactory());
		$this->assertInstanceOf(FieldModuleCollectionDecoratorGeoPositionFrontend::class,
			$pInputModelBuilderGeoRange->getFieldsCollection());
	}


	/**
	 *
	 */

	public function testBuild()
	{
		$pView = new DataListView(13, 'test');
		$pGenerator = $this->_pSubject->build($pView);
		$expectedFields = $this->getExpectedFields();
		$outputCount = 0;

		foreach ($pGenerator as $pInputModel) {
			$outputCount++;
			$this->assertInstanceOf(InputModelDB::class, $pInputModel);
			$field = $pInputModel->getField();
			$this->assertContains($field, $expectedFields);
			$arrayIndex = array_search($field, $expectedFields, true);
			$this->assertNotFalse($arrayIndex);
			unset($expectedFields[$arrayIndex]);
		}
		$this->assertEquals(7, $outputCount);
	}


	/**
	 *
	 */

	public function testBuildForApplicantSearchForm()
	{
		$pView = new DataFormConfigurationApplicantSearch();
		$pView->setFormType(Form::TYPE_APPLICANT_SEARCH);
		$pView->setId(14);
		$models = iterator_to_array($this->_pSubject->build($pView));
		$this->assertCount(4, $models);

		foreach ($models as $pInputModel) {
			$this->assertNotEquals(InputModelDBFactoryConfigGeoFields::FIELDNAME_RADIUS_ACTIVE, $pInputModel->getField());

			// remove this assertion if more get added
			$this->assertNotEquals(InputModelDBFactoryConfigGeoFields::FIELDNAME_GEO_ORDER, $pInputModel->getField());
		}
	}


	/**
	 *
	 */

	public function testGeoLabels()
	{
		$expectation = [
			'street,zip,city,country,radius' => 'Street, Postal Code, City, Country, Radius (km)',
			'radius,street,zip,city,country' => 'Radius (km), Street, Postal Code, City, Country',
		];
		foreach ([onOfficeSDK::MODULE_ESTATE, onOfficeSDK::MODULE_SEARCHCRITERIA] as $module) {
			$this->prepare($module);
			$pView = new DataListView(13, 'test');
			$pGenerator = $this->_pSubject->build($pView);
			for ($i = 0; $i < 6; $i++) {
				$pGenerator->next();
			}
			$pInputModelGeo = $pGenerator->current();
			$this->assertInstanceOf(InputModelDB::class, $pInputModelGeo);
			$this->assertEquals('geo_order', $pInputModelGeo->getField());
			$this->assertEquals('Order', $pInputModelGeo->getLabel());
			$this->assertEquals($expectation, $pInputModelGeo->getValuesAvailable());
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
	 * @before
	 *
	 */

	public function prepare(string $module = null)
	{
		$values = [
			InputModelDBFactoryConfigGeoFields::FIELDNAME_COUNTRY_ACTIVE => '1',
			InputModelDBFactoryConfigGeoFields::FIELDNAME_STREET_ACTIVE => '1',
		];

		$pMockGeoPositionFieldHandler = $this->getMockBuilder(GeoPositionFieldHandler::class)
			->onlyMethods(['getRadiusValue', 'getActiveFields', 'readValues'])
			->getMock();
		$pMockGeoPositionFieldHandler
			->method('getRadiusValue')
			->will($this->returnValue(10));
		$pMockGeoPositionFieldHandler
			->method('getActiveFields')
			->will($this->returnValue($values));

		$pFieldsCollection = new FieldModuleCollectionDecoratorGeoPositionFrontend(new FieldsCollection);

		$this->_pSubject = new InputModelBuilderGeoRange
			($module ?? onOfficeSDK::MODULE_ESTATE, $pMockGeoPositionFieldHandler, $pFieldsCollection);
	}
}
