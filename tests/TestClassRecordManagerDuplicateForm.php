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
use onOffice\WPlugin\Record\RecordManagerDuplicateListViewForm;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use WP_UnitTestCase;
use wpdb;


/**
 *
 */
class TestClassRecordManagerDuplicateForm
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer = null;

	/** @var wpdb */
	private $_pWPDB = null;

	/** @var RecordManagerDuplicateListViewForm */
	private $_pSubject = null;

	/** @var RecordManagerReadForm */
	private $_pRecordManagerReadListViewForm = null;


	/**
	 *
	 * @before
	 *
	 * @throws Exception
	 */

	public function prepare()
	{
		$recordRoot = [
			'form_id' => '22',
			'name' => 'list view root',
			'form_type' => 'contact',
			'template' => 'testtemplate.php',
			'recipient' => '',
			'subject' => '',
			'createaddress' => '0',
			'limitresults' => null,
			'checkduplicates' => 0,
			'pages' => 2,
			'captcha' => 0,
			'newsletter' => 1,
			'country_active' => 1,
			'zip_active' => 1,
			'city_active' => '0',
			'street_active' => '0',
			'radius_active' => '1',
			'radius' => '10',
			'geo_order' => 'street,zip,city,country,radius',
			'show_estate_context' => '',
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

		$this->_pRecordManagerReadListViewForm = $this->getMockBuilder(RecordManagerReadForm::class)
			->getMock();
		$this->_pRecordManagerReadListViewForm->method('getRowByName')->will($this->returnValue($recordRoot));
		$this->_pContainer->set(RecordManagerReadForm::class, $this->_pRecordManagerReadListViewForm);
		$this->_pSubject = new RecordManagerDuplicateListViewForm($this->_pWPDB, $this->_pContainer);
	}


	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function testDuplicateByIds()
	{
		$fieldConfigRecordOutput = [
			(object)[
				'fieldname' => 'test1',
				'fieldlabel' => 'test1',
				'form_id' => 23,
				'order' => 1,
				'module' => '0',
				'individual_fieldname' => 0,
				'required' => 0,
				'availableOptions' => 0
			]
		];

		$sampleData = [
			(object)[
				'form_id' => 23,
				'defaults_id' => 23,
				'customs_labels_id' => 23,
				'locale' => 1,
				'value' => 1,
				'fieldname' => 'test1',
			]
		];

		$recordRootCopy = (object)[
			'form_id' => 22,
			'name' => 'list view root - Copy',
		];

		$this->_pWPDB->expects($this->once())
			->method('get_row')
			->willReturnOnConsecutiveCalls($recordRootCopy);

		$this->_pWPDB->expects($this->exactly(6))
			->method('get_results')
			->willReturnOnConsecutiveCalls(
				$fieldConfigRecordOutput,
				$fieldConfigRecordOutput,
				$sampleData,
				$sampleData,
				$sampleData,
				$sampleData
			);

		$this->_pWPDB->insert_id = 23;
		$this->_pSubject->duplicateByName('list view root');
	}
}

