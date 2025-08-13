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

	public function updateByRow(array $tableRow): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$whereFormConfigTable = ['form_id' => $this->getRecordId()];
		$suppressErrors = $pWpDb->suppress_errors();
		$result = $pWpDb->update($prefix.self::TABLENAME_FORMS, $tableRow, $whereFormConfigTable);
		$pWpDb->suppress_errors($suppressErrors);

		return $result !== false;
	}


	/**
	 *
	 * @param array $fieldConfig
	 * @return bool
	 *
	 */

	public function updateFieldConfigByRow(array $fieldConfig): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$pWpDb->delete($prefix.self::TABLENAME_FIELDCONFIG_FORMS, ['form_id' => $this->getRecordId()]);

		$result = true;

		foreach ($fieldConfig as $row) {
			$result = $result && $pWpDb->insert($pWpDb->prefix.self::TABLENAME_FIELDCONFIG_FORMS, $row);
		}

		return $result;
	}

	/**
	 * @param array $contactTypes
	 *
	 * @return bool
	 */
	public function updateContactTypeByRow(array $contactTypes): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$pWpDb->delete($prefix.self::TABLENAME_CONTACT_TYPES, ['form_id' => $this->getRecordId()]);

		$result = true;
		foreach ($contactTypes as $row) {
			if (empty($row)) {
				continue;
			}

			$result = $result && $pWpDb->insert($pWpDb->prefix.self::TABLENAME_CONTACT_TYPES, $row);
		}

		return $result;
	}

	/**
	 *
	 * @param array $row
	 * @return bool
	 *
	 */

	public function updateActivityConfigByRow(array $row): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$pWpDb->delete($prefix.self::TABLENAME_ACTIVITY_CONFIG_FORM, ['form_id' => $this->getRecordId()]);

		$result = true;
		$row['form_id'] = $this->getRecordId();

		return $result && $pWpDb->insert($pWpDb->prefix.self::TABLENAME_ACTIVITY_CONFIG_FORM, $row);
	}

	/**
	 * @param array $row
	 *
	 * @return bool
	 */
	public function updateTasksConfigByRow(array $row): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$pWpDb->delete($prefix.'oo_plugin_form_taskconfig', ['form_id' => $this->getRecordId()]);

		$result = true;
		$row['form_id'] = $this->getRecordId();

		return $result && $pWpDb->insert($pWpDb->prefix.'oo_plugin_form_taskconfig', $row);
	}

	public function updateMultiPageTitleByRow(array $titles): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$pWpDb->delete($prefix.self::TABLENAME_MULTIPAGE_TITLE_FORMS, ['form_id' => $this->getRecordId()]);

		$result = true;

		foreach ($titles as $row) {
			$result = $result && $pWpDb->insert($prefix.self::TABLENAME_MULTIPAGE_TITLE_FORMS, $row);
		}
		return $result;
	}
}
