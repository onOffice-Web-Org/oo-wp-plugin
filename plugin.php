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
Plugin URI: https://wpplugindoc.onoffice.de
Author: onOffice GmbH
Author URI: https://en.onoffice.com/
Description: Your connection to onOffice: This plugin enables you to have quick access to estates and forms – no additional sync with the software is needed. Consult support@onoffice.de for source code.
Version: 6.11
License: AGPL 3+
License URI: https://www.gnu.org/licenses/agpl-3.0
Text Domain: onoffice-for-wp-websites
Domain Path: /languages
*/
if ( ! defined( 'ABSPATH' ) ) exit;

const ONOFFICE_PLUGIN_VERSION = '6.11';
define('ONOFFICE_PLUGIN_BASENAME', plugin_basename( __FILE__ ));

require __DIR__ . '/vendor/autoload.php';

define('ONOFFICE_PLUGIN_DIR', __DIR__);

use DI\ContainerBuilder;
use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\Cache\CachedOutput;
use onOffice\WPlugin\Cache\CacheHandler;
use onOffice\WPlugin\Controller\AdminViewController;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeRegistrator;
use onOffice\WPlugin\Controller\DetailViewPostSaveController;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\Controller\EstateViewDocumentTitleBuilder;
use onOffice\WPlugin\Controller\RewriteRuleBuilder;
use onOffice\WPlugin\DataView\DataDetailViewCheckAccessControl;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Field\EstateKindTypeReader;
use onOffice\WPlugin\Form\CaptchaDataChecker;
use onOffice\WPlugin\Form\CaptchaEnterpriseDataChecker;
use onOffice\WPlugin\Form\Preview\FormPreviewApplicantSearch;
use onOffice\WPlugin\Form\Preview\FormPreviewEstate;
use onOffice\WPlugin\FormPostHandler;
use onOffice\WPlugin\Installer\DatabaseChangesInterface;
use onOffice\WPlugin\Installer\Installer;
use onOffice\WPlugin\PDF\PdfDocumentModel;
use onOffice\WPlugin\PDF\PdfDocumentModelValidationException;
use onOffice\WPlugin\PDF\PdfDownload;
use onOffice\WPlugin\PDF\PdfDownloadException;
use onOffice\WPlugin\Record\EstateIdRequestGuard;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderRegistrator;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Utility\Redirector;
use onOffice\WPlugin\WP\WPQueryWrapper;
use onOffice\WPlugin\ScriptLoader\IncludeFileModel;
use onOffice\WPlugin\Record\AddressIdRequestGuard;
use onOffice\WPlugin\Controller\Redirector\AddressRedirector;
use onOffice\WPlugin\Controller\Redirector\EstateRedirector;
use onOffice\WPlugin\Controller\AddressDetailUrl;

const DEFAULT_LIMIT_CHARACTER_TITLE = 60;

define('ONOFFICE_DI_CONFIG_PATH', implode(DIRECTORY_SEPARATOR, [ONOFFICE_PLUGIN_DIR, 'config', 'di-config.php']));

$pDIBuilder = new ContainerBuilder();
$pDIBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
$pDI = $pDIBuilder->build();

$pAdminViewController = new AdminViewController();
$pDetailViewPostSaveController = $pDI->get(DetailViewPostSaveController::class);

add_action('save_post', [$pDetailViewPostSaveController, 'onSavePost']);
add_action('wp_trash_post', [$pDetailViewPostSaveController, 'onMoveTrash']);

add_action('admin_menu', [$pAdminViewController, 'register_menu']);
add_action('admin_enqueue_scripts', [$pAdminViewController, 'enqueue_css']);

add_action('admin_head', [$pAdminViewController, 'role_styles']);


add_action('init', [$pAdminViewController, 'onInit']);

add_action('init', [new UserCapabilities(), 'add_plugin_capabilities_to_roles']);

