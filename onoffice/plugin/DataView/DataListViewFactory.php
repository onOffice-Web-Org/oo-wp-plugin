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

use \onOffice\WPlugin\Record\RecordManagerReadListView;

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
	 * @return \onOffice\WPlugin\DataView\DataListView
	 *
	 */

	public function getListViewByName($listViewName)
	{
		$pRecordRead = new RecordManagerReadListView();
		$record = $pRecordRead->getRowByName($listViewName);
		return $this->createListViewByRow($record);
	}


	/**
	 *
	 * @param array $row
	 * @return DataListView
	 *
	 */

	private function createListViewByRow(array $row)
	{
		$pListView = new DataListView($row['listview_id'], $row['name']);
		$pListView->setExpose($row['expose']);
		$pListView->setFields($row[RecordManagerReadListView::FIELDS]);
		$pListView->setFiltername($row['filtername']);
		$pListView->setIsReference((bool)$row['is_reference']);
		$pListView->setPictureTypes($row[RecordManagerReadListView::PICTURES]);
		$pListView->setShowStatus((bool)$row['show_status']);
		$pListView->setSortby($row['sortby']);
		$pListView->setSortorder($row['sortorder']);
		$pListView->setRecordsPerPage($row['recordsPerPage']);
		return $pListView;
	}
}
