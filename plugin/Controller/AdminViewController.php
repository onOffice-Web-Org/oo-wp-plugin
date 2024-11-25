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

namespace onOffice\WPlugin\Controller;

use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\API\APIEmptyResultException;
use onOffice\WPlugin\Controller\SortList\SortListTypes;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Gui\AdminPageAddressList;
use onOffice\WPlugin\Gui\AdminPageAddressListSettings;
use onOffice\WPlugin\Gui\AdminPageAjax;
use onOffice\WPlugin\Gui\AdminPageApiSettings;
use onOffice\WPlugin\Gui\AdminPageBase;
use onOffice\WPlugin\Gui\AdminPageEstate;
use onOffice\WPlugin\Gui\AdminPageEstateListSettings;
use onOffice\WPlugin\Gui\AdminPageEstateUnitList;
use onOffice\WPlugin\Gui\AdminPageEstateUnitSettings;
use onOffice\WPlugin\Gui\AdminPageFormList;
use onOffice\WPlugin\Gui\AdminPageFormSettingsMain;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\WP\WPPluginChecker;
use onOffice\WPlugin\WP\ListTableBulkActionsHandler;
use onOffice\WPlugin\Gui\AdminPageAddress;
use Parsedown;
use HTMLPurifier_Config;
use HTMLPurifier;
use WP_Hook;
use function __;
use function add_action;
use function add_filter;
use function add_menu_page;
use function add_submenu_page;
use function admin_url;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function get_admin_url;
use function is_admin;
use function plugins_url;
use function wp_create_nonce;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;
use const ONOFFICE_DI_CONFIG_PATH;
use const ONOFFICE_PLUGIN_DIR;

/**
 *
 */

class AdminViewController
{
	/** @var string */
	private $_pageSlug = 'onoffice';

	/** @var string[] */
	private $_ajaxHooks = array();

	/** @var AdminPageEstateListSettings */
	private $_pAdminListViewSettings = null;

	/** @var AdminPageAddressListSettings */
	private $_pAdminListViewSettingsAddress = null;

	/** @var AdminPageEstateUnitList */
	private $_pAdminUnitListSettings = null;

	/** @var AdminPageEstate */
	private $_pAdminPageEstates = null;

	/** @var AdminPageFormSettingsMain */
	private $_pAdminPageFormSettings = null;

	/** @var AdminPageAddress */
	private $_pAdminPageAddresses = null;

	/**
	 *
	 */

	public function onInit()
	{
		if (!is_admin()) {
			return;
		}

		$this->_pAdminListViewSettingsAddress = new AdminPageAddressListSettings($this->_pageSlug);
		$this->_ajaxHooks['admin_page_'.$this->_pageSlug.'-editlistviewaddress'] = $this->_pAdminListViewSettingsAddress;

		$this->_pAdminListViewSettings = new AdminPageEstateListSettings($this->_pageSlug);
		$this->_ajaxHooks['admin_page_'.$this->_pageSlug.'-editlistview'] = $this->_pAdminListViewSettings;

		$this->_pAdminUnitListSettings = new AdminPageEstateUnitSettings($this->_pageSlug);
		$this->_ajaxHooks['admin_page_'.$this->_pageSlug.'-editunitlist'] = $this->_pAdminUnitListSettings;

		$this->_pAdminPageFormSettings = new AdminPageFormSettingsMain($this->_pageSlug);
		$this->_ajaxHooks['admin_page_'.$this->_pageSlug.'-editform'] = $this->_pAdminPageFormSettings;

		$this->_pAdminPageEstates = new AdminPageEstate($this->_pageSlug);
		$pSelectedSubPage = $this->_pAdminPageEstates->getSelectedAdminPage();

		if ($pSelectedSubPage instanceof AdminPageAjax) {
			$this->_ajaxHooks['onoffice_page_'.$this->_pageSlug.'-estates'] = $pSelectedSubPage;
		}

		$this->_pAdminPageAddresses = new AdminPageAddress($this->_pageSlug);
		$pSelectedSubPageForAddress = $this->_pAdminPageAddresses->getSelectedAdminPage();

		if ($pSelectedSubPageForAddress instanceof AdminPageAjax) {
			$this->_ajaxHooks['onoffice_page_'.$this->_pageSlug.'-addresses'] = $pSelectedSubPageForAddress;
		}
	}


	/**
	 *
	 * Important note:
	 * - pages usually use the load-(page) hook for handleAdminNotices() but
	 * - ajax pages use it in order to pre-generate the form model.
	 * - page slugs must be chosen according to WP's sanitize_key() function because of
	 *   wp_ajax_closed_postboxes()
	 *
	 */

