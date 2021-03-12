<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\WPlugin\Record;

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use wpdb;


/**
 *
 */
class RecordManagerDuplicateListViewEstate extends RecordManager
{
	/** @var wpdb */
	private $_pWPDB;


	/**
	 *
	 * @param wpdb $pWPDB
	 *
	 */

	public function __construct(wpdb $pWPDB)
	{
		$this->_pWPDB = $pWPDB;
	}

	/**
	 *
	 * @param int $id
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function duplicateByIds(int $id)
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pDI = $pContainerBuilder->build();
		$prefix = $this->_pWPDB->prefix;

		/* @var $pRecordManagerReadListViewEstate RecordManagerReadListViewEstate */
		$pRecordManagerReadListViewEstate = $pDI->get(RecordManagerReadListViewEstate::class);
		$listViewRoot = $pRecordManagerReadListViewEstate->getRowById($id);

		if (isset($listViewRoot) && $listViewRoot != null) {
			$tableListViews = $prefix . self::TABLENAME_LIST_VIEW;

			$originalName = $listViewRoot['name'];
			$newName = "{$originalName} - Copy";
			$selectLikeOriginalName = "SELECT `name` FROM {$tableListViews} WHERE name LIKE '{$originalName}%' ORDER BY listview_id DESC";
			$listViewsRows = $this->_pWPDB->get_row($selectLikeOriginalName);

			if (!empty($listViewsRows->name) && $listViewsRows->name !== $originalName) {
				preg_match('/\s[0-9]+$/', $listViewsRows->name, $matches);
				$counter = 2;
				if (!empty($matches)) {
					$counter = (int)$matches[0] + 1;
				}
				$newName = "{$newName} $counter";
			}

			$this->_pWPDB->insert(
				$tableListViews,
				array(
					'name' => $newName,
					'filterId' => $listViewRoot['filterId'],
					'sortby' => $listViewRoot['sortby'],
					'sortorder' => $listViewRoot['sortorder'],
					'show_status' => $listViewRoot['show_status'],
					'list_type' => $listViewRoot['list_type'],
					'template' => $listViewRoot['template'],
					'expose' => $listViewRoot['expose'],
					'recordsPerPage' => $listViewRoot['recordsPerPage'],
					'random' => $listViewRoot['random'],
					'country_active' => $listViewRoot['country_active'],
					'zip_active' => $listViewRoot['zip_active'],
					'city_active' => $listViewRoot['city_active'],
					'street_active' => $listViewRoot['zip_active'],
					'radius_active' => $listViewRoot['radius_active'],
					'radius' => $listViewRoot['radius'],
					'geo_order' => $listViewRoot['geo_order'],
					'sortBySetting' => $listViewRoot['sortBySetting'],
					'sortByUserDefinedDefault' => $listViewRoot['sortByUserDefinedDefault'],
					'sortByUserDefinedDirection' => $listViewRoot['sortByUserDefinedDirection'],
				)
			);
			$cloneListViewId = $this->_pWPDB->insert_id;
			if ($cloneListViewId !== 0) {
				$tableFieldConfig = $prefix . self::TABLENAME_FIELDCONFIG;
				foreach ($listViewRoot['fields'] as $field) {
					$selectFieldConfigByIdAndFieldName = "SELECT * FROM {$tableFieldConfig} WHERE listview_id='{$id}' AND fieldname ='{$field}'";
					$fieldConfigRows = $this->_pWPDB->get_row($selectFieldConfigByIdAndFieldName);
					if (!empty($fieldConfigRows) != 0) {
						$newFieldConfigRows = [];
						$newFieldConfigRows['listview_id'] = $cloneListViewId;
						$newFieldConfigRows['order'] = $fieldConfigRows->order;
						$newFieldConfigRows['fieldname'] = $field;
						$newFieldConfigRows['filterable'] = $fieldConfigRows->filterable;
						$newFieldConfigRows['hidden'] = $fieldConfigRows->hidden;
						$newFieldConfigRows['availableOptions'] = $fieldConfigRows->availableOptions;
						$this->_pWPDB->insert($tableFieldConfig, $newFieldConfigRows);
					}
				}

				$tablePictureTypes = $prefix . self::TABLENAME_PICTURETYPES;
				$selectPictureTypesById = "SELECT * FROM {$tablePictureTypes} WHERE listview_id='{$id}'";
				$pictureTypeRows = $this->_pWPDB->get_row($selectPictureTypesById);
				if (!empty($pictureTypeRows) != 0) {
					$newPictureTypeRows = [];
					$newPictureTypeRows['listview_id'] = $cloneListViewId;
					$newPictureTypeRows['picturetype'] = $pictureTypeRows->picturetype;
					$this->_pWPDB->insert($tablePictureTypes, $newPictureTypeRows);
				}
			}
		}
	}
}
