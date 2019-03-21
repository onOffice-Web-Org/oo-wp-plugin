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

use Closure;
use onOffice\WPlugin\Record\RecordManagerRead;
use stdClass;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassRecordManagerRead
	extends WP_UnitTestCase
{
	/** @var RecordManagerRead */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSubject = $this->getMock(RecordManagerRead::class, ['getRecords', 'getRowByName']);
	}


	/**
	 *
	 */

	public function testColumns()
	{
		$this->assertEquals([], $this->_pSubject->getColumns());
		$this->_pSubject->addColumn('test1 a\\');
		$this->_pSubject->addColumn('test2', 'asd\\');
		$this->assertEquals([
			'`test1 a\\\`',
			'`test2` AS `asd\\\`',
		], $this->_pSubject->getColumns());
	}


	/**
	 *
	 */

	public function testColumnConst()
	{
		$this->assertEquals([], $this->_pSubject->getColumns());
		$this->_pSubject->addColumnConst('a\\\' b c', 'test');
		$this->_pSubject->addColumnConst('abc', '\'\\test');
		$this->assertEquals([
			'\'a\\\\\\\' b c\' AS `test`',
			'\'abc\' AS `\\\'\\\\test`',
		], $this->_pSubject->getColumns());
	}


	/**
	 *
	 */

	public function testCountOverall()
	{
		$this->assertEquals(0, $this->_pSubject->getCountOverall());
		$this->executeClosureInContext(function() {
			$this->setCountOverall(13);
		});
		$this->assertEquals(13, $this->_pSubject->getCountOverall());
	}


	/**
	 *
	 */

	public function testOffset()
	{
		$this->assertEquals(0, $this->_pSubject->getOffset());
		$this->_pSubject->setOffset(25);
		$this->assertEquals(25, $this->_pSubject->getOffset());
	}


	/**
	 *
	 */

	public function testLimit()
	{
		$this->assertEquals(10, $this->_pSubject->getLimit());
		$this->_pSubject->setLimit(25);
		$this->assertEquals(25, $this->_pSubject->getLimit());
	}


	/**
	 *
	 */

	public function testJoins()
	{
		$this->assertEquals([], $this->_pSubject->getJoins());
		$this->_pSubject->addJoin('1 = 1');
		$this->assertEquals(['1 = 1'], $this->_pSubject->getJoins());
	}


	/**
	 *
	 */

	public function testWhere()
	{
		$this->assertEquals(['1'], $this->_pSubject->getWhere());
		$this->_pSubject->addWhere('`test` = 13');
		$this->assertEquals(['1', '`test` = 13'], $this->_pSubject->getWhere());
	}


	/**
	 *
	 */

	public function testFoundRows()
	{
		$pClosureGetRecords = Closure::bind(function() {
			return $this->getFoundRows();
		}, $this->_pSubject, RecordManagerRead::class);
		$this->assertEquals([], $pClosureGetRecords());

		$newExpectedResult = [new stdClass()];
		$this->executeClosureInContext(function() use ($newExpectedResult) {
			$this->setFoundRows($newExpectedResult);
		});
		$this->assertEquals($newExpectedResult, $pClosureGetRecords());
	}


	/**
	 *
	 */

	public function testIdColumnMain()
	{
		$this->assertEquals('', $this->_pSubject->getIdColumnMain());
		$this->executeClosureInContext(function() {
			$this->setIdColumnMain('listview_id');
		});
		$this->assertEquals('listview_id', $this->_pSubject->getIdColumnMain());
	}


	/**
	 *
	 */

	public function testMainTable()
	{
		$this->assertEquals('', $this->_pSubject->getMainTable());
		$this->executeClosureInContext(function() {
			$this->setMainTable('listviews');
		});
		$this->assertEquals('listviews', $this->_pSubject->getMainTable());
	}


	/**
	 *
	 * @param Closure $pClosure
	 *
	 */

	private function executeClosureInContext(Closure $pClosure)
	{
		Closure::bind($pClosure, $this->_pSubject, RecordManagerRead::class)();
	}
}
