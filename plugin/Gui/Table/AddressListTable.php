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

use onOffice\WPlugin\Gui\Table\WP\ListTable;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use onOffice\WPlugin\Controller\Exception\UnknownFilterException;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\FilterCall;
use onOffice\SDK\onOfficeSDK;
use function __;
use function admin_url;
use function esc_html;
use function esc_html__;
use function esc_js;
use function wp_nonce_url;

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

	/** @var FilterCall */
	private $_pFilterCall = null;

	/**
	 *
	 * @param array $args
	 *
	 */

	public function __construct($args = [])
	{
		parent::__construct([
			'singular' => 'addresslist',
			'plural' => 'addresslists',
			'screen' => $args['screen'] ?? null,
		]);

		$this->_itemsPerPage = $this->get_items_per_page('onoffice-address-listview_per_page', 10);
		$this->_pFilterCall = new FilterCall(onOfficeSDK::MODULE_ADDRESS);
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
		$pRecordRead->addColumn('filterId');
		$pRecordRead->addColumn('template');
		$pRecordRead->addColumn('name', 'shortcode');
		$pRecordRead->addColumn('page_shortcode');

		$pRecord = $pRecordRead->getRecordsSortedAlphabetically();
		$pRecord = $this->handleRecord($pRecord);
		$this->setItems($pRecord);
		$itemsCount = $pRecordRead->getCountOverall();

		$this->set_pagination_args([
			'total_items' => $itemsCount,
			'per_page'    => $this->_itemsPerPage,
			'total_pages' => ceil($itemsCount / 10)
		]);
	}
	/**
	 *
	 * @param object $pItem
	 * @return string
	 *
	 */

	protected function column_filtername($pItem)
	{
		$filterName = '';
		try {
			if ($pItem->filterId != 0) {
				$filterName = $this->_pFilterCall->getFilternameById($pItem->filterId);
			}
		} catch (APIClientCredentialsException $pCredentialsException) {
			$filterName = __('(Needs valid API credentials)', 'onoffice-for-wp-websites');
		} catch (UnknownFilterException $pFilterException) {
			/* translators: %s will be replaced with a number. */
			$filterName = sprintf(__('(Unknown Filter (ID: %s))', 'onoffice-for-wp-websites'),
				$pFilterException->getFilterId());
		}
		return $filterName;
	}
	/**
	 *
	 * @return array
	 *
	 */

	public function get_columns()
	{
		return [
			'cb' => '<input type="checkbox" />',
			'name' => __('Name of View', 'onoffice-for-wp-websites'),
			'filtername' => __('Filter', 'onoffice-for-wp-websites'),
			'shortcode' => __('Shortcode', 'onoffice-for-wp-websites'),
		];
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
		$columns = [
			'cb' => '<input type="checkbox" />',
			'name' => __('List name', 'onoffice-for-wp-websites'),
			'filtername' => __('Selected filter', 'onoffice-for-wp-websites'),
			'template' => __('Template', 'onoffice-for-wp-websites'),
			'shortcode' => __('Shortcode', 'onoffice-for-wp-websites'),
			'page_shortcode' => __('Pages using the shortcode', 'onoffice-for-wp-websites'),
		];

		$hidden = ['ID','filterId'];
		$sortable = [];

		$this->_column_headers = [$columns, $hidden, $sortable,
			$this->get_default_primary_column_name()];

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
		if ( $primary !== $column_name ) {
			return '';
		}

		$editLink = admin_url('admin.php?page=onoffice-editlistviewaddress&id='.$pItem->ID);

		$actions = [];
		$actions['edit'] = '<a href="'.esc_attr($editLink).'">'.esc_html__('Edit').'</a>';
		$actions['duplicate'] = "<a class='button-duplicate' href='"
			. esc_attr(wp_nonce_url(admin_url('admin.php') . '?page=onoffice-addresses&action=bulk_duplicate&listViewId=' . $pItem->name,
				'bulk-addresslists'))
			. "'>" . esc_html__('Duplicate', 'onoffice-for-wp-websites') . "</a>";
		$actions['delete'] = "<a class='submitdelete' href='"
			.wp_nonce_url(admin_url('admin.php').'?page=onoffice-addresses&action=bulk_delete&addresslist[]='.$pItem->ID, 'bulk-addresslists')
			."' onclick=\"if ( confirm( '"
			.esc_js(sprintf(
			/* translators: %s is the name of the list view. */
			__("You are about to delete the listview '%s'\n  'Cancel' to stop, 'OK' to delete.", 'onoffice-for-wp-websites'), $pItem->name))
			."' ) ) { return true; }return false;\">".__('Delete')."</a>";
		return $this->row_actions( $actions );
	}
}
