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
use onOffice\WPlugin\Record\RecordManagerInsertListView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerUpdateListView
	extends RecordManager
{

	/** @var int */
	private $_listviewId = null;


	/**
	 *
	 * @param int $listviewId
	 *
	 */

	public function __construct($listviewId)
	{ $this->_listviewId = $listviewId; }


	/**
	 *
	 * @param DataListView $pDataViewList
	 *
	 */

	public function updateByDataListView($pDataViewList)
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
		$contactPerson = $pDataViewList->getContactPerson();

		$row = array
			(
				'name' => $name,
				'sortby' => $sortby,
				'sortorder' => $sortorder,
				'show_status' => $showStatus,
				'list_type' => $listType,
				'template' => $template,
				'recordsPerPage' => $recordsPerPage,
			);

		$tableRow = array
			(
				self::TABLENAME_LIST_VIEW => $row,
				self::TABLENAME_PICTURETYPES => $pictures,
				self::TABLENAME_FIELDCONFIG => $fields,
				self::TABLENAME_LISTVIEW_CONTACTPERSON => $contactPerson,
			);

		$this->updateByRow($this->_listviewId, $tableRow);
	}


	/**
	 *
	 * @param int $listviewId
	 * @param array $tableRow
	 * @return bool success
	 *
	 */

	public function updateByRow($tableRow)
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$pInsert = new RecordManagerInsertListView();

		$whereListviewTable = array('listview_id' => $this->_listviewId);
		$result = $pWpDb->update($prefix.self::TABLENAME_LIST_VIEW,
				$tableRow[self::TABLENAME_LIST_VIEW],
				$whereListviewTable);

		if (array_key_exists(self::TABLENAME_FIELDCONFIG, $tableRow))
		{
			$fields = $tableRow[self::TABLENAME_FIELDCONFIG];
			$pWpDb->delete($prefix.self::TABLENAME_FIELDCONFIG,
					$whereListviewTable);
			$pInsert->insertFields($this->_listviewId, $fields);
		}

		if (array_key_exists(self::TABLENAME_PICTURETYPES, $tableRow))
		{
			$pictures = $tableRow[self::TABLENAME_PICTURETYPES];
			$pWpDb->delete($prefix.self::TABLENAME_PICTURETYPES,
					$whereListviewTable);
			$pInsert->insertPictures($this->_listviewId, $pictures);
		}

		if (array_key_exists(self::TABLENAME_LISTVIEW_CONTACTPERSON, $tableRow))
		{
			$contactPerson = $tableRow[self::TABLENAME_LISTVIEW_CONTACTPERSON];
			$pWpDb->delete($prefix.self::TABLENAME_LISTVIEW_CONTACTPERSON,
					$whereListviewTable);
			$pInsert->insertContactPerson($this->_listviewId, $contactPerson);
		}

		return $result !== false;
	}
}
