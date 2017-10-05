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

class AdminViewController
{
	/** @var string */
	private $_pageSlug = null;

	/** @var AdminPageApiSettings */
	private $_pAdminSettingsPage = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pageSlug = 'onoffice';
		$this->_pAdminSettingsPage = new AdminPageApiSettings($this->_pageSlug.'-settings');
	}


	/**
	 *
	 */

	public function register_menu()
	{
		add_menu_page( __('onOffice', 'onoffice'), __('onOffice', 'onoffice'), 'edit_pages', $this->_pageSlug, function(){});
		add_submenu_page( $this->_pageSlug, __('Estates', 'onoffice'), __('Estates', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-estates', function() {});
		add_submenu_page( $this->_pageSlug, __('Forms', 'onoffice'), __('Forms', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-forms', function() {});
		add_submenu_page( $this->_pageSlug, __('Modules', 'onoffice'), __('Modules', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-modules', function() {});
		add_submenu_page( $this->_pageSlug, __('Settings', 'onoffice'), __('Settings', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-settings', array($this->_pAdminSettingsPage, 'render'));
	}


	/**
	 *
	 */

	public function registerForms()
	{
		$this->_pAdminSettingsPage->registerForms();
	}
}
