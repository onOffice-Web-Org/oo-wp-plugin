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

	/** @var string[] */
	private $_ajaxHooks = array();

	/** @var AdminPageEstateListSettings */
	private $_pAdminListViewSettings = null;


	/**
	 *
	 */

	public function onInit()
	{
		if (!is_admin()) {
			return;
		}

		$this->_pageSlug = 'onoffice';
		$this->_pAdminListViewSettings = new AdminPageEstateListSettings($this->_pageSlug);
		$this->_ajaxHooks['admin_page_'.$this->_pageSlug.'-editListView'] = $this->_pAdminListViewSettings;
	}


	/**
	 *
	 * Important note:
	 * - pages usually use the load-(page) hook for handleAdminNotices() but
	 * - ajax pages use it in order to pre-generate the form model.
	 *
	 */

	public function register_menu()
	{
		add_menu_page( __('onOffice', 'onoffice'), __('onOffice', 'onoffice'), 'edit_pages',
			$this->_pageSlug, function(){});

		$pAdminPageEstate = new AdminPageEstate($this->_pageSlug);
		$hookEstates = add_submenu_page( $this->_pageSlug, __('Estates', 'onoffice'),
			__('Estates', 'onoffice'), 'edit_pages',
			$this->_pageSlug.'-estates', array($pAdminPageEstate, 'render'));
		add_action( 'load-'.$hookEstates, array($pAdminPageEstate, 'handleAdminNotices'));

		add_submenu_page( $this->_pageSlug, __('Forms', 'onoffice'), __('Forms', 'onoffice'),
			'edit_pages', $this->_pageSlug.'-forms', function() {});

		$pAdminPageModules = new AdminPageModules($this->_pageSlug);
		add_submenu_page( $this->_pageSlug, __('Modules', 'onoffice'), __('Modules', 'onoffice'),
			'edit_pages', $this->_pageSlug.'-modules', array($pAdminPageModules, 'render'));
		add_action( 'admin_init', array($pAdminPageModules, 'registerForms'));

		$hookEditList = add_submenu_page(null, null, null, 'edit_pages', $this->_pageSlug.'-editListView',
			array($this->_pAdminListViewSettings, 'render'));
		add_action( 'load-'.$hookEditList, array($this->_pAdminListViewSettings, 'checkForms'));

		$pAdminSettingsPage = new AdminPageApiSettings($this->_pageSlug.'-settings');
		$hookSettings = add_submenu_page( $this->_pageSlug, __('Settings', 'onoffice'),
			__('Settings', 'onoffice'), 'edit_pages', $this->_pageSlug.'-settings',
			array($pAdminSettingsPage, 'render'));
		add_action( 'admin_init', array($pAdminSettingsPage, 'registerForms'));
		add_action( 'load-'.$hookSettings, array($pAdminSettingsPage, 'handleAdminNotices'));
	}


	/**
	 *
	 * @param string $hook
	 *
	 */

	public function enqueue_ajax($hook)
	{
		if ($hook == '' || !array_key_exists($hook, $this->_ajaxHooks)) {
			return;
		}

		$pAdminView = $this->_ajaxHooks[$hook];
		$ajaxDataAdminPage = $pAdminView->getEnqueueData();
		$ajaxDataGeneral = array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'action' => $hook,
				'nonce' => wp_create_nonce($hook),
			);
		$ajaxData = array_merge($ajaxDataGeneral, $ajaxDataAdminPage);

		wp_enqueue_script('onoffice-ajax-settings',
			plugins_url('/js/ajax_settings.js', ONOFFICE_PLUGIN_DIR.'/index.php'), array('jquery'));

		wp_localize_script('onoffice-ajax-settings', 'onOffice_loc_settings', $ajaxData);
	}


	/**
	 *
	 * @throws Exception
	 *
	 */

	public function add_ajax_actions()
	{
		foreach ($this->_ajaxHooks as $hook => $pAdminPage) {
			if (!$pAdminPage instanceof AdminPageAjax) {
				throw new \Exception(get_class($pAdminPage).' must be an instance of AdminPageAjax!');
			}

			add_action( 'wp_ajax_'.$hook, array($this->_ajaxHooks[$hook], 'ajax_action'));
		}
	}
}
