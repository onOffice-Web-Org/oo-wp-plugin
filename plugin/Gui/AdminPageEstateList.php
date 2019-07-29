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

use onOffice\WPlugin\Gui\AdminPage;
use onOffice\WPlugin\Gui\Table\EstateListTable;
use onOffice\WPlugin\Record\RecordManagerFactory;
use const ONOFFICE_PLUGIN_DIR;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateList
	extends AdminPage
{
	/**
	 *
	 */

	public function renderContent()
	{
		$actionFile = plugin_dir_url(ONOFFICE_PLUGIN_DIR).
			plugin_basename(ONOFFICE_PLUGIN_DIR).'/tools/listview.php?type='.RecordManagerFactory::TYPE_ESTATE;

		$pTable = new EstateListTable();
		$pTable->prepare_items();
		echo '<p>';
		echo '<form method="post" action="'.esc_html($actionFile).'">';
		$pTable->display();
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
		echo '<h1 class="wp-heading-inline">'.esc_html__('onOffice', 'onoffice');

		if ($subTitle != '')
		{
			echo ' › '.esc_html__($subTitle, 'onoffice');
		}

		echo ' › '.esc_html__('List Views', 'onoffice');

		$new_link = admin_url('admin.php?page=onoffice-editlistview');

		echo '</h1>';
		echo '<a href="'.$new_link.'" class="page-title-action">'.esc_html__('Add New', 'onoffice').'</a>';
		echo '<hr class="wp-header-end">';
	}


	/**
	 *
	 */

	public function handleAdminNotices()
	{
		$itemsDeleted = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_NUMBER_INT);

		if ($itemsDeleted !== null && $itemsDeleted !== false) {
			add_action('admin_notices', function() use ($itemsDeleted) {
				$pHandler = new AdminNoticeHandlerListViewDeletion();
				echo $pHandler->handleListView($itemsDeleted);
			});
		}
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		$translation = array(
			'confirmdialog' => __('Are you sure you want to delete the selected items?', 'onoffice'),
		);

		wp_register_script('onoffice-bulk-actions', plugins_url('/js/onoffice-bulk-actions.js',
			ONOFFICE_PLUGIN_DIR.'/index.php'), array('jquery'));

		wp_localize_script('onoffice-bulk-actions', 'onoffice_table_settings', $translation);
		wp_enqueue_script('onoffice-bulk-actions');
	}
}
