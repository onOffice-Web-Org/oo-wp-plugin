<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

use wpdb;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class RecordManager
{
	/** */
	const TABLENAME_LIST_VIEW = 'oo_plugin_listviews';

	/** */
	const TABLENAME_PICTURETYPES = 'oo_plugin_picturetypes';

	/** */
	const TABLENAME_SORTBYUSERVALUES = 'oo_plugin_sortbyuservalues';

	/** */
	const TABLENAME_FIELDCONFIG = 'oo_plugin_fieldconfig';

	/** */
	const TABLENAME_LISTVIEW_CONTACTPERSON = 'oo_plugin_listview_contactperson';

	/** */
	const TABLENAME_FORMS = 'oo_plugin_forms';

	/** */
	const TABLENAME_FIELDCONFIG_FORMS = 'oo_plugin_form_fieldconfig';

	/** */
	const TABLENAME_FIELDCONFIG_FORM_DEFAULTS = 'oo_plugin_fieldconfig_form_defaults';

	/** */
	const TABLENAME_FIELDCONFIG_FORM_DEFAULTS_VALUES = 'oo_plugin_fieldconfig_form_defaults_values';

	/** */
	const TABLENAME_FIELDCONFIG_FORM_CUSTOMS_LABELS = 'oo_plugin_fieldconfig_form_customs_labels';

	/** */
	const TABLENAME_FIELDCONFIG_FORM_TRANSLATED_LABELS = 'oo_plugin_fieldconfig_form_translated_labels';

	/** */
	const TABLENAME_LIST_VIEW_ADDRESS = 'oo_plugin_listviews_address';

	/** */
	const TABLENAME_FIELDCONFIG_ADDRESS = 'oo_plugin_address_fieldconfig';

	/** */
	const TABLENAME_FIELDCONFIG_ESTATE_CUSTOMS_LABELS = 'oo_plugin_fieldconfig_estate_customs_labels';

	/** */
	const TABLENAME_FIELDCONFIG_ESTATE_TRANSLATED_LABELS = 'oo_plugin_fieldconfig_estate_translated_labels';

	/** */
	const TABLENAME_FIELDCONFIG_ESTATE_DEFAULTS = 'oo_plugin_fieldconfig_estate_defaults';

	/** */
	const TABLENAME_FIELDCONFIG_ESTATE_DEFAULTS_VALUES = 'oo_plugin_fieldconfig_estate_defaults_values';
	/**
	 *
	 * @deprecated get wpdb via DI
	 *
	 * @return string
	 *
	 */

	public function getTablePrefix()
	{
		return $this->getWpdb()->prefix;
	}


	/**
	 *
	 * @deprecated get wpdb via DI
	 *
	 * @global wpdb $wpdb
	 * @return wpdb
	 *
	 */

	public function getWpdb()
	{
		global $wpdb;
		return $wpdb;
	}


	/**
	 *
	 * @param string $value
	 * @param string $table
	 * @param string $field
	 * @return string
	 *
	 */

	static public function postProcessValue($value, $table, $field)
	{
		$result = $value;

		if (null === $value && !RecordStructure::isNullAllowed($table, $field)) {
			$result = RecordStructure::getEmptyValue($table, $field);
		}

		return $result;
	}
}
