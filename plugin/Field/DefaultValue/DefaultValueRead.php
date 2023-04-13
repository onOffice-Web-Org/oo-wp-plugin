<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

namespace onOffice\WPlugin\Field\DefaultValue;

use onOffice\WPlugin\Types\Field;
use wpdb;
use const OBJECT;
use function esc_sql;


/**
 *
 * Reads default values as defined in WP, not onOffice enterprise
 *
 */

class DefaultValueRead
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
	 * @param Field $pField
	 * @return DefaultValueModelSingleselect
	 */
	public function readDefaultValuesSingleselect(int $formId, Field $pField): DefaultValueModelSingleselect
	{
		$query = $this->createBaseQuery($formId, $pField);
		$row = $this->_pWPDB->get_row($query, ARRAY_A);
		$pDataModel = new DefaultValueModelSingleselect($formId, $pField);
		$pDataModel->setDefaultsId(!empty($row['defaults_id']) ? (int)$row['defaults_id'] : 0);
		$pDataModel->setValue(!empty($row['value']) ? $row['value'] : '');
		return $pDataModel;
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @return DefaultValueModelMultiselect
	 */
	public function readDefaultValuesMultiSelect(int $formId, Field $pField): DefaultValueModelMultiselect
	{
		$query = $this->createBaseQuery($formId, $pField);
		$rows = $this->_pWPDB->get_results($query, ARRAY_A);
		$values = array_column($rows, 'value');
		$pDataModel = new DefaultValueModelMultiselect($formId, $pField);
		$pDataModel->setValues($values);
		return $pDataModel;
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @return DefaultValueModelBool
	 */
	public function readDefaultValuesBool(int $formId, Field $pField): DefaultValueModelBool
	{
		$query = $this->createBaseQuery($formId, $pField);
		$row = $this->_pWPDB->get_row($query, ARRAY_A);
		$pDataModel = new DefaultValueModelBool($formId, $pField);
		$pDataModel->setDefaultsId(!empty($row['defaults_id']) ? (int)$row['defaults_id'] : 0);
		$pDataModel->setValue(!empty($row['value']) ? (bool)intval($row['value']) : false);

		return $pDataModel;
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @return DefaultValueModelText
	 */
	public function readDefaultValuesText(int $formId, Field $pField): DefaultValueModelText
	{
		$query = $this->createBaseQuery($formId, $pField);
		$rows = $this->_pWPDB->get_results($query, OBJECT);
		$pDataModel = new DefaultValueModelText($formId, $pField);

		foreach ($rows as $pRow) {
			$pDataModel->addValueByLocale($pRow->locale, $pRow->value);
		}

		return $pDataModel;
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @return DefaultValueModelNumericRange
	 */
	public function readDefaultValuesNumericRange(int $formId, Field $pField): DefaultValueModelNumericRange
	{
		$query = $this->createBaseQuery($formId, $pField);
		$rows = $this->_pWPDB->get_results($query, OBJECT);
		$pDataModel = new DefaultValueModelNumericRange($formId, $pField);

		if (count($rows) === 2) {
			$pDataModel->setValueFrom((float)$rows[0]->value);
			$pDataModel->setValueTo((float)$rows[1]->value);
		}
		return $pDataModel;
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @return string
	 */
	private function createBaseQuery(int $formId, Field $pField): string
	{
		$prefix = $this->_pWPDB->prefix;
		$query = "SELECT {$prefix}oo_plugin_fieldconfig_form_defaults.defaults_id,"
			."{$prefix}oo_plugin_fieldconfig_form_defaults_values.locale,\n"
			."{$prefix}oo_plugin_fieldconfig_form_defaults_values.value\n"
			."FROM {$prefix}oo_plugin_fieldconfig_form_defaults\n"
			."INNER JOIN {$prefix}oo_plugin_fieldconfig_form_defaults_values\n"
			."ON {$prefix}oo_plugin_fieldconfig_form_defaults.defaults_id = "
			." {$prefix}oo_plugin_fieldconfig_form_defaults_values.defaults_id\n"
			."WHERE {$prefix}oo_plugin_fieldconfig_form_defaults.fieldname = '".esc_sql($pField->getName())."' AND\n"
			." {$prefix}oo_plugin_fieldconfig_form_defaults.form_id = ".esc_sql($formId);
		return $query;
	}

}