add_action('admin_bar_menu', function ( $wp_admin_bar ) {
	if (is_network_admin()) {
		return;
	}

	if( current_user_can(UserCapabilities::OO_PLUGINCAP_MANAGE_VIEW_MENU) ) {
		$toolBarConfig = [
			[
				'id'    => 'onoffice',
				'title' => __('onOffice', 'onoffice-for-wp-websites'),
				'href'  => admin_url('admin.php?page=onoffice'),
				'meta'  => [ 'class' => 'onoffice' ]
			],
			[
				'id'     => 'onoffice-clear-cache',
				'title' => __('Clear onOffice cache', 'onoffice-for-wp-websites'),
				'href'   => admin_url('admin.php?action=onoffice-clear-cache'),
				'parent' => 'onoffice',
				'meta'   => [ 'class' => 'onoffice-clear-cache' ]
			],
			[
				'id'     => 'addresses',
				'title' => __('Addresses', 'onoffice-for-wp-websites'),
				'href'   => admin_url('admin.php?page=onoffice-addresses'),
				'parent' => 'onoffice',
				'meta'   => [ 'class' => 'addresses' ]
			],
			[
				'id'     => 'estates',
				'title' => __('Estates', 'onoffice-for-wp-websites'),
				'href'   => admin_url('admin.php?page=onoffice-estates'),
				'parent' => 'onoffice',
				'meta'   => [ 'class' => 'estates' ]
			],
			[
				'id'     => 'forms',
				'title' => __('Forms', 'onoffice-for-wp-websites'),
				'href'   => admin_url('admin.php?page=onoffice-forms'),
				'parent' => 'onoffice',
				'meta'   => [ 'class' => 'forms' ]
			],
			[
				'id'     => 'settings',
				'title' => __('Settings', 'onoffice-for-wp-websites'),
				'href'   => admin_url('admin.php?page=onoffice-settings'),
				'parent' => 'onoffice',
				'meta'   => [ 'class' => 'settings' ]
			]
		];

		foreach ( $toolBarConfig as $e ) {
			$wp_admin_bar->add_node($e);
		}
	};
}, 500);

add_action('admin_init', function () use ( $pDI ) {
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- strpos() doesn't execute or display the value, only checks string position.
    if ( isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"], "action=onoffice-clear-cache") !== false ) {
        $onofficeSettingsCache = get_option('onoffice-settings-duration-cache');
        $timestamp = wp_next_scheduled('oo_cache_renew');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'oo_cache_renew');
        }

        wp_schedule_event(time(), $onofficeSettingsCache, 'oo_cache_renew');
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wp_safe_redirect() validates and sanitizes the URL.
        $location = ! empty($_SERVER['HTTP_REFERER']) ? wp_unslash($_SERVER['HTTP_REFERER']) : admin_url('admin.php?page=onoffice-settings');
        update_option('onoffice-notice-cache-was-cleared', true);
        wp_safe_redirect($location);
        exit();
    }
});

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- WordPress core edit action check, no side effects.
if (is_admin() && isset($_GET['post']) && isset($_GET['action']) && sanitize_key(wp_unslash($_GET['action'])) === 'edit') {
    $post_id = absint(wp_unslash($_GET['post']));
    $post_type = get_post_type($post_id);
    if ($post_type === 'page') {
        return $pDI;
    }
}
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$pDI->get(ScriptLoaderRegistrator::class)->generate();

add_action('plugins_loaded', function() use ($pDI) {
	$pDI->get(DatabaseChangesInterface::class)->install();
});

add_action('init', function() use ($pDI) {
	$pRewriteRuleBuilder = $pDI->get(RewriteRuleBuilder::class);
	$pRewriteRuleBuilder->addCustomRewriteTags();
	$pRewriteRuleBuilder->addStaticRewriteRules();
	$pRewriteRuleBuilder->addDynamicRewriteRules();
	$pRewriteRuleBuilder->addCustomRewriteTagsForAddressDetail();
	$pRewriteRuleBuilder->addDynamicRewriteRulesForAddressDetail();
});

// This hook [wp] is one effective place to perform any high-level filtering or validation,
// following queries, but before WordPress does any routing, processing, or handling.
// https://codex.wordpress.org/Plugin_API/Action_Reference/wp
add_action('wp', [FormPostHandler::class, 'initialCheck']);

