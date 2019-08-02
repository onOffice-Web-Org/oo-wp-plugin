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

namespace onOffice\WPlugin\Record;

use wpdb;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class RecordManagerInsertGeneric
	extends RecordManager
{
	/** @var string */
	private $_mainTableName = null;

	/** @var wpdb */
	private $_pWPDB = null;


	/**
	 *
	 * @param string $mainTableName
	 * @param \onOffice\WPlugin\Record\wpdb $pWPDB
	 *
	 */

	public function __construct(string $mainTableName, wpdb $pWPDB = null)
	{
		$this->_mainTableName = $mainTableName;
		$this->_pWPDB = $pWPDB ?? $this->getWpdb();
	}


	/**
	 *
	 * @param array $values
	 * @return int
	 * @throws RecordManagerInsertException
	 *
	 */

	public function insertByRow(array $values): int
	{
		$row = $values[$this->_mainTableName];
		$tableName = $this->_mainTableName;

		array_walk($row, function(&$value, $field) use ($tableName) {
			$value = RecordManager::postProcessValue($value, $tableName, $field);
		});

		if (false === $this->_pWPDB->insert($this->_pWPDB->prefix.$this->_mainTableName, $row)) {
			throw new RecordManagerInsertException();
		}

		return $this->_pWPDB->insert_id;
	}


	/**
	 *
	 * @param array $values
	 * @throws RecordManagerInsertException
	 *
	 */

	public function insertAdditionalValues(array $values)
	{
		unset($values[$this->_mainTableName]);
		foreach ($values as $table => $tablevalues) {
			foreach ($tablevalues as $tablerow) {
				if (is_array($tablerow) && false === $this->_pWPDB->insert
					($this->_pWPDB->prefix.$table, $tablerow)) {
					throw new RecordManagerInsertException();
				}
			}
		}
	}
}
