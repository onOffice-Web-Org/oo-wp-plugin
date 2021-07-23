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
use onOffice\WPlugin\Record\RecordManagerDuplicateListViewEstate;
use onOffice\WPlugin\Record\RecordManagerInsertException;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Record\RecordManager;
use WP_UnitTestCase;
use wpdb;


/**
 *
 */
class TestClassRecordManagerDuplicateListViewEstate
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer = null;

	/** @var wpdb */
	private $_pWPDB = null;

	/** @var RecordManagerDuplicateListViewEstate */
	private $_pSubject = null;

	/** @var RecordManagerReadListViewEstate */
	private $_pRecordManagerReadListViewEstate = null;


	/**
	 *
	 * @before
	 *
	 * @throws \Exception
	 */

	public function prepare()
	{
		$recordRoot = [
			'listview_id' => '22',
			'name' => 'list view root',
			'filterId' => '0',
			'sortby' => 'test',
			'sortorder' => 'ASC',
			'show_status' => '0',
			'list_type' => 'default',
			'template' => 'testtemplate.php',
			'expose' => '',
			'recordsPerPage' => '20',
			'random' => '0',
			'country_active' => '0',
			'zip_active' => '1',
			'city_active' => '0',
			'street_active' => '0',
			'radius_active' => '1',
			'radius' => '10',
			'geo_order' => 'street,zip,city,country,radius',
			'sortBySetting' => '',
			'sortByUserDefinedDefault' => '',
			'sortByUserDefinedDirection' => '0',
			'pictures' => [],
			'sortbyuservalues' => [],
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

		$this->_pRecordManagerReadListViewEstate = $this->getMockBuilder(RecordManagerReadListViewEstate::class)
			->getMock();
		$this->_pRecordManagerReadListViewEstate->method('getRowById')->will($this->returnValue($recordRoot));
		$this->_pContainer->set(RecordManagerReadListViewEstate::class, $this->_pRecordManagerReadListViewEstate);
		$this->_pSubject = new RecordManagerDuplicateListViewEstate($this->_pWPDB, $this->_pContainer);
	}


	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */

	public function testDuplicateByIds()
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
		$pictureTypeRecordOutput = [
			(object)[
				'picturetype' => 'test'
			]
		];
		$sortByUserValueRecordOutput = [
			(object)[
				'sortbyuservalue' => 'test1'
			]
		];

		$this->_pWPDB->expects($this->exactly(4))
			->method('get_results')
			->willReturnOnConsecutiveCalls($fieldConfigRecordOutput, $fieldConfigRecordOutput, $pictureTypeRecordOutput,
				$sortByUserValueRecordOutput);

		$this->_pWPDB->insert_id = 23;
		$this->_pSubject->duplicateByIds(22);
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */

	public function testAppendCountToNameDuplicateByIds()
	{
		$recordRootCopy = (object)[
			'name' => 'list view root - Copy',
		];
		$fieldConfigRecordOutput = [
			(object)[
				'fieldname' => 'test1',
				'order' => 1,
				'filterable' => 0,
				'hidden' => 0,
				'availableOptions' => 0
			]
		];
		$pictureTypeRecordOutput = [
			(object)[
				'picturetype' => 'test'
			]
		];
		$sortByUserValueRecordOutput = [
			(object)[
				'sortbyuservalue' => 'test1'
			]
		];

		$this->_pWPDB->expects($this->once())
			->method('get_row')
			->will($this->returnValue($recordRootCopy));

		$this->_pWPDB->expects($this->exactly(4))
			->method('get_results')
			->willReturnOnConsecutiveCalls($fieldConfigRecordOutput, $fieldConfigRecordOutput, $pictureTypeRecordOutput,
				$sortByUserValueRecordOutput);

		$this->_pWPDB->insert_id = 23;
		$this->_pSubject->duplicateByIds(22);
	}

}

