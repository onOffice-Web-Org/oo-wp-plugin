<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\Record;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class RecordManagerRead
	extends RecordManager
{
	/** @var int */
	private $_offset = 0;

	/** @var int */
	private $_limit = 10;

	/** @var int */
	private $_rowsCountOverall = 0;

	/** @var object[] */
	private $_foundRows = [];

	/** @var string[] */
	private $_columns = [];

	/** @var string[] */
	private $_joins = [];

	/** @var string[] */
	private $_where = [1];

	/** @var string */
	private $_mainTable = '';

	/** @var string */
	private $_idColumnMain = '';


	/**
	 *
	 * @return array The records, each in an object
	 *
	 */

	abstract public function getRecords();


	/**
	 *
	 * @param string $name
	 * @return array
	 *
	 */

	abstract public function getRowByName($name);


	/**
	 *
	 * @param string $column
	 * @param string $alias
	 *
	 */

	public function addColumn(string $column, string $alias = null)
	{
		$columnArray = array(esc_sql($column));

		if ($alias !== null)
		{
			$columnArray []= esc_sql($alias);
		}

		$this->_columns []= "`".implode("` AS `", $columnArray)."`";
	}


	/**
	 *
	 * @param string $column
	 * @param string $alias
	 *
	 */

	public function addColumnConst(string $column, string $alias)
	{
		$this->_columns []= "'".esc_sql($column)."' AS `".esc_sql($alias)."`";
	}




	/**
	 *
	 * @param int $recordId
	 * @return array
	 *
	 */

	public function getRowById(int $recordId): array
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$mainTable = $this->getMainTable();

		$sql = "SELECT *
				FROM `".esc_sql($prefix.$mainTable)."`
				WHERE `".esc_sql($this->getIdColumnMain())."` = ".esc_sql((int)$recordId);

		$result = $pWpDb->get_row($sql, ARRAY_A);

		return $result;
	}


	/** @return int */
	public function getCountOverall(): int
		{ return $this->_rowsCountOverall; }

	/** @param int $countOverall */
	protected function setCountOverall(int $countOverall)
		{ $this->_rowsCountOverall = $countOverall; }

	/** @param int $offset */
	public function setOffset(int $offset)
		{ $this->_offset = $offset; }

	/** @param int $limit */
	public function setLimit(int $limit)
		{ $this->_limit = $limit; }

	/** @param string $fullJoinStatement */
	public function addJoin(string $fullJoinStatement)
		{ $this->_joins []= $fullJoinStatement; }

	/** @param string $where */
	public function addWhere(string $where)
		{ $this->_where []= $where; }

	/** @param array $foundRows */
	protected function setFoundRows(array $foundRows)
		{ $this->_foundRows = $foundRows; }

	/** @return array */
	protected function getFoundRows(): array
		{ return $this->_foundRows; }

	/** @return string [] */
	public function getColumns(): array
		{ return $this->_columns;}

	/** @return string [] */
	public function getJoins(): array
		{ return $this->_joins;}

	/** @return int */
	public function getOffset(): int
		{ return $this->_offset; }

	/** @return int */
	public function getLimit(): int
		{ return $this->_limit; }

	/** @return string[] */
	public function getWhere(): array
		{ return $this->_where; }

	/** @return string */
	public function getMainTable(): string
		{ return $this->_mainTable; }

	/** @param string $mainTable */
	protected function setMainTable(string $mainTable)
		{ $this->_mainTable = $mainTable; }

	/** @return string */
	public function getIdColumnMain(): string
		{ return $this->_idColumnMain; }

	/** @param string $idColumnMain */
	protected function setIdColumnMain(string $idColumnMain)
		{ $this->_idColumnMain = $idColumnMain; }
}
