<?php

/**
 *
 *    Copyright (C) 2017-2019 onOffice GmbH
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

use onOffice\WPlugin\Gui\AdminPage;
use onOffice\WPlugin\Gui\Table\EstateListTable;
use const ONOFFICE_PLUGIN_DIR;
use function __;
use function add_filter;
use function admin_url;
use function esc_attr;
use function esc_html__;
use function plugins_url;
use function wp_enqueue_script;
use function wp_localize_script;
use function wp_register_script;
use function add_screen_option;

/**
 *
 */

class AdminPageEstateList
	extends AdminPage
{
	/** @var EstateListTable */
	private $_pEstateListTable;


	/**
	 *
	 */

	public function renderContent()
	{
		$this->_pEstateListTable->prepare_items();
		$page = 'onoffice-estates';
		$buttonSearch = __('Search Estate Views', 'onoffice-for-wp-websites');
		$id = 'onoffice-form-search-estate';
		$this->generateSearchForm($page,$buttonSearch,null, null, $id);
		echo '<p>';
		echo '<form method="post">';
		$this->_pEstateListTable->display();
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
			echo ' › '.esc_html__($subTitle, 'onoffice-for-wp-websites');
		}

		echo ' › '.esc_html__('List Views', 'onoffice-for-wp-websites');

		$newLink = admin_url('admin.php?page=onoffice-editlistview');

		echo '</h1>';
		echo '<a href="'.esc_attr($newLink).'" class="page-title-action">'.esc_html__('Add New', 'onoffice-for-wp-websites').'</a>';
		echo '<hr class="wp-header-end">';
	}

	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		$translation = array(
			'confirmdialog' => __('Are you sure you want to delete the selected items?', 'onoffice-for-wp-websites'),
		);

		wp_register_script('onoffice-bulk-actions', plugins_url('/js/onoffice-bulk-actions.js',
			ONOFFICE_PLUGIN_DIR.'/index.php'), array('jquery'));

		wp_localize_script('onoffice-bulk-actions', 'onoffice_table_settings', $translation);
		wp_enqueue_script('onoffice-bulk-actions');
	}


	/**
	 *
	 */

	public function preOutput()
	{
		$screen = get_current_screen();
		if (is_object($screen) && $screen->id === "onoffice_page_onoffice-estates") {
			add_screen_option('per_page', array('option' => 'onoffice_estate_listview_per_page'));
		}

		$this->_pEstateListTable = new EstateListTable();
		add_filter('handle_bulk_actions-table-onoffice_page_onoffice-estates', function(): Table\WP\ListTable {
			return $this->_pEstateListTable;
		}, 10);
		parent::preOutput();
	}
}
