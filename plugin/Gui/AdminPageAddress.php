<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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
use onOffice\WPlugin\Record\RecordManagerDeleteListViewAddress;
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
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class AdminPageAddress
	extends AdminPage
{
	/** */
	const PAGE_ADDRESS_LIST = 'list';

	/** */
	const PAGE_ADDRESS_DETAIL = 'detail';

	/** @var string[] */
	private $_subPageClassByTab = array(
		self::PAGE_ADDRESS_LIST => AdminPageAddressList::class,
		self::PAGE_ADDRESS_DETAIL => AdminPageAddressDetail::class,
	);

	/** */
	const PARAM_TAB = 'tab';

	/** @var array */
	private $_tabs = array();

	/** @var AdminPage */
	private $_pSelectedTab = null;

	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		$this->_tabs = array(
			self::PAGE_ADDRESS_LIST => __('Address Views', 'onoffice-for-wp-websites'),
			self::PAGE_ADDRESS_DETAIL => __('Detail View', 'onoffice-for-wp-websites'),
		);

		parent::__construct($pageSlug);

		$selectedTab = $this->getSelectedTab();
		$this->_pSelectedTab = $this->getAdminPageForTab($selectedTab);
	}

	/**
	 *
	 * @return string
	 *
	 */

	private function getSelectedTab(): string
	{
		$selectedTab = $this->getDefaultTab();
		$getParamTab = filter_input(INPUT_GET, self::PARAM_TAB);
		$postParamTab = filter_input(INPUT_POST, self::PARAM_TAB);
		if (!is_null($getParamTab)) {
			$selectedTab = $getParamTab;
		} elseif (!is_null($postParamTab)) {
			$selectedTab = $postParamTab;
		}

		return $selectedTab;
	}


	/**
	 *
	 * @param string $tab
	 * @return AdminPage
	 *
	 */

	private function getAdminPageForTab(string $tab)
	{
		if (!isset($this->_subPageClassByTab[$tab])) {
			$tab = self::PAGE_ADDRESS_LIST;
		}

		$className = $this->_subPageClassByTab[$tab];
		$pAdminPage = new $className($this->getPageSlug());

		return $pAdminPage;
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$selectedTab = $this->getSelectedTab();
		$defaultTab = $this->getDefaultTab();
		$this->_pSelectedTab->generatePageMainTitle(__('Addresses', 'onoffice-for-wp-websites'));

		echo '<h2 class="nav-tab-wrapper">';
		$adminUrl = admin_url('admin.php?page=onoffice-addresses');

		foreach ($this->_tabs as $index => $label)
		{
			$newAdminUrl = ($index != $defaultTab) ? add_query_arg('tab', $index, $adminUrl) : $adminUrl;
			$class = ($index === $selectedTab) ? ' nav-tab-active' : '';
			echo '<a href="'.$newAdminUrl.'" class="nav-tab'.$class.'">'.esc_html($label).'</a>';
		}
		echo '</h2>';
		echo $this->_pSelectedTab->renderContent();
	}

	/**
	 *
	 * @return string
	 *
	 */

	private function getDefaultTab(): string
	{
		return self::PAGE_ADDRESS_LIST;
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
		$this->_pSelectedTab->handleAdminNotices();
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
		$this->_pSelectedTab->preOutput();

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pDI = $pContainerBuilder->build();
		$pClosureDeleteAddress = function(string $redirectTo, Table\WP\ListTable $pTable, array $recordIds) use ($pDI): string {
			/* @var $pBulkDeleteRecord BulkDeleteRecord */
			$pBulkDeleteRecord = $pDI->get(BulkDeleteRecord::class);
			/* @var $pRecordManagerDelete RecordManagerDeleteListViewAddress */
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

		$pClosureDuplicateAddress = function (string $redirectTo, Table\WP\ListTable $pTable) use ($pDI): string {
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

		parent::preOutput();
	}

	/**
	 *
	 * @return AdminPage
	 *
	 */

	public function getSelectedAdminPage()
	{
		return $this->_pSelectedTab;
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		$this->_pSelectedTab->doExtraEnqueues();
	}
}
