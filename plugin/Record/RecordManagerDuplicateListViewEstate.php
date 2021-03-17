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

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Nette\DI\Extensions\DIExtension;
use wpdb;


/**
 *
 */
class RecordManagerDuplicateListViewEstate extends RecordManager
{
	/** @var wpdb */
	private $_pWPDB;

	/** @var Container */
	private $_pContainer = null;

	/**
	 *
	 * @param wpdb $pWPDB
	 * @param Container $_pContainer
	 * @throws \Exception
	 */

	public function __construct(wpdb $pWPDB, Container $_pContainer = null)
	{
		$this->_pWPDB = $pWPDB;
		if ($_pContainer === null) {
			$pDIContainerBuilder = new ContainerBuilder;
			$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
			$_pContainer = $pDIContainerBuilder->build();
		}
		$this->_pContainer = $_pContainer;
	}

	/**
	 *
	 * @param int $id
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function duplicateByIds(int $id)
	{
		$prefix = $this->_pWPDB->prefix;

		/* @var $pRecordManagerReadListViewEstate RecordManagerReadListViewEstate */
		$pRecordManagerReadListViewEstate = $this->_pContainer->get(RecordManagerReadListViewEstate::class);
		$listViewRoot = $pRecordManagerReadListViewEstate->getRowById($id);

		if (isset($listViewRoot) && $listViewRoot != null) {
			$tableListViews = $prefix . self::TABLENAME_LIST_VIEW;

			//append count name
			$originalName = $listViewRoot['name'];
			$newName = "{$originalName} - Copy";
			$selectLikeOriginalName = "SELECT `name` FROM {$tableListViews} WHERE name LIKE '{$newName}%' ORDER BY listview_id DESC";
			$listViewsRows = $this->_pWPDB->get_row($selectLikeOriginalName);

			if (!empty($listViewsRows->name) && $listViewsRows->name !== $originalName) {
				preg_match('/\s[0-9]+$/', $listViewsRows->name, $matches);
				$counter = 2;
				if (!empty($matches)) {
					$counter = (int)$matches[0] + 1;
				}
				$newName = "{$newName} $counter";
			}

			$newNameData = ['name' => $newName];

			//duplicate root data to new list view
			$newListView = [];
			foreach ($listViewRoot as $key => $value) {
				if (is_array($value) || $key === 'listview_id' || $key === 'name') {
					continue;
				}
				$newListView[$key] = $value;
			}
			$newListView = array_merge($newNameData, $newListView);

			$this->_pWPDB->insert(
				$tableListViews,
				$newListView
			);
			$duplicateListViewId = $this->_pWPDB->insert_id;

			if ($duplicateListViewId !== 0) {
				//duplicate data related oo_plugin_fieldconfig table
				$tableFieldConfig = $prefix . self::TABLENAME_FIELDCONFIG;
				foreach ($listViewRoot['fields'] as $field) {
					$selectFieldConfigByIdAndFieldName = "SELECT * FROM {$tableFieldConfig} WHERE listview_id='{$id}' AND fieldname ='{$field}'";
					$fieldConfigRows = $this->_pWPDB->get_results($selectFieldConfigByIdAndFieldName);
					if (!empty($fieldConfigRows) && (count($fieldConfigRows) !== 0)) {
						foreach ($fieldConfigRows as $fieldConfigRow) {
							$newFieldConfigRow = [];
							$newFieldConfigRow['listview_id'] = $duplicateListViewId;
							$newFieldConfigRow['order'] = $fieldConfigRow->order;
							$newFieldConfigRow['fieldname'] = $field;
							$newFieldConfigRow['filterable'] = $fieldConfigRow->filterable;
							$newFieldConfigRow['hidden'] = $fieldConfigRow->hidden;
							$newFieldConfigRow['availableOptions'] = $fieldConfigRow->availableOptions;
							$this->_pWPDB->insert($tableFieldConfig, $newFieldConfigRow);
						}
					}
				}

				//duplicate data related oo_plugin_picturetypes table
				$tablePictureTypes = $prefix . self::TABLENAME_PICTURETYPES;
				$selectPictureTypesById = "SELECT * FROM {$tablePictureTypes} WHERE listview_id='{$id}'";
				$pictureTypeRows = $this->_pWPDB->get_results($selectPictureTypesById);
				if (!empty($pictureTypeRows) && (count($pictureTypeRows) !== 0)) {
					foreach ($pictureTypeRows as $pictureTypeRow) {
						$newPictureTypeRow = [];
						$newPictureTypeRow['listview_id'] = $duplicateListViewId;
						$newPictureTypeRow['picturetype'] = $pictureTypeRow->picturetype;
						$this->_pWPDB->insert($tablePictureTypes, $newPictureTypeRow);
					}
				}

				//duplicate data related oo_plugin_sortbyuservalues table
				$tableSortByUserValues = $prefix . self::TABLENAME_SORTBYUSERVALUES;
				$selectSortByUserValuesById = "SELECT * FROM {$tableSortByUserValues} WHERE listview_id='{$id}'";
				$sortByUserValuesRows = $this->_pWPDB->get_results($selectSortByUserValuesById);
				if (!empty($sortByUserValuesRows) && (count($sortByUserValuesRows) !== 0)) {
					foreach ($sortByUserValuesRows as $sortByUserValuesRow) {
						$newSortByUserValuesRow = [];
						$newSortByUserValuesRow['listview_id'] = $duplicateListViewId;
						$newSortByUserValuesRow['sortbyuservalue'] = $sortByUserValuesRow->sortbyuservalue;
						$this->_pWPDB->insert($tableSortByUserValues, $newSortByUserValuesRow);
					}
				}
			}
		}
	}
}