add_action('admin_enqueue_scripts', [$pAdminViewController, 'enqueue_ajax']);
add_action('admin_enqueue_scripts', [$pAdminViewController, 'enqueueExtraJs']);
add_action('oo_cache_renew', function() use ($pDI) {
	$pDI->get(CacheHandler::class)->renew();
});
add_action('admin_notices', [$pAdminViewController, 'generalAdminNoticeSEO']);
add_action('init', function() use ($pAdminViewController) {
	$pAdminViewController->disableHideMetaboxes();
}, 11);
add_action('admin_init', [$pAdminViewController, 'add_actions']);
add_action('admin_init', [CaptchaDataChecker::class, 'addHook']);
add_action('admin_init', [CaptchaEnterpriseDataChecker::class, 'addHook']);
add_action('admin_init', [$pDetailViewPostSaveController, 'getAllPost']);
add_action('plugins_loaded', function() {
	$mo_file = ONOFFICE_PLUGIN_DIR . '/languages/onoffice-for-wp-websites-'.get_locale().'.mo';
	if (file_exists($mo_file)) {
		load_textdomain('onoffice-for-wp-websites', $mo_file);
	}
	// WordPress automatically loads translations for 'onoffice-for-wp-websites' domain since WP 4.6+

	// Load custom 'onoffice' domain translations from personalized/theme folders
	$onofficePersonalizedFolderLanguages = plugin_dir_path(__DIR__) . 'onoffice-personalized/languages';
	$onofficeThemeFolderLanguages = get_stylesheet_directory() . '/onoffice-theme/languages';

	if (is_dir($onofficeThemeFolderLanguages)) {
		load_textdomain('onoffice', $onofficeThemeFolderLanguages . '/onoffice-'.get_locale().'.mo');
	} elseif (is_dir($onofficePersonalizedFolderLanguages)) {
		load_textdomain('onoffice', $onofficePersonalizedFolderLanguages . '/onoffice-'.get_locale().'.mo');
	}
});

// "Settings" link in plugins list
add_filter('plugin_action_links_'.plugin_basename(__FILE__), [$pAdminViewController, 'pluginSettingsLink']);

$pDI->get(ContentFilterShortCodeRegistrator::class)->register();

// Dynamically provide custom meta (title/description or other fields) for estate detail pages.
// Behavior depends on the option 'onoffice-settings-title-and-description':
//  - If the option is '1': we expose virtual meta fields via get_post_metadata (used by themes/SEO plugins).
//  - Else: we only override Yoast SEO's computed <title> via 'wpseo_title'.
if (get_option('onoffice-settings-title-and-description') === '1')
{
    add_filter('get_post_metadata', function($value, $object_id, $meta_key) use ($pDI) {

        // Obtain current detail view configuration.
        $pDataDetailViewHandler = $pDI->get( DataDetailViewHandler::class );
        $pDetailView            = $pDataDetailViewHandler->getDetailView();

        // All pages (including translated / duplicated ones) that contain the detail shortcode.
        $detail_page_ids = $pDetailView->getPageIdsHaveDetailShortCode();

		// Fallback: Use last saved page ID.
        if (!is_array($detail_page_ids) || empty($detail_page_ids)) {
            $detail_page_ids = [$pDetailView->getPageId()];
        }

        // Only serve dynamic meta on pages that actually host the detail shortcode.
        if (in_array((int)$object_id, $detail_page_ids, true)) {

            $fieldsDetail    = $pDetailView->getFields(); // Field list configured for the detail view.
            $list_meta_keys  = [];                        // Maps meta key => raw field name.
            $limitEllipsis   = '';                        // Length extracted from keys like onoffice_ellipsis{N}_FIELD.

            // Build mapping of allowed meta keys that we can answer.
            foreach ($fieldsDetail as $field) {

                // Support truncated variants: onoffice_ellipsis{LEN}_{field}
                if (strpos($meta_key, 'ellipsis') && strpos($meta_key, $field)) {
                    preg_match("/^.*ellipsis(.+?)_.*$/i", $meta_key, $matches);
                    if (count($matches) !== 0) {
                        $limitEllipsis = $matches[1]; // Extract numeric length from requested key.
                    }
                    $list_meta_keys["onoffice_ellipsis".$limitEllipsis."_".$field] = $field;
                } else {
                    // Full (untruncated) variant: onoffice_{field}
                    $list_meta_keys["onoffice_".$field] = $field;
                }
            }

            // If the requested meta key matches a dynamic onOffice field, compute and return it.
            if (isset($list_meta_keys[$meta_key])) {
                return customFieldCallback($pDI, $list_meta_keys[$meta_key], (int)$limitEllipsis, $meta_key);
            }

            // If key not handled, fall through to return null (let WP continue default handling).
        } else {
            // Not a detail view page -> do not interfere.
            return null;
        }

        // Default: do nothing (so WordPress continues its normal meta retrieval).
    }, 1, 3);

} else {

    // Option disabled: Only adjust the final title on estate detail pages.
     add_filter('document_title_parts', function ($title) use ($pDI) {

        // Only process when an estate detail view is active (query var injected by rewrite rules).
        if (!get_query_var('estate_id')) {
            return $title;
        }

        try {
            /** @var \onOffice\WPlugin\Controller\EstateViewDocumentTitleBuilder $builder */
            $builder = $pDI->get(\onOffice\WPlugin\Controller\EstateViewDocumentTitleBuilder::class);

            // Pass the title parts array to the builder.
            $result = $builder->buildDocumentTitle($title);

            // Extract the modified title.
            $newTitle = $result['title'] ?? $title['title'] ?? '';

            // Enforce character limit (fallback to word boundary).
            if (!empty($newTitle) && strlen($newTitle) > DEFAULT_LIMIT_CHARACTER_TITLE) {
                $short = substr($newTitle, 0, DEFAULT_LIMIT_CHARACTER_TITLE);
                if (substr($newTitle, DEFAULT_LIMIT_CHARACTER_TITLE, 1) !== ' ') {
                    $short = substr($short, 0, strrpos($short, ' '));
                }
                $result['title'] = $short;
            }

            return $result;

        } catch (\Throwable $e) {
            // Fail safe: log error and return original title parts.
            error_log('onoffice document_title_parts error: '.$e->getMessage()); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Fail-safe error logging for title generation.
            return $title;
        }
    }, 20, 1);
}

