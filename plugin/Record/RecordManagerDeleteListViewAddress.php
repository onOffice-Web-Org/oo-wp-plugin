<?php

/**
 *
 *    Copyright (C) 2018-2019 onOffice GmbH
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

use wpdb;


/**
 *
 */

class RecordManagerDeleteListViewAddress
	extends RecordManager
	implements RecordManagerDelete
{
	/** @var wpdb */
	private $_pWPDB;


	/**
	 *
	 * @param wpdb $pWPDB
	 *
	 */

	public function __construct(wpdb $pWPDB)
	{
		$this->_pWPDB = $pWPDB;
	}


	/**
	 *
	 * @param array $ids
	 *
	 */

	public function deleteByIds(array $ids)
	{
		$prefix = $this->_pWPDB->prefix;
		$pWpdb = $this->_pWPDB;

		foreach ($ids as $id) {
			$pWpdb->delete($prefix.'oo_plugin_listviews_address', ['listview_address_id' => $id]);
			$pWpdb->delete($prefix.'oo_plugin_address_fieldconfig', ['listview_address_id' => $id]);
		}
	}
}
