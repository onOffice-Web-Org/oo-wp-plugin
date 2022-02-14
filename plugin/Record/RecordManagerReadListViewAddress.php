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

class RecordManagerReadListViewAddress
	extends RecordManagerRead
{
	/**
	 *
	 */

	public function __construct()
	{
		$this->setMainTable(self::TABLENAME_LIST_VIEW_ADDRESS);
		$this->setIdColumnMain('listview_address_id');
	}


	/**
	 *
	 * @return array
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
				FROM {$prefix}oo_plugin_listviews_address
				{$join}
				WHERE {$where}
				ORDER BY `listview_address_id` ASC
				LIMIT {$this->getOffset()}, {$this->getLimit()}";

		$this->setFoundRows($pWpDb->get_results($sql, OBJECT));
		$this->setCountOverall($pWpDb->get_var('SELECT FOUND_ROWS()'));

		return $this->getFoundRows();
	}


    /**
     *
     * @return array
     *
     */

    public function getRecordsSortedAlphabetically():array
    {
        $prefix = $this->getTablePrefix();
        $pWpDb = $this->getWpdb();
        $columns = implode(', ', $this->getColumns());
        $join = implode("\n", $this->getJoins());
        $where = "(".implode(") AND (", $this->getWhere()).")";
        if (!empty($_GET["search"]))
        {
            $where .= "AND name LIKE '%".esc_sql($_GET['search'])."%' OR template LIKE '%".esc_sql($_GET['search'])."%'";
        }
        $sql = "SELECT SQL_CALC_FOUND_ROWS {$columns}
				FROM {$prefix}oo_plugin_listviews_address
				{$join}
				WHERE {$where}
				ORDER BY `name` ASC
				LIMIT {$this->getOffset()}, {$this->getLimit()}";
        $this->setFoundRows($pWpDb->get_results($sql, OBJECT));
        $this->setCountOverall($pWpDb->get_var('SELECT FOUND_ROWS()'));

        return $this->getFoundRows();
    }


    /**
	 *
	 * @param string $name
	 * @return array
	 *
	 */

	public function getRowByName($name)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$mainTable = $this->getMainTable();

		$sql = "SELECT *
				FROM {$prefix}{$mainTable}
				WHERE `name` = '".esc_sql($name)."'";

		$result = $pWpDb->get_row($sql, ARRAY_A);

		if ($result === null)
		{
			throw new \Exception(__('unknown address list name', 'onoffice-for-wp-websites'));
		}

		$resultFieldConfig = $this->readFieldconfigByListviewId($result[$this->getIdColumnMain()]);
		$result['fields'] = array_column($resultFieldConfig, 'fieldname');
		$result['filterable'] = array_keys(array_filter(array_column($resultFieldConfig, 'filterable', 'fieldname')));
		$result['hidden'] = array_keys(array_filter(array_column($resultFieldConfig, 'hidden', 'fieldname')));
		return $result;
	}


	/**
	 *
	 * @param int $listviewId
	 * @return array
	 *
	 */

	public function readFieldconfigByListviewId($listviewId)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sqlFields = "SELECT *
			FROM {$prefix}oo_plugin_address_fieldconfig
			WHERE `".esc_sql($this->getIdColumnMain())."` = ".esc_sql($listviewId)."
			ORDER BY `order` ASC";

		$result = $pWpDb->get_results($sqlFields, ARRAY_A);

		return $result;
	}
}
