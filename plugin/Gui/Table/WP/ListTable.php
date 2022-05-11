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

namespace onOffice\WPlugin\Gui\Table\WP;

use WP_List_Table;
use const ABSPATH;
use function __;
use function _e;

if (!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class ListTable extends WP_List_Table
{
	/**
	 *
	 * $columns = array(
	 *		'link_name' => __('Link Name', 'onoffice-for-wp-websites'),
	 *		'link_category' => __('Link Category', 'onoffice-for-wp-websites'),
	 *		'cb' => '<input type="checkbox" />',
	 *	);
	 *
	 *	$hidden = array('ID');
	 *	$sortable = array();
	 *
	 *	$this->_column_headers = array($columns, $hidden, $sortable);
	 *	$this->items = array(
	 *		array('link_name' => 'test', 'link_category' => 'check', 'ID' => 4),
	 *		array('link_name' => 'test', 'link_category' => 'check', 'ID' => 5),
	 *		array('link_name' => 'test', 'link_category' => 'check', 'ID' => 6),
	 *	);
	 *
	 *	$this->set_pagination_args( array(
	 *		'total_items' => 3,
	 *		'per_page'    => 3,
	 *		'total_pages' => 1
	 *	) );
	 *
	 */

	public function prepare_items()
	{
		return parent::prepare_items();
	}


	/**
	 *
	 */

	public function no_items()
	{
		_e( 'No items found.' );
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function get_bulk_actions()
	{
		$actions = array();
		$actions['bulk_delete'] = __( 'Delete' );

		return $actions;
	}


	/**
	 *
	 * @param object $pItem
	 * @return string
	 *
	 */

	protected function column_cb($pItem)
	{
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$pItem->ID);
	}


	/**
	 *
	 * @param object $pItem
	 * @param string $columnName
	 * @return string
	 *
	 */

	protected function column_default($pItem, $columnName)
	{
		return $pItem->{$columnName};
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getArgs(): array
	{
		return $this->_args;
	}


	/** @var array $items */
	protected function setItems(array $items)
		{ $this->items = $items; }

	/** @return array */
	protected function getItems()
		{ return $this->items; }

	protected function handleRecord(array $records)
	{
		if (empty($records))
		{
			return [];
		}
		$recordHandled = [];
		foreach ($records as $record)
		{
			if (!empty($record->page_shortcode))
			{
				$listPageID = explode(',',$record->page_shortcode);
				$pages = '';
				$listPage = [];
				foreach ($listPageID as $pageID)
				{
					$listPage[] = "<a href='".esc_attr(get_edit_post_link((int)$pageID))."' target='_blank'>".esc_html(get_the_title((int)$pageID))."</a>";
				}
				$pages = implode(',',$listPage);
				$record->page_shortcode = $pages;
			}
			$recordHandled[] = $record;
		}
		return $recordHandled;
	}

}
