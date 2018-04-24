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
	const TABLENAME_FIELDCONFIG = 'oo_plugin_fieldconfig';

	/** */
	const TABLENAME_LISTVIEW_CONTACTPERSON = 'oo_plugin_listview_contactperson';

	/** */
	const TABLENAME_FORMS = 'oo_plugin_forms';

	/** */
	const TABLENAME_FIELDCONFIG_FORMS = 'oo_plugin_form_fieldconfig';

	/** */
	const TABLENAME_LIST_VIEW_ADDRESS = 'oo_plugin_listviews_address';

	/** */
	const TABLENAME_FIELDCONFIG_ADDRESS = 'oo_plugin_address_fieldconfig';


	/**
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

		if (null == $value) {
			if (!RecordStructure::isNullAllowed($table, $field)) {
				$result = RecordStructure::getEmptyValue($table, $field);
			}
		}

		return $result;
	}
}
