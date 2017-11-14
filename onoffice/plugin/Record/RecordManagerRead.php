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
	private $_rowsCountOverall = null;

	/** @var object[] */
	private $_foundRows = null;

	/** @var string[] */
	private $_columns = array();

	/** @var string[] */
	private $_joins = array();

	/** @var string[] */
	private $_where = array(1);


	/**
	 *
	 * @return array The records, each in an object
	 *
	 */

	abstract public function getRecords();


	/**
	 *
	 * @param string $column
	 * @param string $alias
	 *
	 */

	public function addColumn($column, $alias = null)
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

	public function addColumnConst($column, $alias)
	{
		$this->_columns []= "'".esc_sql($column)."' AS `".esc_sql($alias)."'";
	}

	/** @return int */
	public function getCountOverall()
		{ return $this->_rowsCountOverall; }

	/** @param int $countOverall */
	protected function setCountOverall($countOverall)
		{ $this->_rowsCountOverall = $countOverall; }

	/** @param int $offset */
	public function setOffset($offset)
		{ $this->_offset = (int)$offset; }

	/** @param int $limit */
	public function setLimit($limit)
		{ $this->_limit = (int)$limit; }

	/** @param string $fullJoinStatement */
	public function addJoin($fullJoinStatement)
		{ $this->_joins []= $fullJoinStatement; }

	/** @param string $where */
	public function addWhere($where)
		{ $this->_where []= $where; }

	/** @param array $foundRows */
	protected function setFoundRows($foundRows)
		{ $this->_foundRows = $foundRows; }

	/** @return array */
	protected function getFoundRows()
		{ return $this->_foundRows; }

	/** @return string [] */
	protected function getColumns()
		{ return $this->_columns;}

	/** @return string [] */
	protected function getJoins()
		{ return $this->_joins;}

	/** @return int */
	protected function getOffset()
		{ return $this->_offset; }

	/** @return int */
	protected function getLimit()
		{ return $this->_limit; }

	/** @return string[] */
	protected function getWhere()
		{ return $this->_where; }
}
