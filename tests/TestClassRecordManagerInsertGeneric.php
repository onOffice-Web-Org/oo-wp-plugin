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

use onOffice\WPlugin\Record\RecordManager;
use onOffice\WPlugin\Record\RecordManagerInsertGeneric;
use WP_UnitTestCase;
use wpdb;


/**
 *
 */

class TestClassRecordManagerInsertGeneric
	extends WP_UnitTestCase
{
	/** @var RecordManagerInsertGeneric */
	private $_pSubject = null;

	/** @var \wpdb */
	private $_pWPDB = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pWPDB = $this->getMockBuilder(wpdb::class)
			->setConstructorArgs(['testUser', 'testPassword', 'testDB', 'testHost'])
			->getMock();
		$this->_pWPDB->prefix = 'testPrefix_';
		$this->_pSubject = new RecordManagerInsertGeneric(RecordManager::TABLENAME_FIELDCONFIG, $this->_pWPDB);
	}


	/**
	 *
	 */

	public function testInsertByRow()
	{
		$recordInput = [
			RecordManager::TABLENAME_FIELDCONFIG => [
				'test1' => 'abc',
				'test2' => 'cde',
				'fieldname' => null,
				'order' => null,
			],
		];

		$recordOutput = [
			'test1' => 'abc',
			'test2' => 'cde',
			'fieldname' => '',
			'order' => 0,
		];

		$this->_pWPDB->expects($this->once())->method('insert')
			->with('testPrefix_'.RecordManager::TABLENAME_FIELDCONFIG, $recordOutput)
			->will($this->returnValue(1));
		$this->_pWPDB->insert_id = 33;
		$this->assertEquals(33, $this->_pSubject->insertByRow($recordInput));
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\Record\RecordManagerInsertException
	 *
	 */

	public function testInsertByRowFail()
	{
		$this->_pWPDB->expects($this->once())->method('insert')
				->will($this->returnValue(false));
		$this->_pSubject->insertByRow([RecordManager::TABLENAME_FIELDCONFIG => ['a' => ['b']]]);
	}


	/**
	 *
	 */

	public function testInsertAdditionalValues()
	{
		$recordInput = [
			RecordManager::TABLENAME_FIELDCONFIG => [
				'test1' => 'abc',
				'test2' => 'cde',
				'fieldname' => null,
				'order' => null,
			],
			'otherTable1' => [
				[
					'a' => 'b',
					'c' => 123,
				],
				[
					'a' => 'g',
					'c' => 567,
				],
			],
		];

		$records = [['a' => 'b', 'c' => 123], ['a' => 'g', 'c' => 567]];

		$this->_pWPDB->expects($this->atLeast(1))->method('insert')
			->with('testPrefix_otherTable1', $this->anything())
			->will($this->returnCallback(function(string $table, array $values) use (&$records) {
				$id = array_search($values, $records);
				if ($id !== false) {
					unset($records[$id]);
					return $id + 1;
				}

				return false;
			}));
		$this->_pSubject->insertAdditionalValues($recordInput);
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\Record\RecordManagerInsertException
	 *
	 */

	public function testInsertAdditionalValuesFail()
	{
		$this->_pWPDB->expects($this->once())->method('insert')->will($this->returnValue(false));
		$this->_pSubject->insertAdditionalValues(['test' => [['a' => 'b']]]);
	}


	/**
	 *
	 */

	public function testGetWpdb()
	{
		$this->assertInstanceOf(\wpdb::class, $this->_pSubject->getWpdb());
	}


	/**
	 *
	 * Heads up: this is not the instance we've told it to use
	 *
	 */

	public function testGetTablePrefix()
	{
		$this->assertEquals('wptests_', $this->_pSubject->getTablePrefix());
	}
}
