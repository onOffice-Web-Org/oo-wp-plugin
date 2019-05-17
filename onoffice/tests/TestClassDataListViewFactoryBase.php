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

declare (Strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactoryBase;
use onOffice\WPlugin\DataView\DataViewFilterableFields;
use onOffice\WPlugin\Record\RecordManagerRead;
use ReflectionMethod;
use WP_UnitTestCase;

/**
 *
 */

class TestClassDataListViewFactoryBase
	extends WP_UnitTestCase
{
	/** @var DataListViewFactoryBase */
	private $_pMockDataListViewFactoryBase = null;

	/** @var RecordManagerRead */
	private $_pMockRecordManagerRead = null;


	/**
	 *
	 */

	public function testGetListviewByNameSuccess()
	{
		$this->_pMockRecordManagerRead
			->method('getRowByName')
			->will($this->returnValue(['name' => 'test']));
		$pResult = $this->_pMockDataListViewFactoryBase->getListViewByName('test');
		$this->assertInstanceOf(DataViewFilterableFields::class, $pResult);
	}


	/**
	 *
	 * @expectedException onOffice\WPlugin\DataView\UnknownViewException
	 *
	 */

	public function testGetListviewByNameError()
	{
		$this->_pMockRecordManagerRead
			->method('getRowByName')
			->will($this->returnValue(null));
		$pResult = $this->_pMockDataListViewFactoryBase->getListViewByName('asd');
		$this->assertInstanceOf(DataListView::class, $pResult);
	}


	/**
	 *
	 */

	public function testGetRecordManagerRead()
	{
		$pRecordManager = $this->_pMockDataListViewFactoryBase->getRecordManagerRead();
		$this->assertInstanceOf(RecordManagerRead::class, $pRecordManager);
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pMockDataListViewFactoryBase = $this->getMockBuilder(DataListViewFactoryBase::class)
			->setMethods(['createListViewByRow'])
			->getMock();
		$this->_pMockRecordManagerRead = $this->getMockBuilder(RecordManagerRead::class)
			->setMethods(['getRowByName', 'getRecords'])
			->getMock();
		$pNewRecordFinder = $this->_pMockRecordManagerRead;

		$pReflectionMethod = new ReflectionMethod($pMockDataListViewFactoryBase, 'setRecordManagerRead');
		$pReflectionMethod->setAccessible(true);
		$pReflectionMethod->invokeArgs($pMockDataListViewFactoryBase, [$pNewRecordFinder]);
		$this->_pMockDataListViewFactoryBase = $pMockDataListViewFactoryBase;
	}
}
