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
class RecordManagerDuplicateListViewForm extends RecordManager
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

	public function duplicateByName(string $name)
	{
		$prefix = $this->_pWPDB->prefix;

		/* @var $pRecordManagerReadListViewEstate RecordManagerReadForm */
		$pRecordManagerReadListViewEstate = $this->_pContainer->get(RecordManagerReadForm::class);
		$listViewRoot = $pRecordManagerReadListViewEstate->getRowByName($name);

		if (isset($listViewRoot) && $listViewRoot != null) {
			$tableListViews = $prefix . self::TABLENAME_FORMS;

			$originalName = $listViewRoot['name'];
			$newName = "{$originalName} - Copy";
			$selectLikeOriginalName = "SELECT `name`, `form_id` FROM {$this->_pWPDB->_escape($tableListViews)} WHERE name LIKE '{$this->_pWPDB->_escape($originalName)}%' ORDER BY form_id DESC";
			$listViewsRows = $this->_pWPDB->get_row($selectLikeOriginalName);
			$id = $listViewsRows->form_id;

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
				if (is_array($value) || $key === 'form_id' || $key === 'name') {
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
				$tableFieldConfig = $prefix . self::TABLENAME_FIELDCONFIG_FORMS;
				foreach ($listViewRoot['fields'] as $field) {
					$selectFieldConfigByIdAndFieldName = "SELECT * FROM {$this->_pWPDB->_escape($tableFieldConfig)} WHERE form_id='{$this->_pWPDB->_escape($id)}' AND fieldname ='{$this->_pWPDB->_escape($field)}'";
					$fieldConfigRows = $this->_pWPDB->get_results($selectFieldConfigByIdAndFieldName);
					if (!empty($fieldConfigRows) && (count($fieldConfigRows) !== 0)) {
						foreach ($fieldConfigRows as $fieldConfigRow) {
							$newFieldConfigRow = [];
							$newFieldConfigRow['form_id'] = $duplicateListViewId;
							$newFieldConfigRow['order'] = $fieldConfigRow->order;
							$newFieldConfigRow['fieldname'] = $field;
							$newFieldConfigRow['fieldlabel'] = $fieldConfigRow->fieldlabel;
							$newFieldConfigRow['module'] = $fieldConfigRow->module;
							$newFieldConfigRow['individual_fieldname'] = $fieldConfigRow->individual_fieldname;
							$newFieldConfigRow['required'] = $fieldConfigRow->required;
							$newFieldConfigRow['availableOptions'] = $fieldConfigRow->availableOptions;
							$this->_pWPDB->insert($tableFieldConfig, $newFieldConfigRow);
						}
					}
				}

				//duplicate data related oo_plugin_fieldconfig_form_defaults table
				$formDefaultsFieldConfigTable = $prefix . self::TABLENAME_FIELDCONFIG_FORM_DEFAULTS;
				$formDefaultsFieldConfigValueTable = $prefix . self::TABLENAME_FIELDCONFIG_FORM_DEFAULTS_VALUES;
				$selectFormDefaultsById = "SELECT * FROM {$this->_pWPDB->_escape($formDefaultsFieldConfigTable)} WHERE form_id='{$this->_pWPDB->_escape($id)}'";
				$formDefaultsRows = $this->_pWPDB->get_results($selectFormDefaultsById);
				if (!empty($formDefaultsRows) && (count($formDefaultsRows) !== 0)) {
					foreach ($formDefaultsRows as $formDefaultsRow) {
						$newFormDefaultsRow = [];
						$newFormDefaultsRow['form_id'] = $duplicateListViewId;
						$newFormDefaultsRow['fieldname'] = $formDefaultsRow->fieldname;
						$this->_pWPDB->insert($formDefaultsFieldConfigTable, $newFormDefaultsRow);

						$newDefaultsId = $this->_pWPDB->insert_id;
						$selectFormDefaultValueById = "SELECT * FROM {$this->_pWPDB->_escape($formDefaultsFieldConfigValueTable)} WHERE defaults_id ='{$this->_pWPDB->_escape($formDefaultsRow->defaults_id)}'";
						$formDefaultsValueRows = $this->_pWPDB->get_results($selectFormDefaultValueById);
						if (!empty($formDefaultsValueRows) && (count($formDefaultsValueRows) !== 0)) {
							foreach ($formDefaultsValueRows as $formDefaultsValueRow) {
								$newFormDefaultsValueRow = [];
								$newFormDefaultsValueRow['defaults_id'] = $newDefaultsId;
								$newFormDefaultsValueRow['locale'] = $formDefaultsValueRow->locale;
								$newFormDefaultsValueRow['value'] = $formDefaultsValueRow->value;
								$this->_pWPDB->insert($formDefaultsFieldConfigValueTable, $newFormDefaultsValueRow);
							}
						}
					}
				}

				//duplicate data related oo_plugin_fieldconfig_form_customs_labels table
				$tableFieldFormCustomLabel = $prefix . self::TABLENAME_FIELDCONFIG_FORM_CUSTOMS_LABELS;
				$tableFieldFormTranslatedLabel = $prefix . self::TABLENAME_FIELDCONFIG_FORM_TRANSLATED_LABELS;
				$selectFieldFormCustomLabelById = "SELECT * FROM {$this->_pWPDB->_escape($tableFieldFormCustomLabel)} WHERE form_id='{$this->_pWPDB->_escape($id)}'";
				$fieldFormCustomLabelRows = $this->_pWPDB->get_results($selectFieldFormCustomLabelById);
				if (!empty($fieldFormCustomLabelRows) && (count($fieldFormCustomLabelRows) !== 0)) {
					foreach ($fieldFormCustomLabelRows as $fieldFormCustomLabelRow) {
						$newFieldFormCustomLabelRow = [];
						$newFieldFormCustomLabelRow['form_id'] = $duplicateListViewId;
						$newFieldFormCustomLabelRow['fieldname'] = $fieldFormCustomLabelRow->fieldname;
						$this->_pWPDB->insert($tableFieldFormCustomLabel, $newFieldFormCustomLabelRow);

						$newCustomLabel = $this->_pWPDB->insert_id;
						$selectFormDefaultValueById = "SELECT * FROM {$this->_pWPDB->_escape($tableFieldFormTranslatedLabel)} WHERE input_id ='{$this->_pWPDB->_escape($fieldFormCustomLabelRow->customs_labels_id)}'";
						$formTranslatedLabelRows = $this->_pWPDB->get_results($selectFormDefaultValueById);
						if (!empty($formTranslatedLabelRows) && (count($formTranslatedLabelRows) !== 0)) {
							foreach ($formTranslatedLabelRows as $formTranslatedLabelRow) {
								$newFormTranslatedLabelRow = [];
								$newFormTranslatedLabelRow['input_id'] = $newCustomLabel;
								$newFormTranslatedLabelRow['locale'] = $formTranslatedLabelRow->locale;
								$newFormTranslatedLabelRow['value'] = $formTranslatedLabelRow->value;
								$this->_pWPDB->insert($tableFieldFormTranslatedLabel, $newFormTranslatedLabelRow);
							}
						}
					}
				}
			}
		}
	}
}
