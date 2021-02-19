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
	 * @param Field $pField
	 * @return CustomLabelModelText
	 */
	public function readCustomLabelsText(int $formId, Field $pField): CustomLabelModelText
	{
		$query = $this->createBaseQuery($formId, $pField);
		$rows = $this->_pWPDB->get_results($query, OBJECT);
		$pDataModel = new CustomLabelModelText($formId, $pField);

		foreach ($rows as $pRow) {
			$pDataModel->addValueByLocale($pRow->locale, $pRow->value);
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
			."{$prefix}oo_plugin_fieldconfig_form_customs_labels.locale,\n"
			."{$prefix}oo_plugin_fieldconfig_form_customs_labels.value\n"
			."FROM {$prefix}oo_plugin_fieldconfig_form_defaults\n"
			."INNER JOIN {$prefix}oo_plugin_fieldconfig_form_customs_labels\n"
			."ON {$prefix}oo_plugin_fieldconfig_form_defaults.defaults_id = "
			." {$prefix}oo_plugin_fieldconfig_form_customs_labels.defaults_id\n"
			."WHERE {$prefix}oo_plugin_fieldconfig_form_defaults.fieldname = '".esc_sql($pField->getName())."' AND\n"
			." {$prefix}oo_plugin_fieldconfig_form_defaults.form_id = ".esc_sql($formId);
		return $query;
	}

}
