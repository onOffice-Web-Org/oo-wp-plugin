<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Superchat;

use function add_action;
use function esc_url;
use function function_exists;
use function get_field;
use function in_array;
use function is_array;
use function is_front_page;
use function preg_replace;
use function rawurlencode;
use function trim;

class SuperchatWidgetInjector
{
	private const USERCENTRICS_DPS_ID = 'vSIINyq1z';

	public static function bootstrap(): void
	{
		add_action('wp_footer', [self::class, 'inject'], 1000);
	}

	public static function inject(): void
	{
		if (!function_exists('get_field')) {
			return;
		}

		$config = get_field('third_parties', 'option');
		if (!is_array($config) || !isset($config['superchat'])) {
			return;
		}

		$superchat = $config['superchat'];
		if (!is_array($superchat)) {
			return;
		}

		$widgetSettings = is_array($superchat['widget_settings'] ?? null) ? $superchat['widget_settings'] : [];
		$enabled = (bool)($widgetSettings['enabled'] ?? false);
		if (!$enabled) {
			return;
		}

		$key = trim((string)($superchat['application_key'] ?? ''));
		$key = preg_replace('/\s+/', '', $key);
		if ($key === '') {
			return;
		}

		$visibility = (string)($widgetSettings['visibility'] ?? 'global');
		if ($visibility === 'home' && !is_front_page()) {
			return;
		}

		$src = esc_url('https://widget.superchat.de/snippet.js?applicationKey=' . rawurlencode($key));
		$srcJson = wp_json_encode($src);
		
		// Load via JavaScript to check Usercentrics consent
		echo '<script>
		(function() {
			const SUPERCHAT_ID = "vSIINyq1z";
			const SUPERCHAT_SRC = ' . $srcJson . ';
			
			function loadSuperchat() {
				const script = document.createElement("script");
				script.src = SUPERCHAT_SRC;
				script.async = true;
				document.body.appendChild(script);
			}
			
			function checkConsent() {
				if (!window.uc || !window.uc.whitelisted) {
					setTimeout(checkConsent, 500);
					return;
				}
				
				const whitelistedSet = window.uc.whitelisted.value;
				if (!whitelistedSet) {
					return;
				}
				
				if (whitelistedSet.has(SUPERCHAT_ID)) {
					loadSuperchat();
				}
			}
			
			if (window.uc && window.uc.whitelisted) {
				window.uc.whitelisted.onChange(checkConsent);
			}
			
			checkConsent();
		})();
		</script>';
	}
}
