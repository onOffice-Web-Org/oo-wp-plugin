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

use onOffice\WPlugin\DataView\DataListView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerInsertListView
	extends RecordManager
{

	/**
	 *
	 * @param \onOffice\WPlugin\Record\DataListView $pDataViewList
	 * @return int
	 *
	 */

	public function insertByDataViewList(DataListView $pDataViewList)
	{
		$name = $pDataViewList->getName();
		$sortby = $pDataViewList->getSortby();
		$sortorder = $pDataViewList->getSortOrder();
		$showStatus = $pDataViewList->getShowStatus();
		$listType = $pDataViewList->getListType();
		$template = $pDataViewList->getTemplate();
		$recordsPerPage = $pDataViewList->getRecordsPerPage();
		$pictures = $pDataViewList->getPictureTypes();
		$fields = $pDataViewList->getFields();
		$contactPerson = $pDataViewList->getAddressFields();

		$values = array(
			'name' => $name,
			'sortby' => $sortby,
			'sortorder' => $sortorder,
			'show_status' => $showStatus,
			'list_type' => $listType,
			'template' => $template,
			'recordsPerPage' => $recordsPerPage,
		);

		$row = array(
			self::TABLENAME_LIST_VIEW => $values,
			self::TABLENAME_PICTURETYPES => $pictures,
			self::TABLENAME_FIELDCONFIG => $fields,
			self::TABLENAME_LISTVIEW_CONTACTPERSON => $contactPerson,
		);

		$listViewId = $this->insertByRow($row);

		return $listViewId;
	}


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

		if ($listViewId > 0) {
			if (array_key_exists(self::TABLENAME_FIELDCONFIG, $tableRow)) {
				$fields = $tableRow[self::TABLENAME_FIELDCONFIG];
				$this->insertFields($listViewId, $fields);
			}

			if (array_key_exists(self::TABLENAME_PICTURETYPES, $tableRow)) {
				$pictures = $tableRow[self::TABLENAME_PICTURETYPES];
				$this->insertPictures($listViewId, $pictures);
			}

			if (array_key_exists(self::TABLENAME_LISTVIEW_CONTACTPERSON, $tableRow)) {
				$contactPerson = $tableRow[self::TABLENAME_LISTVIEW_CONTACTPERSON];
				$this->insertContactPerson($listViewId, $contactPerson);
			}
		}
		return $listViewId;
	}


	/**
	 *
	 * @param int $listViewId
	 * @param array $fields
	 *
	 */

	public function insertFields($listViewId, $fields)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$table = $prefix.self::TABLENAME_FIELDCONFIG;

		if (array_key_exists('fieldname', $fields)) {
			$fieldValues = $fields['fieldname'];
			$order = 1;

			foreach ($fieldValues as $field) {
				$fieldRow = array();
				$fieldRow['listview_id'] = $listViewId;
				$fieldRow['fieldname'] = $field;
				$fieldRow['order'] = $order;

				$pWpDb->insert($table, $fieldRow);

				$order++;
			}
		}
	}


	/**
	 *
	 * @param int $listViewId
	 * @param array $pictures
	 *
	 */

	public function insertPictures($listViewId, $pictures)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$table = $prefix.self::TABLENAME_PICTURETYPES;

		if (array_key_exists('picturetype', $pictures)) {
			$picturetypes = $pictures['picturetype'];

			foreach ($picturetypes as $type) {
				$pictureRow = array();
				$pictureRow['picturetype'] = $type;
				$pictureRow['listview_id'] = $listViewId;
				$pWpDb->insert($table, $pictureRow);
			}
		}
	}


	/**
	 *
	 * @param int $listViewId
	 * @param array $contactPrsonValues
	 *
	 */

	public function insertContactPerson($listViewId, $contactPrsonValues)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$table = $prefix.self::TABLENAME_LISTVIEW_CONTACTPERSON;

		if (array_key_exists('fieldname', $contactPrsonValues)) {
			$fieldValues = $contactPrsonValues['fieldname'];
			$order = 1;

			foreach ($fieldValues as $field) {
				$fieldRow = array();
				$fieldRow['listview_id'] = $listViewId;
				$fieldRow['fieldname'] = $field;
				$fieldRow['order'] = $order;

				$pWpDb->insert($table, $fieldRow);

				$order++;
			}
		}
	}
}
