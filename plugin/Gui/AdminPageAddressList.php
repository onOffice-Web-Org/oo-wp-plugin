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

use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\Form\BulkDeleteRecord;
use onOffice\WPlugin\Gui\Table\AddressListTable;
use onOffice\WPlugin\Record\RecordManagerDeleteListViewAddress;
use onOffice\WPlugin\Record\RecordManagerDeleteListViewEstate;
use onOffice\WPlugin\Record\RecordManagerDuplicateListViewAddress;
use const ONOFFICE_DI_CONFIG_PATH;
use function __;
use function add_action;
use function add_filter;
use function add_query_arg;
use function admin_url;
use function check_admin_referer;
use function esc_html__;

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
		$this->generatePageMainTitle(__('Addresses', 'onoffice-for-wp-websites'));
		$this->_pAddressListTable->prepare_items();
		echo '<p>';
		echo '<form method="post">';
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
			echo ' › '.esc_html__($subTitle, 'onoffice-for-wp-websites');
		}

		echo '</h1>';

		$newLink = admin_url('admin.php?page=onoffice-editlistviewaddress');
		echo '<a href="'.$newLink.'" class="page-title-action">'.esc_html__('Add New', 'onoffice-for-wp-websites').'</a>';
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

	public function preOutput()
	{
		$this->_pAddressListTable = new AddressListTable();
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pDI = $pContainerBuilder->build();
		$pClosureDeleteAddress = function(string $redirectTo, Table\WP\ListTable $pTable, array $recordIds)
			use ($pDI): string
		{
			/* @var $pBulkDeleteRecord BulkDeleteRecord */
			$pBulkDeleteRecord = $pDI->get(BulkDeleteRecord::class);
			/* @var $pRecordManagerDelete RecordManagerDeleteListViewEstate */
			$pRecordManagerDelete = $pDI->get(RecordManagerDeleteListViewAddress::class);
			if (in_array($pTable->current_action(), ['delete', 'bulk_delete'])) {
				check_admin_referer('bulk-'.$pTable->getArgs()['plural']);
				$itemsDeleted = $pBulkDeleteRecord->delete
					($pRecordManagerDelete, UserCapabilities::RULE_EDIT_VIEW_ADDRESS, $recordIds);
				$redirectTo = add_query_arg(['delete' => $itemsDeleted],
					admin_url('admin.php?page=onoffice-addresses'));
			}
			return $redirectTo;
		};

		add_filter('handle_bulk_actions-onoffice_page_onoffice-addresses', $pClosureDeleteAddress, 10, 3);

		$pClosureDuplicateAddress = function (string $redirectTo, Table\WP\ListTable $pTable)
		use ($pDI): string {
			if (in_array($pTable->current_action(), ['duplicate', 'bulk_duplicate'])) {
				check_admin_referer('bulk-' . $pTable->getArgs()['plural']);
				if (!(isset($_GET['listViewId']))) {
					wp_die('No List Views for duplicating!');
				}

				/* @var $pRecordManagerDuplicateListViewAddress RecordManagerDuplicateListViewAddress */
				$pRecordManagerDuplicateListViewAddress = $pDI->get(RecordManagerDuplicateListViewAddress::class);
				$listViewRootId = $_GET['listViewId'];
				$pRecordManagerDuplicateListViewAddress->duplicateByName($listViewRootId);
			}
			return $redirectTo;
		};

		add_filter('handle_bulk_actions-onoffice_page_onoffice-addresses', $pClosureDuplicateAddress, 10, 3);
		add_filter('handle_bulk_actions-table-onoffice_page_onoffice-addresses', function(): Table\WP\ListTable {
			return $this->_pAddressListTable;
		});

		parent::preOutput();
	}
}
