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

use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\DataView\DataDetailView;
use const ARRAY_A;
use wpdb;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class RecordManagerPostMeta
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
	* @return array
	*
	*/

	public function getPageId(): array
	{
		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$Container = $pDIContainerBuilder->build();
		$pDetailView = $Container->get(DataDetailView::class);
		$shortCode = '[oo_estate view="' . $pDetailView->getName() . '"]';

		$prefix = $this->_pWPDB->prefix;
		$post_meta_sql="SELECT `post_id`
				FROM {$prefix}postmeta postmeta
				INNER JOIN {$prefix}posts post on postmeta.post_id = post.ID
				WHERE postmeta.meta_key not like '\_%'
					and postmeta.meta_value like '%" . $shortCode . "%'
					and post.post_type = 'page'
					and post.post_status IN ('publish', 'draft')
				ORDER BY postmeta.post_id DESC ";
		$post_meta_results = $this->_pWPDB->get_row( $post_meta_sql ,ARRAY_A);

		return empty($post_meta_results) ? [] : $post_meta_results;
    }
	
	public function deletePostMataUseCustomField(string $metaKey)
	{
		$prefix = $this->_pWPDB->prefix;
		$tablePostMeta = $prefix . "postmeta";
		$this->_pWPDB->delete($tablePostMeta, array('meta_key' => $metaKey));
	}
}