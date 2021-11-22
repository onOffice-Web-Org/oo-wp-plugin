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
		$join = implode("\n", $this->getJoins());
		$where = "(".implode(") AND (", $this->getWhere()).")";
		$sql = "SELECT SQL_CALC_FOUND_ROWS {$columns}
				FROM {$prefix}oo_plugin_forms
				{$join}
				WHERE {$where}
				ORDER BY `form_id` ASC
				LIMIT {$this->getOffset()}, {$this->getLimit()}";
		$this->setFoundRows($pWpDb->get_results($sql, OBJECT));
		$this->setCountOverall($pWpDb->get_var('SELECT FOUND_ROWS()'));

		return $this->getFoundRows();
	}


    /**
     *
     * @return object[]
     *
     */

    public function getRecordsSortedAlphabetically()
    {
        $prefix = $this->getTablePrefix();
        $pWpDb = $this->getWpdb();
        $columns = implode(', ', $this->getColumns());
        $join = implode("\n", $this->getJoins());
        $where = "(".implode(") AND (", $this->getWhere()).")";
		if (!empty($_GET["search"]))
		{
			$where .= "AND name LIKE '%".esc_sql($_GET['search'])."%' OR template LIKE '%".esc_sql($_GET['search'])."%' OR recipient LIKE '%".esc_sql($_GET['search'])."%'";
		}
        $sql = "SELECT SQL_CALC_FOUND_ROWS {$columns}
				FROM {$prefix}oo_plugin_forms
				{$join}
				WHERE {$where}
				ORDER BY `name` ASC
				LIMIT {$this->getOffset()}, {$this->getLimit()}";
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

		$sql = "SELECT *
				FROM {$prefix}oo_plugin_forms";

		$result = $pWpDb->get_results($sql, OBJECT);

		if ($result === null) {
			throw new UnknownFormException();
		}

		return $result;
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

		$sql = "SELECT *
				FROM {$prefix}oo_plugin_forms
				WHERE `name` = '".esc_sql($formName)."'";

		$result = $pWpDb->get_row($sql, ARRAY_A);

		if ($result === null) {
			throw new UnknownFormException($formName);
		}

		$resultFieldConfig = $this->readFieldconfigByFormId($result[$this->getIdColumnMain()]);
		$result['fields'] = array_column($resultFieldConfig, 'fieldname');
		$result['filterable'] = array_keys(array_filter(array_column($resultFieldConfig, 'filterable', 'fieldname')));
		$result['hidden'] = array_keys(array_filter(array_column($resultFieldConfig, 'hidden', 'fieldname')));

		return $result;
	}


	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */

	public function readFieldconfigByFormId($formId)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sqlFields = "SELECT *
			FROM {$prefix}oo_plugin_form_fieldconfig
			WHERE `".esc_sql($this->getIdColumnMain())."` = ".esc_sql($formId)."
			ORDER BY `order` ASC";

		return $pWpDb->get_results($sqlFields, ARRAY_A);
	}


	/**
	 *
	 * @param int $formId
	 * @return object
	 *
	 */

	public function getNameByFormId($formId)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sqlNames = "SELECT *
				FROM {$prefix}oo_plugin_forms
				WHERE `form_id` = ".esc_sql((int)$formId);

		$result = $pWpDb->get_results($sqlNames, OBJECT);

		return $result;
	}


	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */

	public function readFieldsByFormId($formId)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sqlFields = "SELECT *
				FROM {$prefix}oo_plugin_form_fieldconfig
				WHERE `form_id` = ".esc_sql((int)$formId)."
				ORDER BY `order` ASC";

		$result = $pWpDb->get_results($sqlFields, ARRAY_A);

		return $result;
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

		$sql = "SELECT `form_type`, COUNT(`form_id`) as count
				FROM {$prefix}oo_plugin_forms
				GROUP BY `form_type`";
		$result = $pWpDb->get_results($sql, ARRAY_A);
		$returnValues = array();

		foreach ($result as $row)
		{
			$returnValues[$row['form_type']] = $row['count'];
		}

		$returnValues['all'] = array_sum($returnValues);

		return $returnValues;
	}
}