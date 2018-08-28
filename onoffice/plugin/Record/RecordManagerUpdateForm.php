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
 */

class RecordManagerUpdateForm
	extends RecordManagerUpdate
{
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

		$whereFormConfigTable = array('form_id' => $this->getRecordId());
		$result = $pWpDb->update($prefix.self::TABLENAME_FORMS, $tableRow, $whereFormConfigTable);

		return $result !== false;
	}


	/**
	 *
	 * @param array $fieldConfig
	 * @return bool
	 *
	 */

	public function updateFieldConfigByRow(array $fieldConfig)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$pWpDb->delete($prefix.self::TABLENAME_FIELDCONFIG_FORMS, array('form_id' => $this->getRecordId()));

		$result = true;

		foreach ($fieldConfig as $row) {
			$result = $result && $pWpDb->insert($pWpDb->prefix.self::TABLENAME_FIELDCONFIG_FORMS, $row);
		}

		return $result;
	}
}
