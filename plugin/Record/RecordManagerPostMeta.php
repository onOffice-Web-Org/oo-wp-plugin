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

use const ARRAY_A;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerPostMeta
{
	/**
	*
	* @return array
	*
	*/

	public function getPageId(): array
	{
		global $wpdb;
		$prefix = $wpdb->prefix;
		$post_meta_sql="SELECT `post_id`
				FROM {$prefix}postmeta postmeta
				INNER JOIN {$prefix}posts post on postmeta.post_id = post.ID
				WHERE postmeta.meta_key not like '\_%' and postmeta.meta_value like '%[oo_estate%' and post.post_type = 'page'
				ORDER BY postmeta.post_id DESC ";
		$post_meta_results = $wpdb->get_row( $post_meta_sql ,ARRAY_A);
		if (empty($post_meta_results))
		{
			return [];
		}
		return $post_meta_results;
    }
}