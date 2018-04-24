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

namespace onOffice\WPlugin\Record;

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


	/**
	 *
	 * @param string $mainTableName
	 *
	 */

	public function __construct($mainTableName)
	{
		$this->_mainTableName = $mainTableName;
	}


	/**
	 *
	 * @param array $values
	 * @return int
	 *
	 */

	public function insertByRow($values)
	{
		$pWpDb = $this->getWpdb();
		$row = $values[$this->_mainTableName];
		$tableName = $this->_mainTableName;

		array_walk($row, function(&$value, $field) use ($tableName) {
			$value = RecordManager::postProcessValue($value, $tableName, $field);
		});

		$pWpDb->insert($pWpDb->prefix.$this->_mainTableName, $row);
		$formId = $pWpDb->insert_id;

		return $formId;
	}


	/**
	 *
	 * @param array $values
	 * @return bool
	 *
	 */

	public function insertAdditionalValues(array $values)
	{
		$pWpDb = $this->getWpdb();

		unset($values[$this->_mainTableName]);
		$result = true;

		foreach ($values as $table => $tablevalues) {
			foreach ($tablevalues as $tablerow) {
				$result = $result && $pWpDb->insert($pWpDb->prefix.$table, $tablerow);
			}
		}

		return $result;
	}
}
