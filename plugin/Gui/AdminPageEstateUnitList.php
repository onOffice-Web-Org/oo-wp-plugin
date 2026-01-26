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

if ( ! defined( 'ABSPATH' ) ) exit;

use onOffice\WPlugin\Gui\Table\EstateUnitsTable;
use onOffice\WPlugin\Utility\FileVersionHelper;
use WP_List_Table;
use function add_filter;
use function admin_url;
use function esc_html__;
use function add_screen_option;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateUnitList
	extends AdminPage
{
	/** @var EstateUnitsTable */
	private $_pEstateUnitsTable = null;

	/**
	 *
	 */

	public function renderContent()
	{
		$this->_pEstateUnitsTable->prepare_items();
		$page = 'onoffice-estates';
		$buttonSearch = __('Search Estate Views', 'onoffice-for-wp-websites');
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET parameter for tab display filtering, no form processing
		$tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$id = 'onoffice-form-search-estate';
		$this->generateSearchForm($page,$buttonSearch,null,$tab,$id);
		echo '<p>';
		echo '<form method="post">';
		$this->_pEstateUnitsTable->display();
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

		if ($subTitle != '')
		{
			echo ' › ' . esc_html( $subTitle );
		}

		echo ' › '.esc_html__('Units Lists', 'onoffice-for-wp-websites');

		$new_link = admin_url('admin.php?page=onoffice-editunitlist');

		echo '</h1>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $new_link is escaped by admin_url()
		echo '<a href="'.esc_url($new_link).'" class="page-title-action">'.esc_html__('Add New', 'onoffice-for-wp-websites').'</a>';
		echo '<hr class="wp-header-end">';
	}


	/**
	 *
	 */

	public function preOutput()
	{
		$screen = get_current_screen();
		if ( ! is_object( $screen ) || $screen->id !== "onoffice_page_onoffice-estates" ) {
			return;
		}

		add_screen_option('per_page', array('option' => 'onoffice_estate_units_listview_per_page'));
		$this->_pEstateUnitsTable = new EstateUnitsTable();
		add_filter('handle_bulk_actions-table-onoffice_page_onoffice-estates', function(): WP_List_Table {
			return $this->_pEstateUnitsTable;
		}, 10);
		// callback can be same as in estate list view,
		// since it's the same screen and kind of records
		parent::preOutput();
	}

	public function doExtraEnqueues()
	{
		wp_register_script('oo-copy-shortcode',
			plugin_dir_url(ONOFFICE_PLUGIN_DIR . '/index.php') . '/dist/onoffice-copycode.min.js',
			['jquery'], 
			FileVersionHelper::getFileVersion(ONOFFICE_PLUGIN_DIR . '/dist/onoffice-copycode.min.js'), 
			true);
		wp_enqueue_script( 'oo-copy-shortcode' );
	}
}
