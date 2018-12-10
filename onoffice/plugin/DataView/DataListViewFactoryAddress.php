<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DataListViewFactoryAddress
	extends DataListViewFactoryBase
{
	/**
	 *
	 */

	public function __construct()
	{
		$this->setRecordManagerRead(new RecordManagerReadListViewAddress());
	}


	/**
	 *
	 * @param array $row
	 * @return DataListViewAddress
	 *
	 */

	public function createListViewByRow(array $row)
	{
		$pDataListViewAddress = new DataListViewAddress($row['listview_address_id'], $row['name']);
		$pDataListViewAddress->setFields($row['fields']);
		$pDataListViewAddress->setFilterId($row['filterId']);
		$pDataListViewAddress->setRecordsPerPage($row['recordsPerPage']);
		$pDataListViewAddress->setShowPhoto((bool)$row['showPhoto']);
		$pDataListViewAddress->setSortby($row['sortby']);
		$pDataListViewAddress->setSortorder($row['sortorder']);
		$pDataListViewAddress->setTemplate($row['template']);
		$pDataListViewAddress->setFilterableFields($row['filterable']);
		$pDataListViewAddress->setFilterableHiddenFields($row['hidden']);

		return $pDataListViewAddress;
	}
}
