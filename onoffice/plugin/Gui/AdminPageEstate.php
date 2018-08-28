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
		self::PAGE_ESTATE_LIST => '\onOffice\WPlugin\Gui\AdminPageEstateList',
		self::PAGE_ESTATE_DETAIL => '\onOffice\WPlugin\Gui\AdminPageEstateDetail',
		self::PAGE_ESTATE_UNITS => '\onOffice\WPlugin\Gui\AdminPageEstateUnitList',
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
		if (!array_key_exists($tab, $this->_subPageClassByTab))
		{
			wp_die('Missing class!');
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
	 * @return AdminPage
	 *
	 */

	public function getSelectedAdminPage()
	{
		return $this->_pSelectedTab;
	}
}