// Return title custom by custom field onOffice
function getRestrictLength( $limitEllipsis, $title ,$ellipsis = '…' ) {
	if ( empty( $limitEllipsis ) ) {
		return $title;
	} else {
		$newValue = substr( $title, 0, $limitEllipsis + 1 );

		$value = strlen( $title ) > $limitEllipsis ? trim( mb_substr( $title, 0,
				strrpos( $newValue, ' ' ) ) ) . $ellipsis : $title;

		return ( $value != $ellipsis ) ? $value : '';
	}
}

// Return title custom by custom field onOffice
function customFieldCallback( $pDI, $format, $limitEllipsis, $meta_key ) {
	$title = $pDI->get( EstateViewDocumentTitleBuilder::class )->buildDocumentTitleField( $format );
	if ( ! strpos( $meta_key, 'ellipsis' ) ) {
		return $title;
	} else {
		return getRestrictLength( $limitEllipsis, $title );
	}
}


add_filter('wpml_ls_language_url', function($url, $data) use ($pDI) {
	$pWPQueryWrapper = $pDI->get(WPQueryWrapper::class);
	$estateId = (int) $pWPQueryWrapper->getWPQuery()->get('estate_id', 0);
	$addressId = (int) $pWPQueryWrapper->getWPQuery()->get('address_id', 0);

	if (!empty($estateId)) {
		/** @var EstateIdRequestGuard $pEstateIdGuard */
		$pEstateIdGuard = $pDI->get(EstateIdRequestGuard::class);
		$pEstateDetailUrl = $pDI->get(EstateDetailUrl::class);
		$oldUrl = $pDI->get(Redirector::class)->getCurrentLink();
		return $pEstateIdGuard->createEstateDetailLinkForSwitchLanguageWPML($url, $estateId, $pEstateDetailUrl, $oldUrl, $data['default_locale']);
	}

	if (!empty($addressId)) {
		/** @var AddressIdRequestGuard $pAddressIdGuard */
		$pAddressIdGuard = $pDI->get(AddressIdRequestGuard::class);
		$pEstateDetailUrl = $pDI->get(AddressDetailUrl::class);
		$oldUrl = $pDI->get(Redirector::class)->getCurrentLink();
		return $pAddressIdGuard->createAddressDetailLinkForSwitchLanguageWPML($url, $addressId, $pEstateDetailUrl, $oldUrl, $data['default_locale']);
	}
	return $url;
}, 10, 2);

register_activation_hook(__FILE__, [Installer::class, 'install']);
register_deactivation_hook(__FILE__, [Installer::class, 'deactivate']);
register_uninstall_hook(__FILE__, [Installer::class, 'deinstall']);

function custom_cron_schedules($schedules) {
	if(!isset($schedules['ten_minutes'])) {
		$schedules['ten_minutes'] = array(
			'interval' => 60 * 10,
			'display' => __('10 minutes', 'onoffice-for-wp-websites')
		);
	}
	if(!isset($schedules['thirty_minutes'])) {
		$schedules['thirty_minutes'] = array(
			'interval' => 60 * 30,
			'display' => __('30 minutes', 'onoffice-for-wp-websites')
		);
	}
	if(!isset($schedules['six_hours'])) {
		$schedules['six_hours'] = array(
			'interval' => 60 * 60 * 6,
			'display'  => __('6 hours', 'onoffice-for-wp-websites')
		);
	}

	return $schedules;
}

