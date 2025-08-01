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

use onOffice\WPlugin\DataView;
use onOffice\WPlugin\DataView\DataListView;
use const ARRAY_A;
use const OBJECT;
use function esc_sql;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerReadListViewEstate
	extends RecordManagerRead
{
	/**
	 *
	 */

	public function __construct()
	{
		$this->setMainTable('oo_plugin_listviews');
		$this->setIdColumnMain('listview_id');
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
			FROM `{$prefix}oo_plugin_listviews`
			WHERE {$where}
			ORDER BY `listview_id` ASC
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
            $where .= "AND (name LIKE '%".esc_sql($_GET['search'])."%' OR template LIKE '%".esc_sql($_GET['search'])."%')";
        }
        $orderBy = ( ! empty($_GET['orderby'])) ? $_GET['orderby'] : 'name';
        $order = ( ! empty($_GET['order'])) ? $_GET['order'] : 'asc';

		$sql = $pWpDb->prepare(
			"SELECT SQL_CALC_FOUND_ROWS {$columns}
			FROM `{$prefix}oo_plugin_listviews`
			WHERE {$where}
			ORDER BY `listview_id` ASC
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
	 * @param int $listviewId
	 * @return array
	 *
	 */

	public function getRowById(int $listviewId): array
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_listviews`
			WHERE `listview_id` = %d",
			$listviewId
		);

		$result = $pWpDb->get_row($sql, ARRAY_A);

		if ($result !== null)
		{
			$result[DataListView::PICTURES] = $this->getPictureTypesByListviewId($listviewId);
			$result[DataListView::SORT_BY_USER_VALUES] = $this->getSortbyuservaluesByListviewId($listviewId);

			$fieldRows = $this->getFieldconfigByListviewId($listviewId);
			$fields = array_column($fieldRows, 'fieldname');

			$result[DataView\DataListView::FIELDS] = $fields;
			$result['filterable'] = $this->getBooleanFieldValuesByFieldRow($fieldRows, 'filterable');
			$result['hidden'] = $this->getBooleanFieldValuesByFieldRow($fieldRows, 'hidden');
			$result['highlighted'] = $this->getBooleanFieldValuesByFieldRow($fieldRows, 'highlighted');
			$result['availableOptions'] = $this->getBooleanFieldValuesByFieldRow($fieldRows, 'availableOptions');
			$result['convertTextToSelectForCityField'] = $this->getBooleanFieldValuesByFieldRow($fieldRows, 'convertTextToSelectForCityField');
		}

		return $result;
	}


	/**
	 *
	 * @param array $row
	 * @param string $booleanField
	 * @return array an array of fieldnames having $booleanField == '1'
	 *
	 */

	private function getBooleanFieldValuesByFieldRow(array $row, string $booleanField): array
	{
		$fields = array_column($row, 'fieldname');
		$resultBooleanField = array_column($row, $booleanField);

		if ($resultBooleanField === []) {
			return [];
		}

		$tmpFilterable = array_combine($fields, $resultBooleanField);
		$filterableFields = array_keys(array_filter($tmpFilterable));
		return $filterableFields;
	}


	/**
	 *
	 * @param string $listviewName
	 * @param string $type
	 * @return array
	 *
	 */

	public function getRowByName($listviewName, $type = null)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_listviews`
			WHERE `name` = %s",
			$listviewName
		);
		if ($type !== null) {
			$sql .= " AND `list_type` = '".esc_sql($type)."'";
		}

		$result = $pWpDb->get_row($sql, ARRAY_A);

		if ($result !== null)
		{
			$id = $result['listview_id'];
			$result[DataView\DataListView::PICTURES] = $this->getPictureTypesByListviewId($id);
			$result[DataView\DataListView::SORT_BY_USER_VALUES] = $this->getSortbyuservaluesByListviewId($id);

			$fieldRows = $this->getFieldconfigByListviewId($id);
			$fields = array_column($fieldRows, 'fieldname');

			$result[DataView\DataListView::FIELDS] = $fields;
			$result['filterable'] = $this->getBooleanFieldValuesByFieldRow($fieldRows, 'filterable');
			$result['hidden'] = $this->getBooleanFieldValuesByFieldRow($fieldRows, 'hidden');
			$result['highlighted'] = $this->getBooleanFieldValuesByFieldRow($fieldRows, 'highlighted');
			$result['availableOptions'] = $this->getBooleanFieldValuesByFieldRow($fieldRows, 'availableOptions');
			$result['convertTextToSelectForCityField'] = $this->getBooleanFieldValuesByFieldRow($fieldRows, 'convertTextToSelectForCityField');

		}

		return $result;
	}


	/**
	 *
	 * @param int $listviewId
	 * @return array
	 *
	 */

	public function getPicturetypesByListviewId(int $listviewId) : array
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT `picturetype`
			FROM `{$prefix}oo_plugin_picturetypes`
			WHERE `listview_id` = %d",
			$listviewId
		);

		$pictures = $pWpDb->get_col($sql);
		$result = array();

		if (is_array($pictures))
		{
			$result = $pictures;
		}

		return $result;
	}



	/**
	 *
	 * @param int $listviewId
	 * @return array
	 *
	 */

	public function getSortbyuservaluesByListviewId(int $listviewId) : array
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT `sortbyuservalue`
			FROM `{$prefix}oo_plugin_sortbyuservalues`
			WHERE `listview_id` = %d",
			$listviewId
		);

		$sortbyuservalue = $pWpDb->get_col($sql);
		$result = array();

		if (is_array($sortbyuservalue))
		{
			$result = $sortbyuservalue;
		}

		return $result;
	}


	/**
	 *
	 * @param int $listviewId
	 * @return array
	 *
	 */

	public function getFieldconfigByListviewId(int $listviewId) : array
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT *
			FROM `{$prefix}oo_plugin_fieldconfig`
			WHERE `listview_id` = %d
			ORDER BY `order` ASC",
			$listviewId
		);

		$fields = $pWpDb->get_results($sql, ARRAY_A);
		$result = array();

		if (is_array($fields))
		{
			$result = $fields;
		}

		return $result;
	}


	/**
	 *
	 * @param int $listviewId
	 * @return array
	 *
	 */

	public function getContactDataByListviewId(int $listviewId) : array
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"SELECT `fieldname`
			FROM `{$prefix}oo_plugin_listview_contactperson`
			WHERE `listview_id` = %d
			ORDER BY `order` ASC",
			$listviewId
		);

		$fields = $pWpDb->get_col($sql);
		$result = array();

		if (is_array($fields))
		{
			$result = $fields;
		}

		return $result;
	}


	/**
	 *
	 * @param int $listviewId
	 * @param string $column
	 * @return string
	 *
	 */

	public function getColumn(int $listviewId, $column) : array
	{
		$result = null;
		$values = $this->getRowById($listviewId);

		if (is_array($values) && array_key_exists($column, $values))
		{
			$result = $values[$column];
		}

		return $result;
	}


	/**
	 * @param $page
	 * @param $listviewId
	 * @param $tableName
	 * @param $column
	 */

	public function updateColumnPageShortCode(string $page, int $listviewId, string $tableName, string $column )
	{
		$prefix = $this->getTablePrefix();
		$pWpDb  = $this->getWpdb();

		$sql = $pWpDb->prepare(
			"UPDATE `{$prefix}{$tableName}`
			SET `page_shortcode` = %s
			WHERE `{$column}` = %d",
			$page,
			$listviewId
		);

		$pWpDb->query($sql);
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
			FROM `{$prefix}oo_plugin_listviews`
			WHERE name = %s",
			$name
		);

		if (!is_null($id)) {
			$sql .= " AND listview_id != '" . esc_sql($id) . "'";
		}

		$result = $pWpDb->get_row($sql, ARRAY_A);

		return $result['count'] == 0;
	}
}