	public function register_menu()
	{
		add_action('admin_notices', [$this, 'displayAPIError']);
		add_action('admin_notices', [$this, 'displayUsingEmptyDefaultEmailError']);
		add_action('admin_notices', [$this, 'displayDeactivateDuplicateCheckWarning']);
		$pUserCapabilities = new UserCapabilities;
		$roleMainPage = $pUserCapabilities->getCapabilityForRule(UserCapabilities::RULE_VIEW_MAIN_PAGE);
		$roleAddress = $pUserCapabilities->getCapabilityForRule(UserCapabilities::RULE_EDIT_VIEW_ADDRESS);
		$roleEstate = $pUserCapabilities->getCapabilityForRule(UserCapabilities::RULE_EDIT_VIEW_ESTATE);
		$roleForm = $pUserCapabilities->getCapabilityForRule(UserCapabilities::RULE_EDIT_VIEW_FORM);
		$roleModules = $pUserCapabilities->getCapabilityForRule(UserCapabilities::RULE_EDIT_MODULES);
		$roleSettings = $pUserCapabilities->getCapabilityForRule(UserCapabilities::RULE_EDIT_SETTINGS);
		$pDIBuilder = new ContainerBuilder();
		$pDIBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pDI = $pDIBuilder->build();

		// main page
		add_menu_page( __( 'onOffice', 'onoffice-for-wp-websites' ), __( 'onOffice', 'onoffice-for-wp-websites' ),
			$roleMainPage, $this->_pageSlug, function () use ( $pDI ) {
				echo $pDI->get( MainPage::class )->render();
			}, 'none' );

		// Getting started
		add_submenu_page( $this->_pageSlug, __( 'Getting started', 'onoffice-for-wp-websites' ),
			__( 'Getting started', 'onoffice-for-wp-websites' ),
			$roleMainPage, $this->_pageSlug );

		// Addresses
		$hookAddresses = add_submenu_page( $this->_pageSlug, __('Addresses', 'onoffice-for-wp-websites'),
			__('Addresses', 'onoffice-for-wp-websites'), $roleAddress,
			$this->_pageSlug.'-addresses', array($this->_pAdminPageAddresses, 'render'));
		add_action('load-'.$hookAddresses, [$this->_pAdminPageAddresses, 'handleAdminNotices']);
		$pSelectedSubPageForAddress = $this->_pAdminPageAddresses->getSelectedAdminPage();
		if ($pSelectedSubPageForAddress instanceof AdminPageAjax) {
			add_action( 'load-'.$hookAddresses, array($pSelectedSubPageForAddress, 'checkForms'));
		}
		add_action('current_screen', [$this->_pAdminPageAddresses, 'preOutput']);

		// Estates
		$hookEstates = add_submenu_page( $this->_pageSlug, __('Estates', 'onoffice-for-wp-websites'),
			__('Estates', 'onoffice-for-wp-websites'), $roleEstate,
			$this->_pageSlug.'-estates',  array($this->_pAdminPageEstates, 'render'));
		add_action( 'load-'.$hookEstates, array($this->_pAdminPageEstates, 'handleAdminNotices'));
		$pSelectedSubPage = $this->_pAdminPageEstates->getSelectedAdminPage();
		if ($pSelectedSubPage instanceof AdminPageAjax) {
			add_action( 'load-'.$hookEstates, array($pSelectedSubPage, 'checkForms'));
		}
		add_action('current_screen', [$this->_pAdminPageEstates, 'preOutput']);

		// Forms
		$pAdminPageFormList = new AdminPageFormList($this->_pageSlug);
		$hookForms = add_submenu_page( $this->_pageSlug, __('Forms', 'onoffice-for-wp-websites'), __('Forms', 'onoffice-for-wp-websites'),
			$roleForm, $this->_pageSlug.'-forms', array($pAdminPageFormList, 'render'));
		add_action( 'load-'.$hookForms, array($pAdminPageFormList, 'handleAdminNotices'));
		add_action('current_screen', [$pAdminPageFormList, 'preOutput']);

		// Edit Form (hidden page)
		$hookEditForm = add_submenu_page(null, null, null, $roleForm, $this->_pageSlug.'-editform',
			array($this->_pAdminPageFormSettings, 'render'));
		add_action( 'load-'.$hookEditForm, array($this->_pAdminPageFormSettings, 'initSubClassForGet'));
		add_action( 'load-'.$hookEditForm, array($this->_pAdminPageFormSettings, 'handleAdminNotices'));
		add_action( 'load-'.$hookEditForm, array($this->_pAdminPageFormSettings, 'checkForms'));

		// Estates: edit list view (hidden page)
		$hookEditList = add_submenu_page(null, null, null, $roleEstate, $this->_pageSlug.'-editlistview',
			array($this->_pAdminListViewSettings, 'render'));
		add_action( 'load-'.$hookEditList, array($this->_pAdminListViewSettings, 'handleAdminNotices'));
		add_action( 'load-'.$hookEditList, array($this->_pAdminListViewSettings, 'checkForms'));

		// Estates: edit list view (hidden page)
		$hookEditUnitList = add_submenu_page(null, null, null, $roleEstate, $this->_pageSlug.'-editunitlist',
			array($this->_pAdminUnitListSettings, 'render'));
		add_action( 'load-'.$hookEditUnitList, array($this->_pAdminUnitListSettings, 'handleAdminNotices'));
		add_action( 'load-'.$hookEditUnitList, array($this->_pAdminUnitListSettings, 'checkForms'));

		// Address: edit list view (hidden page)
		$hookEditAddressList = add_submenu_page(null, null, null, $roleEstate, $this->_pageSlug.'-editlistviewaddress',
			array($this->_pAdminListViewSettingsAddress, 'render'));
		add_action( 'load-'.$hookEditAddressList, array($this->_pAdminListViewSettingsAddress, 'handleAdminNotices'));
		add_action( 'load-'.$hookEditAddressList, array($this->_pAdminListViewSettingsAddress, 'checkForms'));

		// Settings
		$pAdminSettingsPage = new AdminPageApiSettings($this->_pageSlug.'-settings');
		$hookSettings = add_submenu_page( $this->_pageSlug, __('Settings', 'onoffice-for-wp-websites'),
			__('Settings', 'onoffice-for-wp-websites'), $roleSettings, $this->_pageSlug.'-settings',
			array($pAdminSettingsPage, 'render'));
		add_action( 'admin_init', array($pAdminSettingsPage, 'registerForms'));
		add_action( 'load-'.$hookSettings, array($pAdminSettingsPage, 'handleAdminNotices'));

		// add permission edit setting page for editor
		add_filter('option_page_capability_onoffice-settings', function () {
			return 'edit_pages';
		}, 10, 1);

		add_action('current_screen', function() use ($pDI) {
			/* @var $pWPBulkActionHandler ListTableBulkActionsHandler */
			$pWPBulkActionHandler = $pDI->get(ListTableBulkActionsHandler::class);
			$pWPBulkActionHandler->processBulkAction();
		}, 11);
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
		$currentScreen = get_current_screen()->id;
		$pAdminView = $this->_ajaxHooks[$hook];
		$ajaxDataAdminPage = $pAdminView->getEnqueueData();
		$ajaxDataGeneral = [
			'ajax_url' => admin_url('admin-ajax.php'),
			'action' => $hook,
			'nonce' => wp_create_nonce($hook),
			'current_screen' => $currentScreen
		];

		$ajaxData = array_merge($ajaxDataGeneral, $ajaxDataAdminPage);

		wp_register_script('oo-sort-by-user-selection',
			plugin_dir_url(ONOFFICE_PLUGIN_DIR.'/index.php').'dist/onoffice-sort-by-user-selection.min.js', ['jquery'], '', true);

		wp_register_script('onoffice-ajax-settings',
			plugins_url('/dist/ajax_settings.min.js', ONOFFICE_PLUGIN_DIR.'/index.php'), ['jquery', 'oo-sort-by-user-selection']);
		wp_enqueue_script('onoffice-ajax-settings');
		wp_enqueue_script('onoffice-geofieldbox',
			plugins_url('/dist/geofieldbox.min.js', ONOFFICE_PLUGIN_DIR.'/index.php'), [], null, true);

		wp_localize_script('oo-sort-by-user-selection', 'onoffice_mapping_translations',
			SortListTypes::getSortOrder());

		wp_localize_script('onoffice-ajax-settings', 'onOffice_loc_settings', $ajaxData);
	}


