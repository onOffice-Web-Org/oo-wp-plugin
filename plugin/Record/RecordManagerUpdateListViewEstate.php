<?php

/**
 *
 *    Copyright (C) 2017-2019 onOffice GmbH
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

use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\ComplexUnitsMainIdResolver;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use const ONOFFICE_DI_CONFIG_PATH;

/**
 *
 */

class RecordManagerUpdateListViewEstate
	extends RecordManagerUpdate
{
	/**
	 *
	 * @param DataListView $pDataViewList
	 * @return bool
	 *
	 */

	public function updateByDataListView(DataListView $pDataViewList): bool
	{
		$row = [
			'name' => $pDataViewList->getName(),
			'sortby' => $pDataViewList->getSortby(),
			'sortorder' => $pDataViewList->getSortOrder(),
			'show_status' => $pDataViewList->getShowStatus(),
			'list_type' => $pDataViewList->getListType(),
			'template' => $pDataViewList->getTemplate(),
			'recordsPerPage' => $pDataViewList->getRecordsPerPage(),
			'random' => $pDataViewList->getRandom(),
			'sortBySetting' => $pDataViewList->getSortBySetting(),
			'sortByUserDefinedDefault' => $pDataViewList->getSortByUserDefinedDefault(),
			'sortByUserDefinedDirection' => $pDataViewList->getSortByUserDefinedDirection(),
			'parent_estate_id' => $pDataViewList->getParentEstateId(),
			'parent_estate_main_ids' => wp_json_encode($pDataViewList->getParentEstateMainIds()),
		];

		$tableRow = [
			self::TABLENAME_LIST_VIEW => $row,
			self::TABLENAME_PICTURETYPES => $pDataViewList->getPictureTypes(),
			self::TABLENAME_SORTBYUSERVALUES => $pDataViewList->getSortByUserValues(),
			self::TABLENAME_FIELDCONFIG => $pDataViewList->getFields(),
			self::TABLENAME_LISTVIEW_CONTACTPERSON => $pDataViewList->getAddressFields(),
		];

		return $this->updateByRow($tableRow);
	}


	/**
	 *
	 * @param array $tableRow
	 * @return bool success
	 *
	 */

	public function updateByRow(array $tableRow): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$suppressErrors = $pWpDb->suppress_errors();
		$whereListviewTable = ['listview_id' => $this->getRecordId()];
		$result = $pWpDb->update($prefix.self::TABLENAME_LIST_VIEW,
			$tableRow[self::TABLENAME_LIST_VIEW], $whereListviewTable);
		$pWpDb->suppress_errors($suppressErrors);

		if (array_key_exists(self::TABLENAME_FIELDCONFIG, $tableRow)) {
			$fields = $tableRow[self::TABLENAME_FIELDCONFIG];
			$pWpDb->delete($prefix.self::TABLENAME_FIELDCONFIG, $whereListviewTable);
			foreach ($fields as $fieldRow) {
				$table = $prefix.self::TABLENAME_FIELDCONFIG;
				$pWpDb->insert($table, $fieldRow);
			}
		}

		if (array_key_exists(self::TABLENAME_PICTURETYPES, $tableRow)) {
			$pictures = $tableRow[self::TABLENAME_PICTURETYPES];
			$pWpDb->delete($prefix.self::TABLENAME_PICTURETYPES, $whereListviewTable);
			foreach ($pictures as $pictureRow) {
				$table = $prefix.self::TABLENAME_PICTURETYPES;
				if (is_array($pictureRow)) {
					$pWpDb->insert($table, $pictureRow);
				}
			}
		}

		if (array_key_exists(self::TABLENAME_SORTBYUSERVALUES, $tableRow)) {
			$sortbyuservalues = $tableRow[self::TABLENAME_SORTBYUSERVALUES];
			$pWpDb->delete($prefix.self::TABLENAME_SORTBYUSERVALUES, $whereListviewTable);
			foreach ($sortbyuservalues as $sortbyuservaluesRow) {
				$table = $prefix.self::TABLENAME_SORTBYUSERVALUES;
				if (is_array($sortbyuservaluesRow)) {
					$pWpDb->insert($table, $sortbyuservaluesRow);
				}
			}
		}

		if (array_key_exists(self::TABLENAME_LISTVIEW_CONTACTPERSON, $tableRow)) {
			$contactPerson = $tableRow[self::TABLENAME_LISTVIEW_CONTACTPERSON];
			$pWpDb->delete($prefix.self::TABLENAME_LISTVIEW_CONTACTPERSON, $whereListviewTable);
			foreach ($contactPerson as $contactPersonRow) {
				$table = $prefix.self::TABLENAME_FIELDCONFIG;
				$pWpDb->insert($table, $contactPersonRow);
			}
		}

		return $result !== false;
	}

	/**
	 * Sets the parent estate id for a "complexunits" list view and resolves + persists the
	 * language-specific main ids for all currently active WPML languages.
	 *
	 * This is the internal PHP entry point other plugins (e.g. oo-vue-addons, right after
	 * cloning a project website box) are meant to call directly, e.g.:
	 *   (new RecordManagerUpdateListViewEstate($listviewId))->updateParentEstateId($immobilienId);
	 * There is intentionally no REST endpoint for this in this plugin.
	 *
	 * @param string $parentEstateId
	 * @return bool success
	 */
	public function updateParentEstateId(string $parentEstateId): bool
	{
		$pRecordManagerRead = new RecordManagerReadListViewEstate();
		$row = $pRecordManagerRead->getRowById($this->getRecordId());

		if (empty($row)) {
			return false;
		}

		$pFactory = new DataListViewFactory($pRecordManagerRead);
		$pDataListView = $pFactory->createListViewByRow($row);
		$pDataListView->setParentEstateId($parentEstateId);

		$pDIContainerBuilder = new ContainerBuilder();
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		/** @var ComplexUnitsMainIdResolver $pResolver */
		$pResolver = $pContainer->get(ComplexUnitsMainIdResolver::class);
		$pDataListView->setParentEstateMainIds($pResolver->resolveMainIdsByLanguage($parentEstateId));

		return $this->updateByDataListView($pDataListView);
	}

	/**
	 * Persists the selected fields for this listview from scratch (delete + re-insert), with all
	 * per-field display options (filterable/hidden/highlighted/availableOptions/
	 * convertTextToSelectForCityField/rangeFieldDisplayMode) left at their default/empty value,
	 * since they are not relevant for a plain card/table display such as complexunits.
	 *
	 * Intentionally NOT implemented via updateByRow(self::TABLENAME_FIELDCONFIG, ...): the row
	 * shape updateByDataListView() currently passes for that table (a flat list of field names,
	 * see getFields()) does not match what updateByRow() expects there (associative per-field
	 * rows, as produced by the admin form's InputModelDBAdapterRow) - that existing code path
	 * appears to have never been exercised with non-empty fields.
	 *
	 * This is the internal PHP entry point other plugins (e.g. oo-vue-addons) are meant to call
	 * directly, e.g.:
	 *   (new RecordManagerUpdateListViewEstate($listviewId))->updateSelectedFields($fieldNames);
	 *
	 * @param string[] $fieldNames
	 * @return bool success
	 */
	public function updateSelectedFields(array $fieldNames): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();
		$listviewId = $this->getRecordId();

		$pWpDb->delete($prefix.self::TABLENAME_FIELDCONFIG, ['listview_id' => $listviewId]);

		$result = true;
		foreach (array_values($fieldNames) as $index => $fieldName) {
			$inserted = $pWpDb->insert($prefix.self::TABLENAME_FIELDCONFIG, [
				'listview_id' => $listviewId,
				'order' => $index + 1,
				'fieldname' => $fieldName,
				'filterable' => 0,
				'hidden' => 0,
				'highlighted' => 0,
				'availableOptions' => 0,
				'convertTextToSelectForCityField' => 0,
				'rangeFieldDisplayMode' => '',
			]);
			$result = $result && ($inserted !== false);
		}

		return $result;
	}

	/**
	 * Enables/disables the "Show estate map" option for this listview (used e.g. by the
	 * complexunits list type to show a map alongside the list of units). This is the internal
	 * PHP entry point other plugins (e.g. oo-vue-addons) are meant to call directly, e.g.:
	 *   (new RecordManagerUpdateListViewEstate($listviewId))->updateShowMap($showMap);
	 *
	 * @param bool $showMap
	 * @return bool success
	 */
	public function updateShowMap(bool $showMap): bool
	{
		$prefix = $this->getTablePrefix();
		$pWpDb = $this->getWpdb();

		$result = $pWpDb->update(
			$prefix.self::TABLENAME_LIST_VIEW,
			['show_map' => $showMap ? 1 : 0],
			['listview_id' => $this->getRecordId()]
		);

		return $result !== false;
	}
}
