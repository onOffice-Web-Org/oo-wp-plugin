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
Description: Your connection to onOffice: This plugin enables you to have quick access to estates and forms – no additional sync with the software is needed.
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
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\SearchParameters;

$pAutoloader = new Psr4AutoloaderClass();
$pAutoloader->addNamespace( 'onOffice', __DIR__ );
$pAutoloader->addNamespace( 'onOffice\SDK', __DIR__.DIRECTORY_SEPARATOR.'SDK' );
$pAutoloader->addNamespace( 'onOffice\WPlugin', __DIR__.DIRECTORY_SEPARATOR.'plugin' );
$pAutoloader->register();

$pContentFilter = new ContentFilter();
$pAdminViewController = new AdminViewController();
$pDetailViewPostSaveController = new DetailViewPostSaveController();
$pSearchParams = SearchParameters::getInstance();
$pSearchParams->setParameters( $_GET );

add_action( 'init', array($pContentFilter, 'addCustomRewriteTags') );
add_action( 'init', array($pContentFilter, 'addCustomRewriteRules') );
add_action( 'init', '\onOffice\WPlugin\FormPostHandler::initialCheck' );
add_action( 'admin_menu', array($pAdminViewController, 'register_menu') );
add_action( 'admin_enqueue_scripts', array($pAdminViewController, 'enqueue_ajax') );
add_action( 'admin_enqueue_scripts', array($pAdminViewController, 'enqueue_css') );
add_action( 'admin_enqueue_scripts', array($pAdminViewController, 'enqueueExtraJs') );
add_action( 'wp_enqueue_scripts', array($pContentFilter, 'registerScripts'), 9 );
add_action( 'wp_enqueue_scripts', array($pContentFilter, 'includeScripts') );
add_action( 'save_post', array($pDetailViewPostSaveController, 'onSavePost') );
add_action( 'wp_trash_post', array($pDetailViewPostSaveController, 'onMoveTrash') );
add_action( 'oo_cache_cleanup', 'ooCacheCleanup' );

add_action( 'init', array($pAdminViewController, 'onInit') );
add_action( 'admin_init', array($pAdminViewController, 'add_ajax_actions') );

add_filter( 'the_content', array($pContentFilter, 'filter_the_content') );
add_filter( 'wp_link_pages_link', array($pSearchParams, 'linkPagesLink'), 10, 2 );
add_filter( 'wp_link_pages_args', array($pSearchParams, 'populateDefaultLinkParams') );

// "Settings" link in plugins list
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), array($pAdminViewController, 'pluginSettingsLink') );

add_shortcode( 'oo_estate', array($pContentFilter, 'registerEstateShortCodes') );
add_shortcode( 'oo_form', array($pContentFilter, 'renderFormsSortCodes') );
add_shortcode( 'oo_basicdata', array($pContentFilter, 'renderImpressumShortCodes'));

register_activation_hook( __FILE__, '\onOffice\WPlugin\Installer::install' );
register_deactivation_hook( __FILE__, '\onOffice\WPlugin\Installer::deactivate' );
register_uninstall_hook( __FILE__, '\onOffice\WPlugin\Installer::deinstall' );

if ( ! wp_next_scheduled( 'oo_cache_cleanup' ) ) {
	wp_schedule_event( time(), 'hourly', 'oo_cache_cleanup' );
}


/**
 *
 * Callback for cron job
 *
 */

function ooCacheCleanup() {
	$pSDKWrapper = new SDKWrapper();
	$cacheInstances = $pSDKWrapper->getCache();

	foreach ( $cacheInstances as $pCacheInstance) {
		/* @var $cacheInstance onOfficeSDKCache */
		$pCacheInstance->cleanup();
	}
}
