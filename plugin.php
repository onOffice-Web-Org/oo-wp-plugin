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

/*
Plugin Name: onOffice for WP-Websites
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

require 'vendor/autoload.php';

define('ONOFFICE_PLUGIN_DIR', __DIR__);

use DI\ContainerBuilder;
use onOffice\WPlugin\Cache\CacheHandler;
use onOffice\WPlugin\ContentFilter;
use onOffice\WPlugin\Controller\AdminViewController;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeRegistrator;
use onOffice\WPlugin\Controller\DetailViewPostSaveController;
use onOffice\WPlugin\Form\CaptchaDataChecker;
use onOffice\WPlugin\FormPostHandler;
use onOffice\WPlugin\Installer;
use onOffice\WPlugin\Sandbox;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderRegistrator;
use onOffice\WPlugin\SearchParameters;
use onOffice\WPlugin\Utility\__String;

define('ONOFFICE_DI_CONFIG_PATH', implode(DIRECTORY_SEPARATOR, [ONOFFICE_PLUGIN_DIR, 'config', 'di-config.php']));

$pDIBuilder = new ContainerBuilder();
$pDIBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
$pDI = $pDIBuilder->build();

$pContentFilter = $pDI->get(ContentFilter::class);
$pAdminViewController = new AdminViewController();
$pDetailViewPostSaveController = $pDI->get(DetailViewPostSaveController::class);
$pDI->get(ScriptLoaderRegistrator::class)->generate();
$pSearchParams = SearchParameters::getInstance();
$getVariables = (array)filter_input_array(INPUT_GET, FILTER_DEFAULT);
$pSearchParams->setParameters($getVariables);
//$pSearchParams->setParameters($_GET);

$pSandbox = new Sandbox();

add_action('init', [$pContentFilter, 'addCustomRewriteTags']);
add_action('init', [$pContentFilter, 'addCustomRewriteRules']);

// This hook [wp] is one effective place to perform any high-level filtering or validation,
// following queries, but before WordPress does any routing, processing, or handling.
// https://codex.wordpress.org/Plugin_API/Action_Reference/wp
add_action('wp', [FormPostHandler::class, 'initialCheck']);

add_action('admin_menu', [$pAdminViewController, 'register_menu']);
add_action('admin_enqueue_scripts', [$pAdminViewController, 'enqueue_ajax']);
add_action('admin_enqueue_scripts', [$pAdminViewController, 'enqueue_css']);
add_action('admin_enqueue_scripts', [$pAdminViewController, 'enqueueExtraJs']);
add_action('wp_enqueue_scripts', [CaptchaDataChecker::class, 'registerScripts']);
add_action('save_post', [$pDetailViewPostSaveController, 'onSavePost']);
add_action('wp_trash_post', [$pDetailViewPostSaveController, 'onMoveTrash']);
add_action('oo_cache_cleanup', function() use ($pDI) {
	$pDI->get(CacheHandler::class)->clean();
});

add_action('init', [$pAdminViewController, 'onInit']);
add_action('init', function() use ($pAdminViewController) {
	$pAdminViewController->disableHideMetaboxes();
}, 11);
add_action('admin_init', [$pAdminViewController, 'add_ajax_actions']);
add_action('admin_init', [CaptchaDataChecker::class, 'addHook']);

add_action('plugins_loaded', function() {
	load_plugin_textdomain('onoffice', false, basename(ONOFFICE_PLUGIN_DIR).'/languages');
});
//var_dump(get_permalink());
//$post       = get_post();
//var_dump($post);
//$pWpPost = WP_Post::get_instance($post_id);
//$pWpPost->filter($filter);

add_filter('wp_link_pages_link', [$pSearchParams, 'linkPagesLink'], 10, 2);
add_filter('wp_link_pages_args', [$pSearchParams, 'populateDefaultLinkParams']);

// "Settings" link in plugins list
add_filter('plugin_action_links_'.plugin_basename(__FILE__), [$pAdminViewController, 'pluginSettingsLink']);
//add_filter('the_content', [$pSandbox, 'defineReqVars']);
add_action('wp_loaded', [$pSandbox, 'registerReqVars']);

add_shortcode('oo_estate', [$pContentFilter, 'registerEstateShortCodes']);

$pDI->get(ContentFilterShortCodeRegistrator::class)->register();

add_filter('document_title_parts', [$pContentFilter, 'setTitle'], 10, 2);

register_activation_hook(__FILE__, [Installer::class, 'install']);
register_deactivation_hook(__FILE__, [Installer::class, 'deactivate']);
register_uninstall_hook(__FILE__, [Installer::class, 'deinstall']);

if (!wp_next_scheduled('oo_cache_cleanup')) {
	wp_schedule_event(time(), 'hourly', 'oo_cache_cleanup');
}

// Gets triggered before we know if it has to be updated at all, so that no value has to be changed
add_action('pre_update_option', function($value, $option) use ($pDI) {
	if (__String::getNew($option)->startsWith('onoffice')) {
		$pDI->get(CacheHandler::class)->clear();
	}
	return $value;
}, 10, 2);
