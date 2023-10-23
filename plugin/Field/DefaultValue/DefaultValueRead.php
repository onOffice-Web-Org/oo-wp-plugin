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
	 * @param array $pFields
	 * @return array
	 */
	public function readDefaultMultiValuesSingleSelect(int $formId, array $pFields): array
	{
		$pFieldNames = array_map(function ($field) {
			return $field->getName();
		}, $pFields);
		$query = $this->createMultiBaseQuery($formId, $pFieldNames);
		return $this->_pWPDB->get_results($query, OBJECT);
	}

	/**
	 * @param int $formId
	 * @param array $pFieldNames
	 * @return string
	 */
	private function createMultiBaseQuery(int $formId, array $pFieldNames): string
	{
		$names = array_map(function ($v) {
			return "'" . esc_sql($v) . "'";
		}, $pFieldNames);
		$names = implode(',', $names);

		$prefix = $this->_pWPDB->prefix;
		return "SELECT {$prefix}oo_plugin_fieldconfig_form_defaults.defaults_id,"
				. "{$prefix}oo_plugin_fieldconfig_form_defaults_values.locale,\n"
				. "{$prefix}oo_plugin_fieldconfig_form_defaults_values.value,\n"
				. "{$prefix}oo_plugin_fieldconfig_form_defaults.fieldname\n"
				. "FROM {$prefix}oo_plugin_fieldconfig_form_defaults\n"
				. "INNER JOIN {$prefix}oo_plugin_fieldconfig_form_defaults_values\n"
				. "ON {$prefix}oo_plugin_fieldconfig_form_defaults.defaults_id = "
				. " {$prefix}oo_plugin_fieldconfig_form_defaults_values.defaults_id\n"
				. "WHERE {$prefix}oo_plugin_fieldconfig_form_defaults.fieldname IN (" . $names . ") AND\n"
				. " {$prefix}oo_plugin_fieldconfig_form_defaults.form_id = " . esc_sql($formId);
	}
}
