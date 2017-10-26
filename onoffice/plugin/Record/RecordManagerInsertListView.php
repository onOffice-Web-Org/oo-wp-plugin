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

	static public function insertByDataViewList(DataListView $pDataViewList)
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

		$values = array
			(
				'name' => $name,
				'sortby' => $sortby,
				'sortorder' => $sortorder,
				'show_status' => $showStatus,
				'list_type' => $listType,
				'template' => $template,
				'recordsPerPage' => $recordsPerPage,
			);

		$row = array
				(
					DataListView::TABLENAME_LIST_VIEW => $values,
					DataListView::TABLENAME_PICTUTYPES => $pictures,
					DataListView::TABLENAME_FIELDCONFIG => $fields,
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
		$row = $tableRow[DataListView::TABLENAME_LIST_VIEW];

		$pWpDb->insert($pWpDb->prefix.DataListView::TABLENAME_LIST_VIEW, $row);
		$listViewId = $pWpDb->insert_id;

		if (array_key_exists(DataListView::TABLENAME_FIELDCONFIG, $tableRow))
		{
			$fields = $tableRow[DataListView::TABLENAME_FIELDCONFIG];
			$this->insertFields($listViewId, $fields);
		}

		if (array_key_exists(DataListView::TABLENAME_PICTUTYPES, $tableRow))
		{
			$pictures = $tableRow[DataListView::TABLENAME_PICTUTYPES];
			$this->insertPictures($listViewId, $pictures);
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
		$table = $prefix.DataListView::TABLENAME_FIELDCONFIG;

		//...

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
		$table = $prefix.DataListView::TABLENAME_PICTUTYPES;

		if (array_key_exists('picturetype', $pictures))
		{
			$picturetypes = $pictures['picturetype'];

			foreach ($picturetypes as $type)
			{
				$pictureRow = array();
				$pictureRow['picturetype'] = $type;
				$pictureRow['listview_id'] = $listViewId;
				$pWpDb->insert($table, $pictureRow);
			}
		}
	}
}
