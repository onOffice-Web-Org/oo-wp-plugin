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
use onOffice\WPlugin\EstateList;

$pAutoloader = new Psr4AutoloaderClass();
$pAutoloader->addNamespace( 'onOffice', __DIR__ );
$pAutoloader->addNamespace( 'onOffice\SDK', __DIR__.DIRECTORY_SEPARATOR.'SDK' );
$pAutoloader->addNamespace( 'onOffice\WPlugin', __DIR__.DIRECTORY_SEPARATOR.'plugin' );
$pAutoloader->register();

$config = array();
$config['estate'] = array();

// load user defined settings
include 'config.php';

$pEstateList = new EstateList( $config );

add_action( 'init', array($pEstateList, 'addCustomRewriteTags') );
add_action( 'init', array($pEstateList, 'addCustomRewriteRules') );

add_filter( 'the_posts', array($pEstateList, 'filter_the_posts') );
add_filter( 'the_content', array($pEstateList, 'filter_the_content') );
