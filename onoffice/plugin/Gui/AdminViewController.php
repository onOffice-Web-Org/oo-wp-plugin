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


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pageSlug = 'onoffice';
	}


	/**
	 *
	 */

	public function register_menu()
	{
		add_menu_page( __('onOffice', 'onoffice'), __('onOffice', 'onoffice'), 'edit_pages', $this->_pageSlug, function(){});

		$pAdminPageEstate = new AdminPageEstate($this->_pageSlug);
		$hookEstates = add_submenu_page( $this->_pageSlug, __('Estates', 'onoffice'), __('Estates', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-estates', array($pAdminPageEstate, 'render'));
		add_action( 'load-'.$hookEstates, array($pAdminPageEstate, 'handleAdminNotices'));

		add_submenu_page( $this->_pageSlug, __('Forms', 'onoffice'), __('Forms', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-forms', function() {});

		add_submenu_page( $this->_pageSlug, __('Modules', 'onoffice'), __('Modules', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-modules', function() {});


		$pAdminListViewSettings = new AdminPageEstateListSettings($this->_pageSlug);
		add_submenu_page(null, null, null, 'edit_pages', $this->_pageSlug.'-editListView', array($pAdminListViewSettings, 'render'));

		$pAdminSettingsPage = new AdminPageApiSettings($this->_pageSlug.'-settings');
		$hookSettings = add_submenu_page( $this->_pageSlug, __('Settings', 'onoffice'),
			__('Settings', 'onoffice'), 'edit_pages', $this->_pageSlug.'-settings',
			array($pAdminSettingsPage, 'render'));
		add_action( 'admin_init', array($pAdminSettingsPage, 'registerForms'));
		add_action( 'load-'.$hookSettings, array($pAdminSettingsPage, 'handleAdminNotices'));
	}
}
