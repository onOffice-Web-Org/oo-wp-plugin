jQuery(document).ready(function ($) {
	$(document).on('click', '.duplicate-check-notify .notice-dismiss', function (event) {
		event.preventDefault();
		var data = {
			'action': 'update_duplicate_check_warning_option',
		};

		jQuery.post(duplicate_check_option_vars.ajaxurl, data);
	});
	$(document).on('click', '.active-plugin-seo .notice-dismiss', function (event) {
		event.preventDefault();
		var data = {
			'action': 'update_active_plugin_seo_option',
		};

		jQuery.post(warning_active_plugin_vars.ajaxurl, data);
	});

	$(document).on('click', '.delete-google-recaptcha-keys-button', function (event) {
		let notification = confirm_dialog_google_recaptcha_keys.notification;
		if (confirm(notification)) {
			event.preventDefault();
			const data = {
				'action': 'delete_google_recaptcha_keys'
			};

			jQuery.post(delete_google_recaptcha_keys.ajaxurl, data);
			$('input[name="onoffice-settings-captcha-sitekey"]').val('');
			$('input[name="onoffice-settings-captcha-secretkey"]').val('');
		} else {
			event.preventDefault();
		}
	});
});