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

use DI\Container;
use DI\ContainerBuilder;
use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldCategoryToFieldConverterSearchCriteriaBackendNoGeo;
use onOffice\WPlugin\Field\Collection\FieldLoaderGeneric;
use onOffice\WPlugin\Field\Collection\FieldLoaderSearchCriteria;
use onOffice\WPlugin\Field\Collection\FieldRowConverterSearchCriteria;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Region\RegionController;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;
use function json_decode;


/**
 *
 * @covers onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort::<private>
 * @covers onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort::__construct
 *
 */

class TestClassFieldsCollectionBuilderShort
	extends WP_UnitTestCase
{
	/** @var FieldsCollectionBuilderShort */
	private $_pSubject = null;

	/** @var array */
	private $_exampleRowsByModule = [
		'address' => [
			'testAddressField1' => [
				'label' => 'testField Label',
				'module' => 'address',
				'content' => 'Stammdaten',
				'permittedvalues' => [],
				'type' => 'varchar',
				'rangefield' => false,
			],
		],
		'estate' => [
			'testEstateField2' => [
				'label' => 'testField2 Label',
				'module' => 'estate',
				'content' => 'Technische-Angaben',
				'permittedvalues' => [],
				'type' => 'varchar',
				'rangefield' => false,
			],
		],
		'searchcriteria' => [
			'testSKField1' => [
				'label' => 'testField2 Label',
				'module' => 'searchcriteria',
				'content' => 'Technische-Angaben',
				'permittedvalues' => [],
				'type' => 'varchar',
				'rangefield' => false,
			],
		],
	];


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pFieldLoaderGeneric = $this->getMockBuilder(FieldLoaderGeneric::class)
			->disableOriginalConstructor()
			->setMethods(['load'])
			->getMock();
		$pFieldLoaderGeneric->method('load')->will($this->returnCallback(function(): Generator {
			yield from $this->_exampleRowsByModule['address'] + $this->_exampleRowsByModule['estate'];
		}));

		$pFieldLoaderSearchCriteria = $this->getMockBuilder(FieldLoaderSearchCriteria::class)
			->disableOriginalConstructor()
			->setMethods(['load'])
			->getMock();
		$pFieldLoaderSearchCriteria->method('load')->will($this->returnCallback(function(): Generator {
			yield from $this->_exampleRowsByModule['searchcriteria'];
		}));

		$pSDKWrapper = new SDKWrapperMocker();
		$searchCriteriaFieldsParameters = ['language' => 'ENG', 'additionalTranslations' => true];
		$responseGetSearchcriteriaFields = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseGetSearchcriteriaFieldsENG.json'), true);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields', '',
			$searchCriteriaFieldsParameters, null, $responseGetSearchcriteriaFields);

		$parametersGetFieldList = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'fieldList' => ['benutzer'],
			'language' => 'ENG',
			'modules' => [onOfficeSDK::MODULE_ESTATE],
			'realDataTypes' => true
		];
		$responseGetSupervisorFields = json_decode
		 	(file_get_contents(__DIR__.'/resources/ApiResponseSupervisorFields.json'), true);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
			$parametersGetFieldList, null, $responseGetSupervisorFields);

		$responseGetListSupervisors = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseListSupervisors.json'), true);
		$pSDKWrapper->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'users', '',
			[], null, $responseGetListSupervisors);
		$pRegionController = $this->getMockBuilder(RegionController::class)
			->disableOriginalConstructor()
			->getMock();

		$pContainer->set(SDKWrapper::class, $pSDKWrapper);
		$pContainer->set(RegionController::class, $pRegionController);
		$pContainer->set(FieldLoaderGeneric::class, $pFieldLoaderGeneric);
		$pContainer->set(FieldLoaderSearchCriteria::class, $pFieldLoaderSearchCriteria);
		$this->_pSubject = $pContainer->get(FieldsCollectionBuilderShort::class);
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort::addFieldsAddressEstate
	 *
	 */

	public function testAddFieldsAddressEstate()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertEmpty($pFieldsCollection->getAllFields());
		$this->assertSame($this->_pSubject,
			$this->_pSubject->addFieldsAddressEstate($pFieldsCollection));
		$this->assertCount(2, $pFieldsCollection->getAllFields());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort::addFieldsSearchCriteria
	 *
	 */

	public function testAddFieldsSearchCriteria()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertSame($this->_pSubject, $this->_pSubject->addFieldsSearchCriteria($pFieldsCollection));
		$this->assertCount(11, $pFieldsCollection->getAllFields());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort::addFieldsFormBackend
	 *
	 */

	public function testAddFieldsFormBackend()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertSame($this->_pSubject, $this->_pSubject->addFieldsFormBackend($pFieldsCollection,Form::TYPE_INTEREST));
		$this->assertCount(5, $pFieldsCollection->getAllFields());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort::addFieldsFormFrontend
	 *
	 */

	public function testAddFieldsFormFrontend()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertSame($this->_pSubject, $this->_pSubject->addFieldsFormFrontend($pFieldsCollection));
		$this->assertCount(10, $pFieldsCollection->getAllFields());
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort::addFieldsSearchCriteriaSpecificBackend
	 *
	 */

	public function testAddFieldsSearchCriteriaSpecificBackend()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertSame($this->_pSubject, $this->_pSubject->addFieldsSearchCriteriaSpecificBackend
			($pFieldsCollection));
		$this->assertCount(2, $pFieldsCollection->getAllFields());
	}


	/**
	 *
	 * @coversNothing
	 *
	 */

	public function testCombination()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->_pSubject
			->addFieldsAddressEstate($pFieldsCollection)
			->addFieldsFormBackend($pFieldsCollection,Form::TYPE_INTEREST)
			->addFieldsFormFrontend($pFieldsCollection)
			->addFieldsSearchCriteria($pFieldsCollection)
			->addFieldsSearchCriteriaSpecificBackend($pFieldsCollection);
		$this->assertCount(30, $pFieldsCollection->getAllFields());
	}

	/**
	 *
	 * @covers onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort::addFieldSupervisorForSearchCriteria
	 *
	 */
	public function testAddFieldSupervisorForSearchCriteria()
	{
		$pFieldsCollection = new FieldsCollection();
		$this->assertSame($this->_pSubject, $this->_pSubject->addFieldSupervisorForSearchCriteria($pFieldsCollection));
		$this->assertCount(1, $pFieldsCollection->getAllFields());
	}
}
