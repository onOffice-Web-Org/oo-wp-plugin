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

/*
Plugin Name: onOffice direct
Author: onOffice GmbH
Author URI: https://en.onoffice.com/
Description: Your connection to onOffice: This plugin enables you to have quick access to estates and forms – no additional sync with the software is needed. Consult support@onoffice.de for source code.
Version: 2.0
License: AGPL 3+
License URI: https://www.gnu.org/licenses/agpl-3.0
Text Domain: onoffice
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die();

include 'Psr4AutoloaderClass.php';

define('ONOFFICE_PLUGIN_DIR', __DIR__);

use onOffice\SDK\Cache\onOfficeSDKCache;
use onOffice\SDK\Psr4AutoloaderClass;
use onOffice\WPlugin\ContentFilter;
use onOffice\WPlugin\Controller\AdminViewController;
use onOffice\WPlugin\Controller\DetailViewPostSaveController;
use onOffice\WPlugin\Field\DistinctFieldsChecker;
use onOffice\WPlugin\Form\CaptchaDataChecker;
use onOffice\WPlugin\FormPostHandler;
use onOffice\WPlugin\Installer;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\SearchParameters;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeAddress;

$pAutoloader = new Psr4AutoloaderClass();
$pAutoloader->addNamespace('onOffice', __DIR__);
$pAutoloader->addNamespace('onOffice\SDK', implode(DIRECTORY_SEPARATOR, [__DIR__, 'SDK', 'trunk', 'SDK']));
$pAutoloader->addNamespace('onOffice\WPlugin', __DIR__.DIRECTORY_SEPARATOR.'plugin');
$pAutoloader->register();

define('ONOFFICE_FEATURE_CONFIGURE_GEO', filter_input(INPUT_SERVER, 'SERVER_NAME') === 'localhost');

$pContentFilter = new ContentFilter();
$pContentFilterAddress = new ContentFilterShortCodeAddress();
$pAdminViewController = new AdminViewController();
$pDetailViewPostSaveController = new DetailViewPostSaveController();
$pSearchParams = SearchParameters::getInstance();
$pSearchParams->setParameters($_GET);

add_action('init', [$pContentFilter, 'addCustomRewriteTags']);
add_action('init', [$pContentFilter, 'addCustomRewriteRules']);
add_action('init', [FormPostHandler::class, 'initialCheck']);
add_action('admin_menu', [$pAdminViewController, 'register_menu']);
add_action('admin_enqueue_scripts', [$pAdminViewController, 'enqueue_ajax']);
add_action('admin_enqueue_scripts', [$pAdminViewController, 'enqueue_css']);
add_action('admin_enqueue_scripts', [$pAdminViewController, 'enqueueExtraJs']);
add_action('wp_enqueue_scripts', [CaptchaDataChecker::class, 'registerScripts']);
add_action('wp_enqueue_scripts', [$pContentFilter, 'registerScripts'], 9);
add_action('wp_enqueue_scripts', [$pContentFilter, 'includeScripts']);
add_action('save_post', [$pDetailViewPostSaveController, 'onSavePost']);
add_action('wp_trash_post', [$pDetailViewPostSaveController, 'onMoveTrash']);
add_action('oo_cache_cleanup', 'ooCacheCleanup');

add_action('init', [$pAdminViewController, 'onInit']);
add_action('admin_init', [$pAdminViewController, 'add_ajax_actions']);
add_action('admin_init', [CaptchaDataChecker::class, 'addHook']);

add_action( 'wp_ajax_addHook', [DistinctFieldsChecker::class, 'addHook']);

add_action( 'plugins_loaded', 'my_plugin_load_plugin_textdomain' );

add_filter('wp_link_pages_link', [$pSearchParams, 'linkPagesLink'], 10, 2);
add_filter('wp_link_pages_args', [$pSearchParams, 'populateDefaultLinkParams']);

// "Settings" link in plugins list
add_filter('plugin_action_links_'.plugin_basename(__FILE__), [$pAdminViewController, 'pluginSettingsLink']);


add_shortcode('oo_address', [$pContentFilterAddress, 'replaceShortCodes']);
add_shortcode('oo_estate', [$pContentFilter, 'registerEstateShortCodes']);
add_shortcode('oo_form', [$pContentFilter, 'renderFormsShortCodes']);
add_shortcode('oo_basicdata', [$pContentFilter, 'renderImpressumShortCodes']);

add_filter('widget_text_content', [$pContentFilter, 'renderWidgetImpressum']);
add_filter('widget_title', [$pContentFilter, 'renderWidgetImpressum']);
add_filter('document_title_parts', [$pContentFilter, 'setTitle'], 10, 2);

register_activation_hook(__FILE__, [Installer::class, 'install']);
register_deactivation_hook(__FILE__, [Installer::class, 'deactivate']);
register_uninstall_hook(__FILE__, [Installer::class, 'deinstall']);

if (!wp_next_scheduled('oo_cache_cleanup')) {
	wp_schedule_event(time(), 'hourly', 'oo_cache_cleanup');
}


/**
 *
 * Callback for cron job
 *
 */

function ooCacheCleanup() {
	$pSDKWrapper = new SDKWrapper();
	$cacheInstances = $pSDKWrapper->getCache();

	foreach ($cacheInstances as $pCacheInstance) {
		/* @var $cacheInstance onOfficeSDKCache */
		$pCacheInstance->cleanup();
	}
}


/**
 *
 * loading text domain
 *
 */

function my_plugin_load_plugin_textdomain(){
	load_plugin_textdomain('onoffice', FALSE, basename( dirname(__FILE__)).'/languages');
}