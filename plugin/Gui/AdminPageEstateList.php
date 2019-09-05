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

use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\Form\BulkDeleteRecord;
use onOffice\WPlugin\Gui\AdminPage;
use onOffice\WPlugin\Gui\Table\EstateListTable;
use onOffice\WPlugin\Record\RecordManagerDeleteListViewEstate;
use WP_List_Table;
use const ONOFFICE_DI_CONFIG_PATH;
use const ONOFFICE_PLUGIN_DIR;
use function __;
use function add_action;
use function add_filter;
use function add_query_arg;
use function admin_url;
use function check_admin_referer;
use function esc_attr;
use function esc_html__;
use function plugins_url;
use function wp_enqueue_script;
use function wp_localize_script;
use function wp_register_script;

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
		echo '<h1 class="wp-heading-inline">'.esc_html__('onOffice', 'onoffice');

		if ($subTitle != '')
		{
			echo ' › '.esc_html__($subTitle, 'onoffice');
		}

		echo ' › '.esc_html__('List Views', 'onoffice');

		$newLink = admin_url('admin.php?page=onoffice-editlistview');

		echo '</h1>';
		echo '<a href="'.esc_attr($newLink).'" class="page-title-action">'.esc_html__('Add New', 'onoffice').'</a>';
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


	/**
	 *
	 */

	public function preOutput()
	{
		$this->_pEstateListTable = new EstateListTable();
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pDI = $pContainerBuilder->build();

		$pClosureDeleteEstate = function(string $redirectTo, string $doaction, array $estateIds)
			use ($pDI): string
		{
			/* @var $pBulkDeleteRecord BulkDeleteRecord */
			$pBulkDeleteRecord = $pDI->get(BulkDeleteRecord::class);
			/* @var $pRecordManagerDelete RecordManagerDeleteListViewEstate */
			$pRecordManagerDelete = $pDI->get(RecordManagerDeleteListViewEstate::class);
			if (in_array($doaction, ['delete', 'bulk_delete'])) {
				check_admin_referer('bulk-'.$this->_pEstateListTable->getArgs()['plural']);
				$itemsDeleted = $pBulkDeleteRecord->delete
					($pRecordManagerDelete, UserCapabilities::RULE_EDIT_VIEW_ESTATE, $estateIds);
				$redirectTo = add_query_arg('delete', $itemsDeleted,
					admin_url('admin.php?page=onoffice-estates'));
			}
			return $redirectTo;
		};

		add_filter('handle_bulk_actions-onoffice_page_onoffice-estates', $pClosureDeleteEstate, 10, 3);
		add_filter('handle_bulk_actions-table-onoffice_page_onoffice-estates', function(): WP_List_Table {
			return $this->_pEstateListTable;
		}, 10);
		parent::preOutput();
	}
}
