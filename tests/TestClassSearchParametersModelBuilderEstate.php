<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\SearchParametersModelBuilderEstate;
use onOffice\WPlugin\Controller\SortList\SortListDataModel;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

class TestClassSearchParametersModelBuilderEstate
	extends \WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->disableOriginalConstructor()
			->setMethods(['addFieldsAddressEstate'])
			->getMock();
		$pFieldsCollectionBuilderShort
			->method('addFieldsAddressEstate')
			->willReturnCallback(function(FieldsCollection $pFieldsCollection)
				use ($pFieldsCollectionBuilderShort): FieldsCollectionBuilderShort {
				$pField1 = new Field('testField1', onOfficeSDK::MODULE_ESTATE,
					'Test Field 1');
				$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField1);
				$pFieldsCollection->addField(new Field('testField2', onOfficeSDK::MODULE_ESTATE,
					'Test Field 2'));
				$pField3 = new Field('testField3', onOfficeSDK::MODULE_ESTATE,
					'Test Field 3');
				$pField3->setType(FieldTypes::FIELD_TYPE_DATETIME);
				$pFieldsCollection->addField($pField3);
				return $pFieldsCollectionBuilderShort;
			});

		$this->_pContainer->set(FieldsCollectionBuilderShort::class, $pFieldsCollectionBuilderShort);
	}


	public function testBuildSearchParametersModel()
	{
		$_GET = [
			'testField1' => ['hi', 'other'],
			'testField2' => 'bye',
			'testField3__von' => '2020-03-20 07:33:33',
			'testField3__bis' => '2020-05-01 01:01:01',
		];

		$pDataListView = new DataListView(13, 'testlist');
		$pDataListView->setFilterableFields(['testField1', 'testField2', 'testField3', GeoPosition::FIELD_GEO_POSITION]);
		$pSortListDataModelInput = new SortListDataModel;
		$pSortListDataModelInput->setAdjustableSorting(true);
		$pSubject = $this->_pContainer->get(SearchParametersModelBuilderEstate::class);
		$pSearchParametersResult = $pSubject->buildSearchParametersModel($pDataListView, $pSortListDataModelInput);
		$pSearchParametersExpectation = new SearchParametersModel;
		$pSearchParametersExpectation->setParameters([
			'testField1' => ['hi', 'other'],
			'testField2' => 'bye',
			'testField3' => '',
			'testField3__von' => '2020-03-20 07:33:33',
			'testField3__bis' => '2020-05-01 01:01:01',
			'sortby' => '',
			'sortorder' => '',
		]);
		$pSearchParametersExpectation->setAllowedGetParameters([
			'testField1',
			'testField2',
			'testField3',
			'testField3__von',
			'testField3__bis',
			'sortby',
			'sortorder',
		]);
		$this->assertEquals($pSearchParametersExpectation, $pSearchParametersResult);
	}
}