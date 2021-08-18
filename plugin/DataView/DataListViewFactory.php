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

use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataListViewFactory
	extends DataListViewFactoryBase
{
	/**
	 *
	 * @param RecordManagerReadListViewEstate $pRecordManagerReadListViewEstate
	 *
	 */

	public function __construct(RecordManagerReadListViewEstate $pRecordManagerReadListViewEstate = null)
	{
		$this->setRecordManagerRead($pRecordManagerReadListViewEstate ?? new RecordManagerReadListViewEstate());
	}


	/**
	 *
	 * @param array $row
	 * @return DataListView
	 *
	 */

	public function createListViewByRow(array $row): DataViewFilterableFields
	{
		$pListView = new DataListView($row['listview_id'], $row['name']);
		$pListView->setExpose($row['expose']);
		$pListView->setFields($row[DataListView::FIELDS]);
		$pListView->setFilterId($row['filterId'] ?? 0);
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
		$pListView->setAvailableOptions($row['availableOptions']);
		$pListView->setSortBySetting((int)$row['sortBySetting']);
		$pListView->setSortByUserDefinedDefault($row['sortByUserDefinedDefault']);
		$pListView->setSortByUserDefinedDirection($row['sortByUserDefinedDirection']);
		$pListView->setSortByUserValues($row[DataListView::SORT_BY_USER_VALUES]);

		$geoFieldsAll = [
			InputModelDBFactoryConfigGeoFields::FIELDNAME_COUNTRY_ACTIVE => GeoPosition::ESTATE_LIST_SEARCH_COUNTRY,
			InputModelDBFactoryConfigGeoFields::FIELDNAME_RADIUS_ACTIVE => GeoPosition::ESTATE_LIST_SEARCH_RADIUS,
			InputModelDBFactoryConfigGeoFields::FIELDNAME_STREET_ACTIVE => GeoPosition::ESTATE_LIST_SEARCH_STREET,
			InputModelDBFactoryConfigGeoFields::FIELDNAME_ZIP_ACTIVE => GeoPosition::ESTATE_LIST_SEARCH_ZIP,
			InputModelDBFactoryConfigGeoFields::FIELDNAME_RADIUS => $row['radius']
		];

		$geoFieldsActive = array_intersect_key($geoFieldsAll, $row);
		$pListView->setGeoFields($geoFieldsActive);

		return $pListView;
	}
}
