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
class RecordManagerDuplicateListViewAddress extends RecordManager
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
	 * @param string $name
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function duplicateByName(string $name)
	{
		$prefix = $this->_pWPDB->prefix;

		/* @var $pRecordManagerReadListViewAddress RecordManagerReadListViewAddress */
		$pRecordManagerReadListViewAddress = $this->_pContainer->get(RecordManagerReadListViewAddress::class);
		$listViewRoot = $pRecordManagerReadListViewAddress->getRowByName($name);

		if (isset($listViewRoot) && $listViewRoot != null) {
			$tableListViews = $prefix . self::TABLENAME_LIST_VIEW_ADDRESS;

			$originalName = $listViewRoot['name'];
			$newName = "{$originalName} - Copy";
			$selectLikeOriginalName = "SELECT `name`, `listview_address_id` FROM {$this->_pWPDB->_escape($tableListViews)} WHERE name LIKE '{$this->_pWPDB->_escape($originalName)}%' ORDER BY listview_address_id DESC";
			$listViewsRows = $this->_pWPDB->get_row($selectLikeOriginalName);
			$id = $listViewsRows->listview_address_id;
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
				if (is_array($value) || $key === 'listview_address_id' || $key === 'name') {
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
				$tableFieldConfig = $prefix . self::TABLENAME_FIELDCONFIG_ADDRESS;
				foreach ($listViewRoot['fields'] as $field) {
					$selectFieldConfigByIdAndFieldName = "SELECT * FROM {$this->_pWPDB->_escape($tableFieldConfig)} WHERE listview_address_id='{$this->_pWPDB->_escape($id)}' AND fieldname ='{$this->_pWPDB->_escape($field)}'";
					$fieldConfigRows = $this->_pWPDB->get_results($selectFieldConfigByIdAndFieldName);

					if (!empty($fieldConfigRows) && (count($fieldConfigRows) !== 0)) {
						$newFieldConfigRows = $fieldConfigRows;
						foreach ($newFieldConfigRows as $newFieldConfigRow) {
							$newFieldConfigRow->listview_address_id = $duplicateListViewId;
							unset($newFieldConfigRow->address_fieldconfig_id);
							$this->_pWPDB->insert($tableFieldConfig, (array)$newFieldConfigRow);
						}
					}
				}
			}
		}
	}
}
