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

use DI\ContainerBuilder;
use onOffice\WPlugin\API\DataViewToAPI\DataListViewAddressToAPIParameters;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFieldsFilter;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListViewAddressFactory;
use onOffice\WPlugin\Filter\FilterBuilderInputVariablesFactory;
use WP_UnitTestCase;
use const ONOFFICE_DI_CONFIG_PATH;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassDataListViewAddressToAPIParameters
	extends WP_UnitTestCase
{

	/** @var DataListViewAddressToAPIParameters */
	private $_pDataListViewAddressToAPIParameters = null;

	/** @var DataListViewAddress */
	private $_pDataListViewAddress = null;


	/** @var array */
	private $_filter = [
		'testField' => ['op' => '=', 'val' => 1],
		'testField2' => ['op' => 'like', 'val' => 'a%'],
	];



	/**
	 *
	 */

	public function testBuildParameters()
	{
		$result = $this->_pDataListViewAddressToAPIParameters->buildParameters(
				['Vorname', 'Name', 'Zusatz1'], $this->_pDataListViewAddress, 1);

		$expectedResult = [
			'data' => ['Vorname', 'Name', 'Zusatz1'],
			'listoffset' => 0,
			'listlimit' => 15,
			'sortby' => 'Vorname',
			'sortorder' => 'desc',
			'filter' => $this->_filter,
			'filterid' => 12,
			'outputlanguage' => 'ENG',
			'formatoutput' => true,
		];

		$this->assertEquals($expectedResult, $result);
	}


	/**
	 *
	 */

	public function testBuildParametersImage()
	{
		$this->_pDataListViewAddress->setShowPhoto(true);
		$result = $this->_pDataListViewAddressToAPIParameters->buildParameters(
				['Vorname', 'Name', 'Zusatz1'], $this->_pDataListViewAddress, 1);

		$expectedResult = [
			'data' => ['Vorname', 'Name', 'Zusatz1', 'imageUrl'],
			'listoffset' => 0,
			'listlimit' => 15,
			'sortby' => 'Vorname',
			'sortorder' => 'desc',
			'filter' => $this->_filter,
			'filterid' => 12,
			'outputlanguage' => 'ENG',
			'formatoutput' => true,
		];

		$this->assertEquals($expectedResult, $result);
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pDataListViewAddress = new DataListViewAddress(1, 'test');
		$this->_pDataListViewAddress->setFields(['Vorname', 'Name', 'Zusatz1']);
		$this->_pDataListViewAddress->setSortby('Vorname');
		$this->_pDataListViewAddress->setSortorder('desc');
		$this->_pDataListViewAddress->setFilterId(12);
		$this->_pDataListViewAddress->setRecordsPerPage(15);

		$pContainerBuilder = new ContainerBuilder();
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();

		$filter = [
			'testField' => ['op' => '=', 'val' => 1],
			'testField2' => ['op' => 'like', 'val' => 'a%'],
		];

		$pFieldsCollectionBuilderShort = $pContainer->get(FieldsCollectionBuilderShort::class);
		$pFilterBuilderFactory = $pContainer->get(FilterBuilderInputVariablesFactory::class);
		$pCompoundFieldsFilter = $pContainer->get(CompoundFieldsFilter::class);

		$pFactory = $this->getMockBuilder(DefaultFilterBuilderListViewAddressFactory::class)
				->setConstructorArgs([$pFieldsCollectionBuilderShort, $pCompoundFieldsFilter, $pFilterBuilderFactory])
				->getMock();

		$pDefaultFilterBuilderListViewAddress = new DefaultFilterBuilderListViewAddressMocker($filter);

		$pFactory->method('create')->with($this->_pDataListViewAddress)->will($this->returnValue($pDefaultFilterBuilderListViewAddress));

		$this->_pDataListViewAddressToAPIParameters = new DataListViewAddressToAPIParameters($pFactory);
	}
}