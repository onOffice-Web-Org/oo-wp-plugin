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
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
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
	 * @param string $name
	 *
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFormException
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
					$fieldConfigRows = $this->_pWPDB->get_results($selectFieldConfigByIdAndFieldName, 'ARRAY_A');
					$this->duplicateDataRelated( $duplicateListViewId, $fieldConfigRows,
						$tableFieldConfig, 'form_id', 'form_fieldconfig_id' );
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
						$formDefaultsValueRows = $this->_pWPDB->get_results($selectFormDefaultValueById, 'ARRAY_A');
						$this->duplicateDataRelated( $newDefaultsId, $formDefaultsValueRows,
							$formDefaultsFieldConfigValueTable, 'defaults_id', 'defaults_values_id' );
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

						$newCustomLabelId = $this->_pWPDB->insert_id;
						$selectFormDefaultValueById = "SELECT * FROM {$this->_pWPDB->_escape($tableFieldFormTranslatedLabel)} WHERE input_id ='{$this->_pWPDB->_escape($fieldFormCustomLabelRow->customs_labels_id)}'";
						$formTranslatedLabelRows = $this->_pWPDB->get_results($selectFormDefaultValueById, 'ARRAY_A');
						$this->duplicateDataRelated( $newCustomLabelId, $formTranslatedLabelRows,
							$tableFieldFormTranslatedLabel, 'input_id', 'translated_label_id' );
					}
				}
			}
		}
	}


	/**
	 * @param int $newInsertDataId
	 * @param array $formDataRelatedRows
	 * @param string $relatedTableName
	 * @param string $relatedKeyId
	 * @param string $disAllowKey
	 */

	public function duplicateDataRelated(
		int $newInsertDataId,
		array $formDataRelatedRows,
		string $relatedTableName,
		string $relatedKeyId,
		string $disAllowKey
	) {
		foreach ( $formDataRelatedRows as $formDataRelatedRow ) {
			$existingColumns = $this->_pWPDB->get_col("DESC {$relatedTableName}", 0);
			$formFilterDataRelatedRow = array_intersect_key($formDataRelatedRow, array_flip($existingColumns));
			$formFilterDataRelatedRow[ $relatedKeyId ] = $newInsertDataId;
			unset( $formFilterDataRelatedRow[ $disAllowKey ] );
			$this->_pWPDB->insert( $relatedTableName, $formFilterDataRelatedRow );
		}
	}
}
