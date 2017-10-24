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
		$isReference = $pDataViewList->getIsReference();
		$template = $pDataViewList->getTemplate();
		$recordsPerPage = $pDataViewList->getRecordsPerPage();
		$pictures = $pDataViewList->getPictureTypes();
		$fields = $pDataViewList->getFields();

		$row = array
			(
				'name' => $name,
				'sortby' => $sortby,
				'sortorder' => $sortorder,
				'show_status' => $showStatus,
				'is_reference' => $isReference,
				'template' => $template,
				'recordsPerPage' => $recordsPerPage,
			);

		$tableRow = array
			(
				DataListView::TABLENAME_LIST_VIEW => $row,
				DataListView::TABLENAME_PICTUTYPES => $pictures,
				DataListView::TABLENAME_FIELDCONFIG => $fields,
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
		$result = $pWpDb->update($prefix.DataListView::TABLENAME_LIST_VIEW,
				$tableRow[DataListView::TABLENAME_LIST_VIEW],
				$whereListviewTable);

		if (array_key_exists(DataListView::TABLENAME_FIELDCONFIG, $tableRow))
		{
			$fields = $tableRow[DataListView::TABLENAME_FIELDCONFIG];
			$pWpDb->delete($prefix.DataListView::TABLENAME_FIELDCONFIG,
					$whereListviewTable);
			$pInsert->insertFields($this->_listviewId, $fields);
		}

		if (array_key_exists(DataListView::TABLENAME_PICTUTYPES, $tableRow))
		{
			$pictures = $tableRow[DataListView::TABLENAME_PICTUTYPES];
			$pWpDb->delete($prefix.DataListView::TABLENAME_PICTUTYPES,
					$whereListviewTable);
			$pInsert->insertPictures($this->_listviewId, $pictures);
		}

		return $result !== false;
	}
}
