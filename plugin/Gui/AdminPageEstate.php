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

use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\Form\BulkDeleteRecord;
use onOffice\WPlugin\Record\RecordManagerDeleteListViewEstate;
use WP_List_Table;
use const ONOFFICE_DI_CONFIG_PATH;
use function __;
use function add_action;
use function add_filter;
use function add_query_arg;
use function admin_url;
use function check_admin_referer;
use function esc_html;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstate
	extends AdminPage
{
	/** */
	const PAGE_ESTATE_LIST = 'list';

	/** */
	const PAGE_ESTATE_DETAIL = 'detail';

	/** */
	const PAGE_ESTATE_UNITS = 'units';

	/** */
	const PARAM_TAB = 'tab';

	/** @var array */
	private $_tabs = array();

	/** @var string[] */
	private $_subPageClassByTab = array(
		self::PAGE_ESTATE_LIST => AdminPageEstateList::class,
		self::PAGE_ESTATE_DETAIL => AdminPageEstateDetail::class,
		self::PAGE_ESTATE_UNITS => AdminPageEstateUnitList::class,
	);

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
			self::PAGE_ESTATE_LIST => __('List Views', 'onoffice'),
			self::PAGE_ESTATE_DETAIL => __('Detail View', 'onoffice'),
			self::PAGE_ESTATE_UNITS => __('Unit Lists', 'onoffice'),
		);

		parent::__construct($pageSlug);

		$selectedTab = $this->getSelectedTab();
		$this->_pSelectedTab = $this->getAdminPageForTab($selectedTab);
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$selectedTab = $this->getSelectedTab();
		$defaultTab = $this->getDefaultTab();
		$this->_pSelectedTab->generatePageMainTitle('Estates');

		echo '
		<h2 class="nav-tab-wrapper">';
		$adminUrl = admin_url('admin.php?page=onoffice-estates');

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

	private function getSelectedTab()
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
	 * @return string
	 *
	 */

	private function getDefaultTab()
	{
		return self::PAGE_ESTATE_LIST;
	}


	/**
	 *
	 * @param string $tab
	 * @return AdminPage
	 *
	 */

	private function getAdminPageForTab($tab)
	{
		if (!isset($this->_subPageClassByTab[$tab])) {
			$tab = self::PAGE_ESTATE_LIST;
		}

		$className = $this->_subPageClassByTab[$tab];
		$pAdminPage = new $className($this->getPageSlug());

		return $pAdminPage;
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

	public function doExtraEnqueues()
	{
		$this->_pSelectedTab->doExtraEnqueues();
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

		$pClosureDeleteEstate = function(string $redirectTo, WP_List_Table $pTable, array $estateIds)
			use ($pDI): string
		{
			/* @var $pBulkDeleteRecord BulkDeleteRecord */
			$pBulkDeleteRecord = $pDI->get(BulkDeleteRecord::class);
			/* @var $pRecordManagerDelete RecordManagerDeleteListViewEstate */
			$pRecordManagerDelete = $pDI->get(RecordManagerDeleteListViewEstate::class);
			if (in_array($pTable->current_action(), ['delete', 'bulk_delete'])) {
				check_admin_referer('bulk-'.$pTable->getArgs()['plural']);
				$itemsDeleted = $pBulkDeleteRecord->delete
					($pRecordManagerDelete, UserCapabilities::RULE_EDIT_VIEW_ESTATE, $estateIds);
				$redirectTo = add_query_arg(['delete' => $itemsDeleted, 'tab' => $this->getSelectedTab()],
					admin_url('admin.php?page=onoffice-estates'));
			}
			return $redirectTo;
		};

		add_filter('handle_bulk_actions-onoffice_page_onoffice-estates', $pClosureDeleteEstate, 10, 3);

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
}
