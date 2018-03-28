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

class RecordManagerInsertListViewEstate
	extends RecordManager
{
	/**
	 *
	 * @param array $tableRow
	 * @return int
	 *
	 */

	public function insertByRow($tableRow)
	{
		$pWpDb = $this->getWpdb();
		$row = $tableRow[self::TABLENAME_LIST_VIEW];

		foreach ($row as $key => $value) {
			if (null == $value) {
				if (!RecordStructure::isNullAllowed(self::TABLENAME_LIST_VIEW, $key)) {
					$emptyValue = RecordStructure::getEmptyValue(self::TABLENAME_LIST_VIEW, $key);

					if ($emptyValue !== false) {
						$row[$key] = $emptyValue;
					}
				}
			}
		}

		$pWpDb->insert($pWpDb->prefix.self::TABLENAME_LIST_VIEW, $row);
		$listViewId = $pWpDb->insert_id;

		return $listViewId;
	}


	/**
	 *
	 * @param array $values
	 * @return bool
	 *
	 */

	public function insertAdditionalValues(array $values) {
		$pWpDb = $this->getWpdb();

		unset($values[self::TABLENAME_LIST_VIEW]);
		$result = true;

		foreach ($values as $table => $tablevalues) {
			foreach ($tablevalues as $tablerow) {
				$result = $result && $pWpDb->insert($pWpDb->prefix.$table, $tablerow);
			}
		}

		return $result;
	}
}
