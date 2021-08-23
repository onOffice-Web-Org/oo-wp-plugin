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
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\WPlugin\Record\RecordManagerDuplicateListViewAddress;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use WP_UnitTestCase;
use wpdb;


/**
 *
 */
class TestClassRecordManagerDuplicateListViewAddress
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer = null;

	/** @var wpdb */
	private $_pWPDB = null;

	/** @var RecordManagerDuplicateListViewAddress */
	private $_pSubject = null;

	/** @var RecordManagerReadListViewEstate */
	private $_pRecordManagerReadListViewAddress = null;


	/**
	 *
	 * @before
	 *
	 * @throws Exception
	 */

	public function prepare()
	{
		$recordRoot = [
			'listview_address_id' => '22',
			'name' => 'list view root',
			'filterId' => '0',
			'sortby' => 'test',
			'sortorder' => 'ASC',
			'template' => 'testtemplate.php',
			'recordsPerPage' => '20',
			'showPhoto' => '0',
			'fields' => [
				'test1',
				'test2'
			],
			'filterable' => [],
			'hidden' => [],
			'availableOptions' => []
		];

		$this->_pWPDB = $this->getMockBuilder(wpdb::class)
			->setConstructorArgs(['testUser', 'testPassword', 'testDB', 'testHost'])
			->getMock();
		$this->_pWPDB->prefix = 'testPrefix_';

		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pDIContainerBuilder->build();

		$this->_pRecordManagerReadListViewAddress = $this->getMockBuilder(RecordManagerReadListViewAddress::class)
			->getMock();
		$this->_pRecordManagerReadListViewAddress->method('getRowByName')->will($this->returnValue($recordRoot));
		$this->_pContainer->set(RecordManagerReadListViewAddress::class, $this->_pRecordManagerReadListViewAddress);
		$this->_pSubject = new RecordManagerDuplicateListViewAddress($this->_pWPDB, $this->_pContainer);
	}


	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function testDuplicateByName()
	{
		$fieldConfigRecordOutput = [
			(object)[
				'fieldname' => 'test1',
				'order' => 1,
				'filterable' => 0,
				'hidden' => 0,
				'availableOptions' => 0
			]
		];
		$listViewsRows =
			(object)[
				'listview_address_id' => '22',
				'name' => 'list view root - Copy 1',
			];

		$this->_pWPDB->expects($this->once())
			->method('get_row')
			->willReturnOnConsecutiveCalls($listViewsRows);
		$this->_pWPDB->expects($this->exactly(2))
			->method('get_results')
			->willReturnOnConsecutiveCalls($fieldConfigRecordOutput, $fieldConfigRecordOutput);

		$this->_pWPDB->insert_id = 22;
		$this->_pSubject->duplicateByName('list view root');
	}
}

