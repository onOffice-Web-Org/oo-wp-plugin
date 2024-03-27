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

namespace onOffice\WPlugin\WP;

use WP_Query;

/**
 *
 */

class WPQueryWrapper
{
	/**
	 *
	 * @param int $pListViewId
	 * @global \WP_Query $wp_query
	 * @return WP_Query
	 *
	 */

	public function getWPQuery(int $pListViewId = null): WP_Query
	{
		global $wp_query, $paged;
		$wpquery = clone $wp_query;

		$pageParameter = 'page_of_id_' . $pListViewId;
		if (isset($_GET[$pageParameter]) && is_numeric($_GET[$pageParameter])) {
			$paged = (int) $_GET[$pageParameter];
		} elseif (get_query_var( 'paged' )) {
			$paged = get_query_var('paged');
		}
		elseif ( get_query_var('page')) {
			$paged = get_query_var( 'page' );
		}
		else {
			$paged = 1;
		}

		$wpquery->set('paged', $paged);

		return $wpquery;
	}
}