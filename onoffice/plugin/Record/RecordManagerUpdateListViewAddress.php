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
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerUpdateListViewAddress
	extends RecordManagerUpdate
{
	/** @var array */
	private $_addressRelationTables = array(
		self::TABLENAME_FIELDCONFIG_ADDRESS => 'listview_address_id',
	);


	/**
	 *
	 * @param array $tableRow
	 * @return bool
	 *
	 */

	public function updateByRow($tableRow)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$whereListViewTable = array('listview_address_id' => $this->getRecordId());
		$result = $pWpDb->update($prefix.self::TABLENAME_LIST_VIEW_ADDRESS, $tableRow, $whereListViewTable);

		return $result !== false;
	}


	/**
	 *
	 * @param array $tableRow
	 * @param int $mainRecordId
	 * @return bool
	 *
	 */

	public function updateRelations($tableRow)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$result = true;

		foreach ($this->_addressRelationTables as $table => $foreignKey) {
			if (isset($tableRow[$table])) {
				$whereCondition = array($foreignKey => $this->getRecordId());
				$pWpDb->delete($prefix.$table, $whereCondition);
				$newRecords = $tableRow[$table];
				$result = $result && $this->insertNewRecords($newRecords, $table);
			}
		}
		return $result;
	}


	/**
	 *
	 * @param array $records
	 * @param string $table
	 * @return bool
	 *
	 */

	private function insertNewRecords(array $records, $table)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$result = true;
		foreach ($records as $record) {
			$result = $result && $pWpDb->insert($prefix.$table, $record);
		}
		return $result;
	}
}
