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

use onOffice\tests\DefaultFilterBuilderListViewAddressMocker;
use onOffice\WPlugin\API\DataViewToAPI\DataListViewAddressToAPIParameters;
use onOffice\WPlugin\DataView\DataListViewAddress;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassDataListViewAddressToAPIParameters
	extends WP_UnitTestCase
{
	/** @var array */
	private $_filter = [
		'testField' => ['op' => '=', 'val' => 1],
		'testField2' => ['op' => 'like', 'val' => 'a%'],
	];


	/**
	 *
	 */

	public function testConstruct()
	{
		$pDataListViewAddressToAPIParameters = $this->getNewDataListViewToAPIParameters();
		$pDataListViewAddress = $pDataListViewAddressToAPIParameters->getDataListView();
		$this->assertEquals($pDataListViewAddress, $pDataListViewAddressToAPIParameters->getDataListView());
		$this->assertEquals(1, $pDataListViewAddressToAPIParameters->getPage());
	}


	/**
	 *
	 */

	public function testSetPage()
	{
		$pDataListViewAddressToAPIParameters = $this->getNewDataListViewToAPIParameters();
		$this->assertEquals(1, $pDataListViewAddressToAPIParameters->getPage());
		$pDataListViewAddressToAPIParameters->setPage(2);
		$this->assertEquals(2, $pDataListViewAddressToAPIParameters->getPage());
	}


	/**
	 *
	 */

	public function testBuildParameters()
	{
		$pDataListViewAddressToAPIParameters = $this->getNewDataListViewToAPIParameters();
		$pDataAddressList = $pDataListViewAddressToAPIParameters->getDataListView();
		$result = $pDataListViewAddressToAPIParameters->buildParameters($pDataAddressList->getFields());
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
		$pDataListViewAddressToAPIParameters = $this->getNewDataListViewToAPIParameters();
		$pDataAddressList = $pDataListViewAddressToAPIParameters->getDataListView();
		$pDataAddressList->setShowPhoto(true);
		$result = $pDataListViewAddressToAPIParameters->buildParameters($pDataAddressList->getFields());
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
	 * @return DataListViewAddressToAPIParameters
	 *
	 */

	private function getNewDataListViewToAPIParameters(): DataListViewAddressToAPIParameters
	{
		$pDataListViewAddress = new DataListViewAddress(1, 'test');
		$pDataListViewAddress->setFields(['Vorname', 'Name', 'Zusatz1']);
		$pDataListViewAddress->setSortby('Vorname');
		$pDataListViewAddress->setSortorder('desc');
		$pDataListViewAddress->setFilterId(12);
		$pDataListViewAddress->setRecordsPerPage(15);

		$filter = [
			'testField' => ['op' => '=', 'val' => 1],
			'testField2' => ['op' => 'like', 'val' => 'a%'],
		];

		$pDefaultFilterBuilderListViewAddress = new DefaultFilterBuilderListViewAddressMocker($filter);
		$pDataListViewAddressToAPIParameters = new DataListViewAddressToAPIParameters
			($pDataListViewAddress, $pDefaultFilterBuilderListViewAddress);
		return $pDataListViewAddressToAPIParameters;
	}
}
