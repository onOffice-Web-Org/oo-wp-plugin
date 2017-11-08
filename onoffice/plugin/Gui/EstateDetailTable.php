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

namespace onOffice\WPlugin\Gui;

use onOffice\WPlugin\wp_dependent\ListTable;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class EstateDetailTable extends ListTable
{
	/**
	 *
	 */

	public function prepare_items()
	{
		$columns = array(
			'name' => __('Name of View', 'onoffice'),
			'list_type' => __('Type of List', 'onoffice'),
			'shortcode' => __('Shortcode', 'onoffice'),
		);

		$sortable = array();

		$this->_column_headers = array($columns, array(), $sortable,
			$this->get_default_primary_column_name());

		$pItem = new \stdClass();
		$pItem->name = __('Detail VIew', 'onoffice');
		$pItem->list_type = __('Detail View', 'onoffice');
		$pItem->shortcode = '<input type="text" readonly value="[oo_estate view=&quot;Detail&quot;]">';

		$this->setItems(array($pItem));
		$this->fillData();
	}


	/**
	 *
	 */

	private function fillData()
	{
		$this->set_pagination_args( array(
			'total_items' => 1,
			'per_page' => 1,
			'total_pages' => 1,
		) );
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function get_columns()
	{
		return array(
			'name' => __('Name of View', 'onoffice'),
			'list_type' => __('Type of List', 'onoffice'),
			'shortcode' => __('Shortcode', 'onoffice'),
		);
	}


	/**
	 *
	 * @param object $pItem
	 * @param string $columnName
	 * @return string
	 *
	 */

	protected function column_default($pItem, $columnName) {
		$result = null;
		if (property_exists($pItem, $columnName)) {
			$result = $pItem->{$columnName};
		}
		return $result;
	}


	/**
	 * Generates and displays row action links.
	 *
	 * @param object $pItem Link being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary Primary column name.
	 * @return string Row action output for links.
	 *
	 */

	protected function handle_row_actions($pItem, $column_name, $primary)
	{
		if ( $primary !== $column_name )
		{
			return '';
		}

		$edit_link = admin_url('admin.php?page=onoffice-editlistview&listViewId=0');
		$actions = array();
		$actions['edit'] = '<a href="'.$edit_link.'">'.esc_html__('Edit').'</a>';
		return $this->row_actions( $actions );
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function get_bulk_actions() {
		return array();
	}
}
