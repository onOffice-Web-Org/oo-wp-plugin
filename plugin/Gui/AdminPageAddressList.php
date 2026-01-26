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

namespace onOffice\WPlugin\Gui;

if ( ! defined( 'ABSPATH' ) ) exit;

use onOffice\WPlugin\Gui\Table\AddressListTable;
use onOffice\WPlugin\Utility\FileVersionHelper;
use function __;
use function add_filter;
use function admin_url;
use function esc_html__;
use function add_screen_option;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class AdminPageAddressList
	extends AdminPage
{
	/** @var AddressListTable */
	private $_pAddressListTable = null;


	/**
	 *
	 */

	public function renderContent()
	{
		$this->_pAddressListTable->prepare_items();
		$page = 'onoffice-addresses';
		$buttonSearch = __('Search Addresses', 'onoffice-for-wp-websites');
		$id = 'onoffice-form-search-address';
		$this->generateSearchForm($page,$buttonSearch, null,null,$id);
		echo '<p>';
		echo '<form method="post">';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- views() returns escaped HTML
		echo $this->_pAddressListTable->views();
		$this->_pAddressListTable->display();
		echo '</form>';
		echo '</p>';
	}


	/**
	 *
	 * @param string $subTitle
	 *
	 */

	public function generatePageMainTitle($subTitle)
	{
		echo '<h1 class="wp-heading-inline">'.esc_html__('onOffice', 'onoffice-for-wp-websites');

		if ($subTitle != '') {
			echo ' › ' .  esc_html( $subTitle );
		}

		echo ' › '.esc_html__('List Views', 'onoffice-for-wp-websites');

		$newLink = admin_url('admin.php?page=onoffice-editlistviewaddress');

		echo '</h1>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $newLink is escaped by admin_url()
		echo '<a href="'.esc_url($newLink).'" class="page-title-action">'.esc_html__('Add New', 'onoffice-for-wp-websites').'</a>';
		echo '<hr class="wp-header-end">';
	}

	/**
	 *
	 */

	public function preOutput()
	{
		$screen = get_current_screen();
		if ( ! is_object( $screen ) || $screen->id !== "onoffice_page_onoffice-addresses" ) {
			return;
		}

		add_screen_option( 'per_page', array('option' => 'onoffice_address_listview_per_page') );
		$this->_pAddressListTable = new AddressListTable();

		add_filter('handle_bulk_actions-table-onoffice_page_onoffice-addresses', function(): Table\WP\ListTable {
			return $this->_pAddressListTable;
		});

		parent::preOutput();
	}

	public function doExtraEnqueues()
	{
		$translation = array(
			'confirmdialog' => __('Are you sure you want to delete the selected items?', 'onoffice-for-wp-websites'),
		);

		wp_register_script('onoffice-bulk-actions', 
			plugins_url('/dist/onoffice-bulk-actions.min.js', ONOFFICE_PLUGIN_DIR.'/index.php'), 
			array('jquery'),
			FileVersionHelper::getFileVersion(ONOFFICE_PLUGIN_DIR . '/dist/onoffice-bulk-actions.min.js'),
			true);

		wp_localize_script('onoffice-bulk-actions', 'onoffice_table_settings', $translation);
		wp_enqueue_script('onoffice-bulk-actions');

		wp_register_script('oo-copy-shortcode',
			plugin_dir_url(ONOFFICE_PLUGIN_DIR . '/index.php') . '/dist/onoffice-copycode.min.js',
			['jquery'], 
			FileVersionHelper::getFileVersion(ONOFFICE_PLUGIN_DIR . '/dist/onoffice-copycode.min.js'), 
			true);
		wp_enqueue_script( 'oo-copy-shortcode');
	}
}
