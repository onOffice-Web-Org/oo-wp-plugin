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
	 * @global \wpdb $wpdb
	 * @return \wpdb
	 *
	 */

	public function getWpdb()
	{
		global $wpdb;
		return $wpdb;
	}
}
