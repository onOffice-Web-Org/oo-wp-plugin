<?php
/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

/**
 *
 */

class Helper
{
	/** ordPress database abstraction object.*/
	private $_wpdb = null;


	/**
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 */
	public function __construct() {

		global $wpdb;

		$this->_wpdb = $wpdb;
	}

	/**
	 *
	 * @param string $pagename
	 * @return int
	 *
	 */

	public function get_pageId_by_title( $pagename ){

		$pageId = $this->_wpdb->get_var($this->_wpdb->prepare("SELECT ID FROM ".$this->_wpdb->posts." WHERE post_name = '%s'", $pagename));

		return $pageId;
	}
}

?>