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
use onOffice\WPlugin\Record\RecordManagerReadListView;
use onOffice\WPlugin\Form\FormModelBuilderEstateListSettings;
use onOffice\WPlugin\FilterCall;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class EstateListTable extends ListTable
{
	/** @var int */
	private $_itemsPerPage = null;

	/** @var FilterCall */
	private $_pFilterCall = null;

	/**
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @param array $args An associative array of arguments.
	 *
	 */

	public function __construct($args = array())
	{
		parent::__construct(array(
			'singular' => 'listpage',
			'plural' => 'listpages',
			'screen' => isset($args['screen']) ? $args['screen'] : null,
		));

		$this->_itemsPerPage = $this->get_items_per_page('onoffice-estate-listview_per_page', 10);
		$this->_pFilterCall = new FilterCall(\onOffice\SDK\onOfficeSDK::MODULE_ESTATE);
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function ajax_user_can()
	{
		return current_user_can( 'edit_pages' );
	}


	/**
	 *
	 */

	private function fillData()
	{
		$page = $this->get_pagenum() - 1;
		$itemsPerPage = $this->_itemsPerPage;
		$offset = $page * $itemsPerPage;

		$pRecordRead = new RecordManagerReadListView();
		$pRecordRead->setLimit($itemsPerPage);
		$pRecordRead->setOffset($offset);
		$pRecordRead->addColumn('listview_id', 'ID');
		$pRecordRead->addColumn('name');
		$pRecordRead->addColumn('filterId');
		$pRecordRead->addColumn('show_status');
		$pRecordRead->addColumn('list_type');
		$pRecordRead->addColumn('name', 'shortcode');
		$pRecordRead->addWhere("`list_type` IN('default', 'reference', 'favorites')");

		$this->setItems($pRecordRead->getRecords());
		$itemsCount = $pRecordRead->getCountOverall();

		$this->set_pagination_args( array(
			'total_items' => $itemsCount,
			'per_page'    => $this->_itemsPerPage,
			'total_pages' => ceil($itemsCount / 10)
		) );
	}


	/**
	 *
	 */

	public function prepare_items()
	{
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'name' => __('Name of View', 'onoffice'),
			'filtername' => __('Filter', 'onoffice'),
			'show_status' => __('Show Status', 'onoffice'),
			'list_type' => __('Type of List', 'onoffice'),
			'shortcode' => __('Shortcode', 'onoffice'),
		);

		$hidden = array('ID', 'filterId');
		$sortable = array();

		$this->_column_headers = array($columns, $hidden, $sortable,
			$this->get_default_primary_column_name());

		$this->fillData();
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
	 * @param object $pItem
	 * @return string
	 *
	 */

	protected function column_list_type($pItem)
	{
		$listTypes = FormModelBuilderEstateListSettings::getListViewLabels();
		$selectedTypeLabel = null;
		$selectedType = $pItem->list_type;

		if (array_key_exists($selectedType, $listTypes))
		{
			$selectedTypeLabel = $listTypes[$selectedType];
		}

		return $selectedTypeLabel;
	}


	/**
	 *
	 * @param object $pItem
	 * @return string
	 *
	 */

	protected function column_show_status($pItem)
	{
		return $pItem->show_status == '1' ? __('Yes', 'onoffice') : __('No', 'onoffice');
	}


	/**
	 *
	 * @param object $pItem
	 * @return string
	 *
	 */

	protected function column_filtername($pItem)
	{
		return $this->_pFilterCall->getFilternameById($pItem->filterId);
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
			'filtername' => __('Filter', 'onoffice'),
			'show_status' => __('Show Status', 'onoffice'),
			'list_type' => __('Type of List', 'onoffice'),
			'shortcode' => __('Shortcode', 'onoffice'),
		);
	}


	/**
	 *
	 * @param string $pItem
	 * @return string
	 *
	 */

	protected function column_shortcode($pItem)
	{
		return '<input type="text" readonly value="[oo_estate view=&quot;'.esc_html($pItem->name).'&quot;]">';
	}


	/**
	 * Get the name of the default primary column.
	 *
	 * @since 4.3.0
	 *
	 * @return string Name of the default primary column
	 */

	protected function get_default_primary_column_name()
	{
		return 'name';
	}


	/**
	 *
	 * @return array
	 */

	protected function get_bulk_actions() {
		$actions = array();
		$actions['bulk_delete'] = __( 'Delete' );

		return $actions;
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
		$editLink = admin_url('admin.php?page=onoffice-editlistview&'.$viewidParam.'='.$pItem->ID);

		$actionFile = plugin_dir_url(ONOFFICE_PLUGIN_DIR).
			plugin_basename(ONOFFICE_PLUGIN_DIR).'/tools/listview.php';

		$actions = array();
		$actions['edit'] = '<a href="'.$editLink.'">'.esc_html__('Edit').'</a>';
		$actions['delete'] = "<a class='submitdelete' href='"
			.wp_nonce_url($actionFile.'?action=delete&list_id='.$pItem->ID, 'delete-listview_'.$pItem->ID)
			."' onclick=\"if ( confirm( '"
			.esc_js(sprintf(__(
			"You are about to delete this listview '%s'\n  'Cancel' to stop, 'OK' to delete."), $pItem->name))
			."' ) ) { return true;}return false;\">" . __('Delete') . "</a>";
		return $this->row_actions( $actions );
	}
}
