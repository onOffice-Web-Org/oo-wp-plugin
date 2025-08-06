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

namespace onOffice\tests;

use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use WP_UnitTestCase;
use wpdb;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassRecordManagerReadListViewAddress
	extends WP_UnitTestCase
{
	private $_pRecordManagerReadListViewAddress = null;

	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pRecordManagerReadListViewAddress = $this->getMockBuilder(RecordManagerReadListViewAddress::class)
			->getMock();
	}
	/**
	 *
	 */

	public function testConstruct()
	{
		$pRecordManager = new RecordManagerReadListViewAddress();
		$pMainTable = $pRecordManager->getMainTable();
		$pIdColumnMain = $pRecordManager->getIdColumnMain();

		$this->assertEquals('oo_plugin_listviews_address', $pMainTable);
		$this->assertEquals('listview_address_id', $pIdColumnMain);
	}

	private function getBasicFieldsArray(int $addressId): array
	{
		$fields = [
			[
				'address_fieldconfig_id' => '1',
				'form_id' => $addressId,
				'order' => '1',
				'fieldname' => 'Test 1',
				'filterable' => 0,
				'hidden' => 0,
			],
			[
				'address_fieldconfig_id' => '1',
				'form_id' => $addressId,
				'order' => '1',
				'fieldname' => 'Test 2',
				'filterable' => 0,
				'hidden' => 0,
			],
			[
				'address_fieldconfig_id' => '1',
				'form_id' => $addressId,
				'order' => '1',
				'fieldname' => 'Test 3',
				'filterable' => 0,
				'hidden' => 0,
			],
			[
				'address_fieldconfig_id' => '1',
				'form_id' => $addressId,
				'order' => '1',
				'fieldname' => 'Test 4',
				'filterable' => 0,
				'hidden' => 0,
			],
		];

		return $fields;
	}

	private function getBaseRow(int $addressId): array
	{
		return [
			'listview_address_id' => $addressId,
			'name' => 'testAddress' . $addressId,
			'filterId' => 0,
			'sortby' => 'KdNr',
			'sortorder' => 'ASC',
			'template' => 'testtemplate.php',
			'recordsPerPage' => 20,
			'showPhoto' => '0',
			'page_shortcode' => 500,
		];
	}

	public function testGetRecords()
	{
		$pFieldsForm = $this->_pRecordManagerReadListViewAddress->getRecords();
		$this->assertEquals(null,$pFieldsForm);
	}

	public function testGetRecordsSortedAlphabetically()
	{
		$pFieldsFormSortAlphabe = $this->_pRecordManagerReadListViewAddress->getRecordsSortedAlphabetically();
		$this->assertEquals([],$pFieldsFormSortAlphabe);
	}


	public function testGetRowByName()
	{
		$this->_pRecordManagerReadListViewAddress->method('getRowByName')->will($this->returnValueMap([
			['testAddress1', $this->getBaseRow(1)]
		]));
		$pRowAddress = $this->_pRecordManagerReadListViewAddress->getRowByName('testAddress1');
		$this->assertEquals(9, count($pRowAddress));
	}

	public function testGetFieldconfigByListviewId()
	{
		$this->_pRecordManagerReadListViewAddress->method('getFieldconfigByListviewId')->will($this->returnValueMap([
			[1, $this->getBasicFieldsArray(1)]
		]));
		$pFieldsAddress = $this->_pRecordManagerReadListViewAddress->getFieldconfigByListviewId(1);
		$this->assertEquals(4, count($pFieldsAddress));
	}

	/**
	 * 
	 */
	public function testCheckSameName()
	{
		$configOutput = ['count' => 0];

		$pWPDB = $this->getMockBuilder(wpdb::class)
			->disableOriginalConstructor(['testUser', 'testPassword', 'testDB', 'testHost'])
			->onlyMethods(['prepare', 'get_row'])
			->getMock();
		$pWPDB->prefix = 'testPrefix';
		$pWPDB->method('prepare')
			->will($this->returnArgument(0));
		$pWPDB->expects($this->once())
			->method('get_row')
			->willReturnOnConsecutiveCalls($configOutput);
		$pRecordManagerReadListViewAddress = $this->getMockBuilder(RecordManagerReadListViewAddress::class)
			->onlyMethods(['getWpdb'])
			->getMock();

		$pRecordManagerReadListViewAddress->method('getWpdb')->will($this->returnValue($pWPDB));
		$pData = $pRecordManagerReadListViewAddress->checkSameName('testForm1');

		$this->assertTrue($pData);
	}
}