	/**
	 *
	 * @throws Exception
	 *
	 */

	public function add_actions()
	{
		foreach ( $this->_ajaxHooks as $hook => $pAdminPage ) {
			if ( ! is_callable( array( $pAdminPage, 'save_form' ) ) ) {
				throw new Exception( get_class( $pAdminPage ) . ' must be an instance of AdminPageAjax!' );
			}

			add_action( 'admin_post_' . $hook, array( &$this->_ajaxHooks[ $hook ], 'save_form' ) );
		}
	}


	/**
	 *
	 */

	public function disableHideMetaboxes()
	{
		$hookNames = array_map(function(string $value): string {
			return 'metaboxhidden_'.$value;
		}, array_keys($this->_ajaxHooks));

		// never auto-hide metaboxes (such as geo-position box)
		add_filter('update_user_metadata', function ($null, $userId, $metaKey) use ($hookNames) {
			return (in_array($metaKey, $hookNames)) ?: null;
		}, 10, 3);
	}


	/**
	 *
	 */

	public function enqueue_css()
	{
		wp_enqueue_style('onoffice-admin-css',
			plugins_url('/css/admin.css', ONOFFICE_PLUGIN_DIR.'/index.php'), array(), 'v5.2');
	}


	/**
	 *
	 * @param string $hook
	 *
	 */

