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

namespace onOffice\WPlugin\DataView;

use onOffice\WPlugin\Record\RecordManagerReadListView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataListViewFactory
{
	/**
	 *
	 * @param string $listViewName
	 * @param string $type
	 * @return DataListView
	 *
	 */

	public function getListViewByName($listViewName, $type = null)
	{
		$pRecordRead = new RecordManagerReadListView();
		$record = $pRecordRead->getRowByName($listViewName, $type);

		if ($record === null) {
			throw new UnknownViewException($listViewName);
		}

		return $this->createListViewByRow($record);
	}


	/**
	 *
	 * @param array $row
	 * @return DataListView
	 *
	 */

	public function createListViewByRow(array $row)
	{
		$pListView = new DataListView($row['listview_id'], $row['name']);
		$pListView->setExpose($row['expose']);
		$pListView->setFields($row[DataListView::FIELDS]);
		$pListView->setFilterId($row['filterId']);
		$pListView->setListType($row['list_type']);
		$pListView->setPictureTypes($row[DataListView::PICTURES]);
		$pListView->setShowStatus((bool)$row['show_status']);
		$pListView->setSortby($row['sortby']);
		$pListView->setSortorder($row['sortorder']);
		$pListView->setRecordsPerPage($row['recordsPerPage']);
		$pListView->setTemplate($row['template']);
		$pListView->setRandom((bool)$row['random']);
		$pListView->setFilterableFields($row['filterable']);
		$pListView->setHiddenFields($row['hidden']);

		return $pListView;
	}
}
