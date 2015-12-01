<?php
/*
Plugin Name: onOffice Plugin
Plugin URI: http://www.onoffice.com/
Description: onOffice Plugin (just for testing)
Author: onOffice Software AG
Author URI: http://en.onoffice.com/
Version: 1.0
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

include 'Psr4AutoloaderClass.php';

use onOffice\SDK\Psr4AutoloaderClass;
use onOffice\WPlugin\ContentFilter;

$pAutoloader = new Psr4AutoloaderClass();
$pAutoloader->addNamespace( 'onOffice', __DIR__ );
$pAutoloader->addNamespace( 'onOffice\SDK', __DIR__.DIRECTORY_SEPARATOR.'SDK' );
$pAutoloader->addNamespace( 'onOffice\WPlugin', __DIR__.DIRECTORY_SEPARATOR.'plugin' );
$pAutoloader->register();

$config = array();
$config['cache'] = array();
$config['apiversion'] = '1.5';
$config['estate'] = array();
$config['apiversion'] = '1.5';

// load user defined settings
include 'config.php';

$pContentFilter = new ContentFilter( $config );

add_action( 'init', array($pContentFilter, 'addCustomRewriteTags') );
add_action( 'init', array($pContentFilter, 'addCustomRewriteRules') );
add_action( 'wp_enqueue_scripts', array($pContentFilter, 'includeScripts') );

add_filter( 'the_posts', array($pContentFilter, 'filter_the_posts') );
add_filter( 'the_content', array($pContentFilter, 'filter_the_content') );

if ( ! wp_next_scheduled( 'oo_cache_cleanup' ) ) {
	wp_schedule_event( time(), 'hourly', 'oo_cache_cleanup' );
}

add_action( 'oo_cache_cleanup', 'ooCacheCleanup' );


/**
 *
 * Callback for cron job
 *
 */

function ooCacheCleanup() {
	global $config;

	$cacheInstances = $config['cache'];

	foreach ( $cacheInstances as $pCacheInstance) {
		/* @var $cacheInstance \onOffice\SDK\Cache\onOfficeSDKCache */
		$pCacheInstance->cleanup();
	}
}