	public function enqueueExtraJs($hook)
	{
		$confirmDialogGoogleRecaptcha = [
			'notification' => __('Would you like to permanently delete the site key and the secret key?', 'onoffice-for-wp-websites'),
		];
		wp_register_script('handle-notification-actions', plugins_url('dist/onoffice-handle-notification-actions.min.js', ONOFFICE_PLUGIN_DIR . '/index.php'),
			array('jquery'));
		wp_localize_script('handle-notification-actions', 'duplicate_check_option_vars', ['ajaxurl' => admin_url('admin-ajax.php')]);
		wp_localize_script('handle-notification-actions', 'warning_active_plugin_vars', ['ajaxurl' => admin_url('admin-ajax.php')]);
		wp_enqueue_script('handle-notification-actions');

		if (__String::getNew($hook)->contains($this->_pageSlug.'-settings')) {
			wp_register_script('handle-visibility-google-recaptcha-keys', plugins_url('dist/onoffice-handle-visibility-google-recaptcha-keys.min.js', ONOFFICE_PLUGIN_DIR . '/index.php'),
				array('jquery'));
			wp_localize_script('handle-notification-actions', 'delete_google_recaptcha_keys', ['ajaxurl' => admin_url('admin-ajax.php')]);
			wp_localize_script('handle-notification-actions', 'confirm_dialog_google_recaptcha_keys', $confirmDialogGoogleRecaptcha);
			wp_enqueue_script('handle-visibility-google-recaptcha-keys');
		}

		if (__String::getNew($hook)->contains('onoffice')) {
			$pObject = $this->getObjectByHook($hook);
			if ($pObject !== null && method_exists($pObject, 'doExtraEnqueues')) {
				$pObject->doExtraEnqueues();
			}
		}
	}


	/**
	 *
	 * Todo: Delete if pages are being registered and accessible from
	 *	     a member variable by hook
	 *
	 * @global WP_Hook[] $wp_filter
	 * @param string $hook
	 * @return AdminPageBase
	 *
	 */

	private function getObjectByHook($hook)
	{
		global $wp_filter;
		$fullHook = $hook;

		if (isset($wp_filter[$fullHook])) {
			/* @var $pWpHook WP_Hook */
			$pWpHook = $wp_filter[$fullHook];

			foreach ($pWpHook->callbacks as $priority => $settingsPriorized) {
				foreach ($settingsPriorized as $settings) {
					$pObject = isset($settings['function']) && is_array($settings['function']) ?
						$settings['function'][0] : null;
					if ($pObject !== null) {
						return $pObject;
					}
				}
			}
		}
		return null;
	}


	/**
	 *
	 * @param array $links
	 * @return array
	 *
	 */

	public function pluginSettingsLink($links)
	{
		$url = get_admin_url().'admin.php?'.http_build_query(['page' => $this->_pageSlug]);
		$settings_link = '<a href="'.esc_html($url).'">'.esc_html__('Settings', 'onoffice-for-wp-websites').'</a>';
		array_unshift($links, $settings_link);
		return $links;
	}


	/**
	 *
	 */

