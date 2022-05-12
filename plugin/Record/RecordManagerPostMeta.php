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
	
	
	/** @var string */
	private $_shortCodePageDetail;
	
	/**
	 *
	 * @param wpdb $pWPDB
	 *
	 */

	public function __construct(wpdb $pWPDB)
	{
		$this->_pWPDB = $pWPDB;
		$pDIContainerBuilder = new ContainerBuilder;
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		$pDetailView = $pContainer->get(DataDetailView::class);
		$this->_shortCodePageDetail = '[oo_estate view="' . $pDetailView->getName() . '"]';
	}
	/**
	*
	* @return array
	*
	*/

	public function getPageId(): array
	{
		$prefix = $this->_pWPDB->prefix;
		
		$post_meta_sql = "SELECT postmeta.post_id
				FROM {$prefix}postmeta postmeta
				INNER JOIN {$prefix}posts post on postmeta.post_id = post.ID
				WHERE postmeta.meta_key not like '\_%'
					and postmeta.meta_value like '%" . $this->_shortCodePageDetail . "%'
					and post.post_type = 'page'
					and post.post_status IN ('publish', 'draft')
				ORDER BY postmeta.post_id DESC ";
		$post_meta_results = $this->_pWPDB->get_row( $post_meta_sql ,ARRAY_A);
		
		return empty($post_meta_results) ? [] : $post_meta_results;
	}
	
	
	/**
	 * @return string
	 */
	public function getShortCodePageDetail()
	{
		return $this->_shortCodePageDetail;
	}
	
	/**
	 * @return wpdb
	 */
	public function getWPDB()
	{
		return $this->_pWPDB;
	}
}