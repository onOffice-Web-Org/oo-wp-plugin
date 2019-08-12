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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\Record\RecordManager;
use onOffice\WPlugin\Record\RecordManagerDeleteListViewAddress;
use onOffice\WPlugin\Record\RecordManagerDeleteListViewEstate;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerInsertGeneric;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Record\RecordManagerUpdateForm;
use onOffice\WPlugin\Record\RecordManagerUpdateListViewAddress;
use onOffice\WPlugin\Record\RecordManagerUpdateListViewEstate;
use WP_UnitTestCase;


/**
 *
 */

class TestClassRecordManagerFactory
	extends WP_UnitTestCase
{
	/** @var array */
	private $_combinations = [
		RecordManagerFactory::TYPE_ADDRESS => [
			RecordManagerFactory::ACTION_READ => RecordManagerReadListViewAddress::class,
			RecordManagerFactory::ACTION_INSERT => RecordManagerInsertGeneric::class,
			RecordManagerFactory::ACTION_UPDATE => RecordManagerUpdateListViewAddress::class,
			RecordManagerFactory::ACTION_DELETE => RecordManagerDeleteListViewAddress::class,
		],
		RecordManagerFactory::TYPE_ESTATE => [
			RecordManagerFactory::ACTION_READ => RecordManagerReadListViewEstate::class,
			RecordManagerFactory::ACTION_INSERT => RecordManagerInsertGeneric::class,
			RecordManagerFactory::ACTION_UPDATE => RecordManagerUpdateListViewEstate::class,
			RecordManagerFactory::ACTION_DELETE => RecordManagerDeleteListViewEstate::class,
		],
		RecordManagerFactory::TYPE_FORM => [
			RecordManagerFactory::ACTION_READ => RecordManagerReadForm::class,
			RecordManagerFactory::ACTION_INSERT => RecordManagerInsertGeneric::class,
			RecordManagerFactory::ACTION_UPDATE => RecordManagerUpdateForm::class,
		],
	];


	/**
	 *
	 * @covers onOffice\WPlugin\Record\RecordManagerFactory::createByTypeAndAction
	 *
	 */

	public function testCreateByTypeAndAction()
	{
		foreach ($this->_combinations as $type => $actionClass) {
			foreach ($actionClass as $action => $class) {
				$this->execute($type, $action, $class);
			}
		}
	}


	/**
	 *
	 * @param string $type
	 * @param string $action
	 * @param string $class
	 *
	 */

	private function execute($type, $action, $class)
	{
		$recordId = null;

		if ($action === RecordManagerFactory::ACTION_UPDATE) {
			$recordId = 1;
		}

		$pRecordManager = RecordManagerFactory::createByTypeAndAction
			($type, $action, $recordId);
		$this->assertInstanceOf($class, $pRecordManager);
	}


	/**
	 *
	 * @covers onOffice\WPlugin\Record\RecordManagerFactory::getGenericClassTables
	 *
	 */

	public function testGetGenericClassTables()
	{
		$expected = [
			RecordManagerFactory::TYPE_ADDRESS => RecordManager::TABLENAME_LIST_VIEW_ADDRESS,
			RecordManagerFactory::TYPE_ESTATE => RecordManager::TABLENAME_LIST_VIEW,
			RecordManagerFactory::TYPE_FORM => RecordManager::TABLENAME_FORMS,
		];

		$genericTables = RecordManagerFactory::getGenericClassTables();
		$this->assertEquals($expected, $genericTables);
	}
}