add_filter('cron_schedules', 'custom_cron_schedules');

if (!wp_next_scheduled('oo_cache_renew')) {
	$onofficeSettingsCache = get_option('onoffice-settings-duration-cache');
	wp_schedule_event(time(), $onofficeSettingsCache, 'oo_cache_renew');
}

add_action('update_option_onoffice-settings-duration-cache', function($old_value, $value) {
	if ($old_value !== $value) {
		$timestamp = wp_next_scheduled('oo_cache_renew');
		if ($timestamp) {
			wp_unschedule_event($timestamp, 'oo_cache_renew');
		}

		wp_schedule_event(time(), $value, 'oo_cache_renew');
	}
}, 10, 2);

add_filter('query_vars', function(array $query_vars): array {
    $query_vars []= 'onoffice_estate_type_json';
    $query_vars []= 'onoffice_applicant_search_preview';
    $query_vars []= 'onoffice_estate_preview';
    $query_vars []= 'document_pdf';
    $query_vars []= 'preview_name';
    $query_vars []= 'nonce';
    return $query_vars;
});

add_action('parse_request', function(WP $pWP) use ($pDI) {
	if (isset($pWP->query_vars['document_pdf'])) {
		try {
			$pPdfDocumentModel = new PdfDocumentModel($pWP->query_vars['estate_id'] ?? 0, $pWP->query_vars['view'] ?? '');
			/* @var $pPdfDownload PdfDownload */
			$pPdfDownload = $pDI->get(PdfDownload::class);
			$pPdfDownload->download($pPdfDocumentModel);
		} catch (PdfDocumentModelValidationException $pEx) {
			$pWP->handle_404();
			include(get_query_template('404'));
		} catch (PdfDownloadException $pEx) {
			$pWP->handle_404();
			include(get_query_template('404'));
		}
		die();
	}
});

$pEstateRedirection = apply_filters('oo_is_detailpage_redirection', true);

add_action('parse_request', function(WP $pWP) use ($pDI, $pEstateRedirection) {
	$estateId = $pWP->query_vars['estate_id'] ?? '';
	/** @var EstateIdRequestGuard $pEstateIdGuard */
	$pEstateIdGuard = $pDI->get(EstateIdRequestGuard::class);
	/** @var DataDetailViewHandler $pDataDetailViewHandler */
	$pDataDetailViewHandler = $pDI->get( DataDetailViewHandler::class);

	if ($estateId !== '') {
		$estateId = (int)$estateId;
		/** @var DataDetailViewCheckAccessControl $pDataDetailViewCheckAccessControl */
		$pDataDetailViewCheckAccessControl = $pDI->get(DataDetailViewCheckAccessControl::class);
		$restrictAccessChecker = $pDataDetailViewCheckAccessControl->checkRestrictAccess($estateId);

		if ($estateId === 0 || $restrictAccessChecker|| !$pEstateIdGuard->isValid($estateId)) {
			$pWP->handle_404();
			include(get_query_template('404'));
			die();
		}
		$pEstateIdGuard->estateDetailUrlChecker( $estateId, $pDI->get( EstateRedirector::class ), $pEstateRedirection);
	}
});

$pAddressRedirection = apply_filters('oo_is_address_detail_page_redirection', true);

add_action('parse_request', function(WP $pWP) use ($pDI, $pAddressRedirection) {
	$addressId = $pWP->query_vars['address_id'] ?? '';
	/** @var AddressIdRequestGuard $pAddressIdGuard */
	$pAddressIdGuard = $pDI->get(AddressIdRequestGuard::class);

	if ($addressId !== '') {
		$addressId = (int)$addressId;

		if ($addressId === 0 || !$pAddressIdGuard->isValid($addressId)) {
			$pWP->handle_404();
			include(get_query_template('404'));
			die();
		}
		$pAddressIdGuard->addressDetailUrlChecker($addressId, $pDI->get(AddressRedirector::class), $pAddressRedirection);
	}
});

