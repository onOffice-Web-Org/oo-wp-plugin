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

namespace onOffice\WPlugin\Field\CustomLabel;

use onOffice\WPlugin\Types\Field;
use wpdb;
use const OBJECT;
use function esc_sql;


/**
 *
 * Reads default values as defined in WP, not onOffice enterprise
 *
 */
class CustomLabelRead
{
	/** @var wpdb */
	private $_pWPDB;


	/**
	 * @param wpdb $pWPDB
	 */
	public function __construct(wpdb $pWPDB)
	{
		$this->_pWPDB = $pWPDB;
	}

	/**
	 * @param int $formId
	 * @param array $pFields
	 * @param string $currentLocale
	 * @param string $pCustomsLabelConfigurationField
	 * @param string $pTranslateLabelConfigurationField
	 * @return array
	 */
	public function getCustomLabelsFieldsForAdmin(int $formId, array $pFields, string $currentLocale, string $pCustomsLabelConfigurationField, string $pTranslateLabelConfigurationField): array
	{
		$pDataModels = [];
		$customLabelRows = $this->readCustomLabelsField($formId, $pFields, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField);
		$rowsByFieldName = $this->groupCustomLabelRowsByFieldName($customLabelRows);

		foreach ($pFields as $pField) {
			$pDataModel = new CustomLabelModelField($formId, $pField);
			if (isset($rowsByFieldName[$pField->getName()])) {
				foreach ($rowsByFieldName[$pField->getName()] as $pRow) {
					$pDataModel->addValueByLocale($pRow->locale, $pRow->value);
				}
			}

			$valuesByLocale = $this->getLocalizedValues($pDataModel, $currentLocale);
			$pDataModels[$pField->getName()] = $valuesByLocale;
		}

		return $pDataModels;
	}

	/**
	 * @param CustomLabelModelField $pDataModel
	 * @param string $currentLocale
	 * @return array
	 */
	private function getLocalizedValues(CustomLabelModelField $pDataModel, string $currentLocale): array
	{
		$valuesByLocale = $pDataModel->getValuesByLocale();
	
		if (isset($valuesByLocale[$currentLocale])) {
			$valuesByLocale['native'] = $valuesByLocale[$currentLocale];
			unset($valuesByLocale[$currentLocale]);
		}
	
		return $valuesByLocale;
	}

	/**
	 * @param array $rows
	 * @return array
	 */
	private function groupCustomLabelRowsByFieldName(array $rows): array
	{
		$customLabelGroup = [];
		foreach ($rows as $row) {
			$customLabelGroup[$row->fieldname][] = $row;
		}

		return $customLabelGroup;
	}

	/**
	 * @param int $formId
	 * @param array $pFields
	 * @param string $pCustomsLabelConfigurationField
	 * @param string $pTranslateLabelConfigurationField
	 * @return array
	 */
	public function readCustomLabelsField(int $formId, array $pFields, string $pCustomsLabelConfigurationField, string $pTranslateLabelConfigurationField): array
	{
		$pFieldNames = array_map(function ($field) {
			return $field->getName();
		}, $pFields);

		$query = $this->createBaseQuery($formId, $pFieldNames, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField);

		return $this->_pWPDB->get_results($query, OBJECT);
	}

	/**
	 * @param int $formId
	 * @param $field
	 * @param $current_lang
	 * @return array
	 */
	public function readCustomLabelByFormIdAndFieldName(int $formId, $field, $current_lang, string $pCustomsLabelConfigurationField, string $pTranslateLabelConfigurationField): array
	{
		$query = $this->createCustomsLabelsByFormIdQuery($formId, $field, $current_lang, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField);
		return $this->_pWPDB->get_results($query, OBJECT);
	}


	/**
	 * @param int $formId
	 * @param array $pFieldNames
	 * @param string $pCustomsLabelConfigurationField
	 * @param string $pTranslateLabelConfigurationField
	 * @return string
	 */
	private function createBaseQuery(int $formId, array $pFieldNames, string $pCustomsLabelConfigurationField, string $pTranslateLabelConfigurationField): string
	{
		$prefix = $this->_pWPDB->prefix;

		$names = array_map(function ($item) {
			return "'" . esc_sql($item) . "'";
		}, $pFieldNames);
		$names = implode(',', $names);

		$query = "SELECT {$prefix}$pCustomsLabelConfigurationField.customs_labels_id,"
			. "{$prefix}$pTranslateLabelConfigurationField.locale,\n"
			. "{$prefix}$pTranslateLabelConfigurationField.value,\n"
			. "{$prefix}$pCustomsLabelConfigurationField.fieldname\n"
			. "FROM {$prefix}$pCustomsLabelConfigurationField\n"
			. "INNER JOIN {$prefix}$pTranslateLabelConfigurationField\n"
			. "ON {$prefix}$pCustomsLabelConfigurationField.customs_labels_id = "
			. " {$prefix}$pTranslateLabelConfigurationField.input_id\n"
			. "WHERE {$prefix}$pCustomsLabelConfigurationField.fieldname IN (" . $names . ") AND\n"
			. " {$prefix}$pCustomsLabelConfigurationField.form_id = " . esc_sql($formId);

		return $query;
	}

	/**
	 * @param int $formId
	 * @param string $field
	 * @param $current_lang
	 * @return string
	 */
	private function createCustomsLabelsByFormIdQuery(int $formId, string $field, $current_lang, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField): string
	{
		$prefix = $this->_pWPDB->prefix;
		$queryByFormId = "SELECT {$prefix}$pCustomsLabelConfigurationField.customs_labels_id,"
			. "{$prefix}$pTranslateLabelConfigurationField.locale,\n"
			. "{$prefix}$pTranslateLabelConfigurationField.value\n"
			. "FROM {$prefix}$pCustomsLabelConfigurationField\n"
			. "INNER JOIN {$prefix}$pTranslateLabelConfigurationField\n"
			. "ON {$prefix}$pCustomsLabelConfigurationField.customs_labels_id = "
			. " {$prefix}$pTranslateLabelConfigurationField.input_id\n"
			. "WHERE BINARY {$prefix}$pCustomsLabelConfigurationField.fieldname = '" . esc_sql($field) . "' AND\n"
			. "{$prefix}$pTranslateLabelConfigurationField.locale = '" . esc_sql($current_lang) . "' AND\n"
			. " {$prefix}$pCustomsLabelConfigurationField.form_id = " . esc_sql($formId);
		return $queryByFormId;
	}
}