	public function displayAPIError()
	{
		try {
			$this->getField()->loadApiEstateCategories();
		} catch (APIClientCredentialsException $pCredentialsException) {
			$class = 'notice notice-error';
			$label = __('API token and secret', 'onoffice-for-wp-websites');
			$loginCredentialsLink = sprintf('<a href="admin.php?page=onoffice-settings">%s</a>', $label);
			/* translators: %s will be replaced with the translation of 'API token and secret'. */
			$message = sprintf(esc_html(__('It looks like you did not enter any valid API '
				.'credentials. Please consider reviewing your %s.', 'onoffice-for-wp-websites')), $loginCredentialsLink);

			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
		} catch ( APIEmptyResultException $pEmptyResultException ) {
			$class = 'notice notice-error';
			$label = __('The onOffice plugin has an unexpected problem when trying to reach the onOffice API.', 'onoffice-for-wp-websites');
			$labelOnOfficeServerStatus = __( 'onOffice server status', 'onoffice-for-wp-websites' );
			$onOfficeServerStatusLink  = sprintf( '<a href="' . __('https://status.onoffice.de/', 'onoffice-for-wp-websites') . '">%s</a>', $labelOnOfficeServerStatus );
			$labelSupportFormLink      = __( 'support form', 'onoffice-for-wp-websites' );
			$supportFormLink           = sprintf( '<a href="' . __('https://wp-plugin.onoffice.com/en/support/', 'onoffice-for-wp-websites') . '">%s</a>', $labelSupportFormLink );
			/* translators: %1$s is office server status page link, %2$s is support form page link */
			$message                   = sprintf( esc_html( __( 'Please check the %1$s to see if there are known problems. Otherwise, report the problem using the %2$s.',
				'onoffice-for-wp-websites' ) ), $onOfficeServerStatusLink, $supportFormLink );

			printf( '<div class="%1$s"><p>%2$s</p><p>%3$s</p></div>', esc_attr( $class ), $label, $message );
		}
	}


	/**
	 *
	 */

	public function displayDeactivateDuplicateCheckWarning()
	{
		if ( get_option( 'onoffice-duplicate-check-warning', '' ) === "1" ) {
			$class = 'notice notice-error duplicate-check-notify is-dismissible';
			$message = esc_html(__("We have deactivated the plugin's duplicate check for all of your forms, "
				. "because the duplicate check can unintentionally overwrite address records. This function will be removed "
				. "in the future. The option has been deactivated for these forms: Contact, Interest, Owner",
				'onoffice-for-wp-websites'));

			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
		}
	}

	public function getField()
	{
		return new Fieldnames(new FieldsCollection());
	}

	public function generalAdminNoticeSEO() {
		$urlOnofficeSetting = admin_url().'admin.php?page=onoffice-settings#notice-seo';
		$nameOnofficeSetting = esc_html__('onOffice plugin settings','onoffice-for-wp-websites');
		$pluginOnofficeSetting = sprintf("<a href='%s' target='_blank' rel='noopener'>%s</a>", $urlOnofficeSetting,$nameOnofficeSetting);

        $WPPluginChecker = new WPPluginChecker;
		$activeSEOPlugins = $WPPluginChecker->getActiveSEOPlugins();
		$listNamePluginSEO = implode(", ", $activeSEOPlugins);
		if ($WPPluginChecker->isSEOPluginActive()) {
			if (get_option('onoffice-click-button-close-action') == 0
				&& get_current_screen()->id !== 'onoffice_page_onoffice-settings'
				&& get_option('onoffice-settings-title-and-description') == 0) {
				$class = 'notice notice-warning active-plugin-seo is-dismissible';
				$message = sprintf(esc_html__('The onOffice plugin has detected an active SEO plugin: %s. You currently have configured the onOffice plugin to fill out the title and description of the detail page, which can lead to conflicts with the SEO plugin.
								We recommend that you go to the %s and configure the onOffice plugin to not modify the title and description. This allows you to manage the title and description with your active SEO plugin.', 'onoffice-for-wp-websites'), $listNamePluginSEO, $pluginOnofficeSetting);
				$messageParsedown = Parsedown::instance()
					->setSafeMode(true)
					->setUrlsLinked(false)
					->setBreaksEnabled(true)->text(
						$message
					);
				$messageDecodeHTML = html_entity_decode($messageParsedown);
				echo sprintf('<div class="%1$s">%2$s</div>', esc_attr($class), $messageDecodeHTML);
			}
		} else {
			update_option('onoffice-click-button-close-action', 0);
		}
	}
	
	public function displayUsingEmptyDefaultEmailError()
	{
		if ( ! get_option( 'onoffice-settings-default-email', '' ) && $this->getRecordManagerReadForm()->getCountDefaultRecipientRecord() != 0 ) {
			$class                   = 'notice notice-error';
			$label                   = __( 'plugin settings', 'onoffice-for-wp-websites' );
			$defaultEmailAddressLink = sprintf( '<a href="admin.php?page=onoffice-settings">%s</a>', $label );
			/* translators: %s will be replaced with the translation of 'plugin settings'. */
			$message = sprintf(esc_html(__('The onOffice plugin is missing a default email address. You have forms that use the default email and they will currently not send emails. Please add a default email address in the %s to dismiss this warning.', 'onoffice-for-wp-websites')),
				$defaultEmailAddressLink);

			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
		}
	}

	public function getRecordManagerReadForm()
	{
		return new RecordManagerReadForm();
	}
}