add_action('parse_request', static function(WP $pWP) use ($pDI) {
	if (isset($pWP->query_vars['onoffice_estate_type_json'])) {
		$content = wp_json_encode($pDI->get(EstateKindTypeReader::class)->read());
		/** @var CachedOutput $pCachedOutput */
		$pCachedOutput = $pDI->get(CachedOutput::class);
		header('Content-Type: application/json; charset='.get_option('blog_charset'));
		$pCachedOutput->outputCached($content, 60 * 60 * 24 * 2);
		exit;
	}
});

add_action('parse_request', function(WP $pWP) use ($pDI) {
	if (isset($pWP->query_vars['onoffice_estate_preview'], $pWP->query_vars['preview_name']) &&
		wp_verify_nonce($pWP->query_vars['nonce'], 'onoffice-estate-preview') === 1) {
		wp_send_json($pDI->get(FormPreviewEstate::class)
			->preview((string)$pWP->query_vars['preview_name']));
	}
});

add_action('parse_request', function(WP $pWP) use ($pDI) {
	if (isset($pWP->query_vars['onoffice_applicant_search_preview'], $pWP->query_vars['preview_name']) &&
		wp_verify_nonce($pWP->query_vars['nonce'], 'onoffice-applicant-search-preview') === 1) {
		wp_send_json($pDI->get(FormPreviewApplicantSearch::class)
			->preview((string)$pWP->query_vars['preview_name']));
	}
});

add_filter('set-screen-option', function ($status, $option, $value) {
	$pagination_screen_option = array(
		"onoffice_address_listview_per_page",
		"onoffice_estate_listview_per_page",
		"onoffice_forms_forms_per_page",
		"onoffice_estate_units_listview_per_page"
	);

	if (in_array($option, $pagination_screen_option)) {
		return $value;
	}
	return $status;
}, 10, 3);

function update_duplicate_check_warning_option()
{
	update_option('onoffice-duplicate-check-warning', 0);
	echo true;
	wp_die();
}

function update_status_close_action_button_option()
{
	update_option('onoffice-click-button-close-action', 1);
	echo true;
	wp_die();
}

function delete_google_recaptcha_keys()
{
	if (!check_ajax_referer('delete_google_recaptcha_keys', 'nonce', false)) {
        wp_send_json_error('Invalid nonce', 403);
    }
    update_option('onoffice-settings-captcha-sitekey', '');
    update_option('onoffice-settings-captcha-secretkey', '');
    echo true;
    wp_die();
}

function delete_google_recaptcha_enterprise_keys()
{
	if (!check_ajax_referer('delete_google_recaptcha_enterprise_keys', 'nonce', false)) {
        wp_send_json_error('Invalid nonce', 403);
    }
    update_option('onoffice-settings-captcha-enterprise-projectid', '');
    update_option('onoffice-settings-captcha-enterprise-sitekey', '');
    update_option('onoffice-settings-captcha-enterprise-apikey', '');
    echo true;
    wp_die();
}

add_action('wp_ajax_update_active_plugin_seo_option', 'update_status_close_action_button_option');
add_action('wp_ajax_update_duplicate_check_warning_option', 'update_duplicate_check_warning_option');
add_action('wp_ajax_delete_google_recaptcha_keys', 'delete_google_recaptcha_keys');
add_action('wp_ajax_delete_google_recaptcha_enterprise_keys', 'delete_google_recaptcha_enterprise_keys');

add_action('wp', function () {
	if (!get_option('add-detail-posts-to-rewrite-rules')) {
		flush_rewrite_rules(false);
		delete_option('add-detail-posts-to-rewrite-rules');
	}
});

add_action('admin_notices', function () {
    if (get_option('onoffice-notice-cache-was-cleared') == true) {
        $class = 'notice notice-success is-dismissible';
        $message = __('The cache was cleaned.', 'onoffice-for-wp-websites');

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        update_option('onoffice-notice-cache-was-cleared', false);
    }
});

add_filter('script_loader_tag', 'filter_script_loader_tag', 10, 2);
function filter_script_loader_tag($tag, $handle) {
	$attributes = [IncludeFileModel::LOAD_DEFER, IncludeFileModel::LOAD_ASYNC];
	foreach ($attributes as $attr) {
		if (!wp_scripts()->get_data($handle, $attr)) continue;
		if (!preg_match(":\s$attr(=|>|\s):", $tag))
			$tag = preg_replace(':(?=></script>):', " $attr", $tag, 1);
		break;
	}
	return $tag;
}


return $pDI;
