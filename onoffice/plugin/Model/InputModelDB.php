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

namespace onOffice\WPlugin\Model;
use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputModelDB
	extends InputModelBase
{
	/** @var string Table without prefix */
	private $_table = null;

	/** @var string */
	private $_field = null;

	/** @var int */
	private $_mainRecordId = null;

	/** @var string */
	private $_module = null;

	/** @var bool */
	private $_ignore = false;


	/**
	 *
	 * @param string $name
	 * @param string $label
	 *
	 */

	public function __construct($name, $label)
	{
		$this->setName($name);
		$this->setLabel($label);
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getIdentifier()
	{
		if (__String::getNew($this->_table)->isEmpty() ||
			__String::getNew($this->_field)->isEmpty()) {
			throw new \Exception('Table and field must be set');
		}

		$identifier = $this->_table.'-'.$this->_field;
		$truncateChars = __String::getNew('\'".%$!_`')->split();

		return __String::getNew($identifier)->remove($truncateChars);
	}


	/** @return string */
	public function getTable()
		{ return $this->_table; }

	/** @return string */
	public function getField()
		{ return $this->_field; }

	/** @return int */
	public function getMainRecordId()
		{ return $this->_mainRecordId; }

	/** @param string $table */
	public function setTable($table)
		{ $this->_table = $table; }

	/** @param string $field*/
	public function setField($field)
		{ $this->_field = $field; }

	/** @param int $mainRecordId */
	public function setMainRecordId($mainRecordId)
		{ $this->_mainRecordId = $mainRecordId; }

	/** @return string */
	public function getModule()
		{ return $this->_module; }

	/** @param string $module */
	public function setModule($module)
		{ $this->_module = $module; }

	/** @return bool */
	public function getIgnore()
		{ return $this->_ignore; }

	/** @param bool $ignore */
	public function setIgnore($ignore)
		{ $this->_ignore = $ignore; }
}
