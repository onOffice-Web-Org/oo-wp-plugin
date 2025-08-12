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

use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use const ARRAY_A;
use const OBJECT;
use function esc_sql;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerReadForm
	extends RecordManagerRead
{
	/**
	 *
	 */

	public function __construct()
	{
		$this->setMainTable(self::TABLENAME_FORMS);
		$this->setIdColumnMain('form_id');
	}


	/**
	 *
	 * @return object[]
	 *
	 */

	public function getRecords()
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$columns = implode(', ', $this->getColumns());
		$where = "(".implode(") AND (", $this->getWhere()).")";
		$sql = $pWpDb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS {$columns}
			FROM `{$prefix}oo_plugin_forms`
			WHERE {$where}
			ORDER BY `form_id` ASC
			LIMIT %d, %d",
			$this->getOffset(),
			$this->getLimit()
		);
		$this->setFoundRows($pWpDb->get_results($sql, OBJECT));
		$this->setCountOverall($pWpDb->get_var('SELECT FOUND_ROWS()'));

		return $this->getFoundRows();
	}


    /**
     *
     * @return object[]
     *
     */

    public function getRecordsSortedAlphabetically():array
    {
        $prefix = $this->getTablePrefix();
        $pWpDb = $this->getWpdb();
        $columns = implode(', ', $this->getColumns());
        $where = "(".implode(") AND (", $this->getWhere()).")";
        if (!empty($_GET["search"]))
        {
            $where .= "AND (name LIKE '%".esc_sql($_GET['search'])."%' OR template LIKE '%".esc_sql($_GET['search'])."%' OR recipient LIKE '%".esc_sql($_GET['search'])."%' OR subject LIKE '%".esc_sql($_GET['search'])."%')";
        }
		$sql = $pWpDb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS {$columns}
			FROM `{$prefix}oo_plugin_forms`
			WHERE {$where}
			ORDER BY `name` ASC
			LIMIT %d, %d",
			$this->getOffset(),
			$this->getLimit()
		);
        $this->setFoundRows($pWpDb->get_results($sql, OBJECT));
        $this->setCountOverall($pWpDb->get_var('SELECT FOUND_ROWS()'));

        return $this->getFoundRows();
    }


	/**
	 * @return object
	 * @throws UnknownFormException
	 */

	public function getAllRecords()
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_forms`"
		);

		$result = $pWpDb->get_results($sql, OBJECT);

		if ($result === null) {
			throw new UnknownFormException();
		}

		return $result;
	}


	/**
	 * @param string $formType
	 *
	 * @return object
	 * @throws UnknownFormException
	 */

	public function getAllRecordsByFormType(string $formType)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_forms`
			WHERE `form_type` = %s",
			$formType
		);

		$result = $pWpDb->get_results($sql, OBJECT);

		if ($result === null) {
			throw new UnknownFormException();
		}

		return $result;
	}

	public function getCountDefaultRecipientRecord()
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT COUNT(`form_id`) as count
			FROM `{$prefix}oo_plugin_forms`
			WHERE `default_recipient` = 1"
		);


		$rowCount = $pWpDb->get_var($sql);

		return $rowCount;
	}

	/**
	 *
	 * @param string $formName
	 * @return array
	 *
	 * @throws UnknownFormException
	 *
	 */

	public function getRowByName($formName)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_forms`
			WHERE `name` = %s",
			$formName
		);


		$result = $pWpDb->get_row($sql, ARRAY_A);

		if ($result === null) {
			throw new UnknownFormException($formName);
		}

		$resultFieldConfig = $this->readFieldconfigByFormId($result[$this->getIdColumnMain()]);
		$result['fields'] = array_column($resultFieldConfig, 'fieldname');
		$result['filterable'] = array_keys(array_filter(array_column($resultFieldConfig, 'filterable', 'fieldname')));
		$result['hidden'] = array_keys(array_filter(array_column($resultFieldConfig, 'hidden', 'fieldname')));

		$result['contact_type'] = $this->readContactTypesByFormId($result['form_id']);

		return $result;
	}


	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */

	public function readFieldconfigByFormId(int $formId)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_form_fieldconfig`
			WHERE {$this->getIdColumnMain()} = %d
			ORDER BY `order` ASC",
			$formId
		);

		return $pWpDb->get_results($sql, ARRAY_A);
	}


	/**
	 *
	 * @param int $formId
	 * @return object
	 *
	 */

	public function getNameByFormId(int $formId)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_forms`
			WHERE `form_id` = %d",
			$formId
		);

		$result = $pWpDb->get_results($sql, OBJECT);

		return $result;
	}


	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */

	public function readFieldsByFormId(int $formId)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_form_fieldconfig`
			WHERE `form_id` = %d
			ORDER BY `order` ASC",
			$formId
		);

		$result = $pWpDb->get_results($sql, ARRAY_A);

		return $result;
	}

	/**
	 * Reads all multilingual page titles for a specific form
	 *
	 * @param int $formId The ID of the form to read titles for
	 * @return array Array of title records with page and language information
	 */
	public function readTitlePerMultipageByFormId(int $formId): array
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sqlTitlePerPages = $pWpDb->prepare(
			"SELECT *
                FROM `{$prefix}oo_plugin_form_multipage_title`
                WHERE `form_id` = %d",
			$formId
		);

		return $pWpDb->get_results($sqlTitlePerPages, ARRAY_A) ?: [];
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getCountByType()
	{
		$pWpDb = $this->getWpdb();
		$prefix = $this->getTablePrefix();

		$sql = $pWpDb->prepare(
			"SELECT `form_type`, COUNT(`form_id`) as count
			FROM `{$prefix}oo_plugin_forms`
			GROUP BY `form_type`"
		);

		$result = $pWpDb->get_results($sql, ARRAY_A);
		$returnValues = array();

		foreach ($result as $row)
		{
			$returnValues[$row['form_type']] = $row['count'];
		}

		$returnValues['all'] = array_sum($returnValues);

		return $returnValues;
	}

	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */

	public function readActivityConfigByFormId(int $formId): array
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_form_activityconfig`
			WHERE `{$this->getIdColumnMain()}` = %d",
			$formId
		);

		return $pWpDb->get_row($sql, ARRAY_A) ?? [];
	}

	/**
	 * @param string $name
	 * @param string|null $id
	 *
	 * @return bool
	 */
	public function checkSameName(string $name, string $id = null): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT COUNT(*) AS count
			FROM `{$prefix}oo_plugin_forms`
			WHERE name = %s",
			$name
		);

		if (!is_null($id)) {
			$sql .= " AND form_id != '" . esc_sql($id) . "'";
		}

		$result = $pWpDb->get_row($sql, ARRAY_A);
		return $result['count'] == 0;
	}

	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */
	public function readFormTaskConfigByFormId(int $formId): array
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_form_taskconfig`
			WHERE `{$this->getIdColumnMain()}` = %d",
			$formId
		);

		return $pWpDb->get_row($sql, ARRAY_A) ?? [];
	}
}