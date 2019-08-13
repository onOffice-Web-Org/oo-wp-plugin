<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

namespace onOffice\WPlugin\Gui\Table;

use onOffice\WPlugin\Gui\AdminPageEstateListSettingsBase;
use onOffice\WPlugin\Gui\Table\WP\ListTable;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class AddressListTable
	extends ListTable
{
	/** @var int */
	private $_itemsPerPage = null;


	/**
	 *
	 * @param array $args
	 *
	 */

	public function __construct($args = array())
	{
		parent::__construct(array(
			'singular' => 'listpage',
			'plural' => 'listpages',
			'screen' => isset($args['screen']) ? $args['screen'] : null,
		));

		$this->_itemsPerPage = $this->get_items_per_page('onoffice-address-listview_per_page', 10);
	}


	/**
	 *
	 */

	private function fillData()
	{
		$page = $this->get_pagenum() - 1;
		$itemsPerPage = $this->_itemsPerPage;
		$offset = $page * $itemsPerPage;

		$pRecordRead = new RecordManagerReadListViewAddress();
		$pRecordRead->setLimit($itemsPerPage);
		$pRecordRead->setOffset($offset);
		$pRecordRead->addColumn('listview_address_id', 'ID');
		$pRecordRead->addColumn('name');
		$pRecordRead->addColumn('name', 'shortcode');

		$this->setItems($pRecordRead->getRecords());
		$itemsCount = $pRecordRead->getCountOverall();

		$this->set_pagination_args(array(
			'total_items' => $itemsCount,
			'per_page'    => $this->_itemsPerPage,
			'total_pages' => ceil($itemsCount / 10)
		));
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function get_columns()
	{
		return array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name of View', 'onoffice'),
			'shortcode' => __('Shortcode', 'onoffice'),
		);
	}


	/**
	 *
	 * @param object $pItem
	 * @return string
	 *
	 */

	protected function column_shortcode($pItem)
	{
		return '<input type="text" readonly value="[oo_address view=&quot;'.esc_html($pItem->name).'&quot;]">';
	}


	/**
	 *
	 * Get the name of the default primary column.
	 *
	 * @since 4.3.0
	 *
	 * @return string Name of the default primary column
	 *
	 */

	protected function get_default_primary_column_name()
	{
		return 'name';
	}


	/**
	 *
	 */

	public function prepare_items()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name of View', 'onoffice'),
			'shortcode' => __('Shortcode', 'onoffice'),
		);

		$hidden = array('ID');
		$sortable = array();

		$this->_column_headers = array($columns, $hidden, $sortable,
			$this->get_default_primary_column_name());

		$this->fillData();
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

		$viewidParam = AdminPageEstateListSettingsBase::GET_PARAM_VIEWID;
		$editLink = admin_url('admin.php?page=onoffice-editlistviewaddress&'.$viewidParam.'='.$pItem->ID);

		$actionFile = plugin_dir_url(ONOFFICE_PLUGIN_DIR).
			plugin_basename(ONOFFICE_PLUGIN_DIR).'/tools/listview.php';

		$actions = array();
		$actions['edit'] = '<a href="'.$editLink.'">'.esc_html__('Edit').'</a>';
		$actions['delete'] = "<a class='submitdelete' href='"
			.wp_nonce_url($actionFile.'?action=delete&list_id='.$pItem->ID.'&type='
				.RecordManagerFactory::TYPE_ADDRESS, 'delete-listview_'.$pItem->ID)
			."' onclick=\"if ( confirm( '"
			.esc_js(sprintf(
			/* translators: %s is the name of the list view. */
			__("You are about to delete the listview '%s'\n  'Cancel' to stop, 'OK' to delete.", 'onoffice'), $pItem->name))
			."' ) ) { return true; }return false;\">".__('Delete')."</a>";
		return $this->row_actions( $actions );
	}
}
