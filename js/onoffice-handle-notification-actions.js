const onOfficeSaveNameMessage = (typeof onOffice_loc_settings !== 'undefined' && onOffice_loc_settings) ? onOffice_loc_settings : [];
const screenDataHandleNotification = (typeof screen_data_handle_notification !== 'undefined' && screen_data_handle_notification) ? screen_data_handle_notification : [];
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

	// TODO: remove later, when Enterprise reCAPTCHA is fully rolled out
	// Classic reCAPTCHA Delete Keys
    $(document).on('click', '.delete-google-recaptcha-keys-button', function (event) {
        let notification = confirm_dialog_google_recaptcha_keys.notification;
        if (confirm(notification)) {
            event.preventDefault();
            const data = {
                'action': 'delete_google_recaptcha_keys',
				'nonce': delete_google_recaptcha_keys.nonce
            };

            jQuery.post(delete_google_recaptcha_keys.ajaxurl, data);
            $('input[name="onoffice-settings-captcha-sitekey"]').val('');
            $('input[name="onoffice-settings-captcha-secretkey"]').val('');
        } else {
            event.preventDefault();
        }
    });

    // Enterprise reCAPTCHA Delete Keys
    $(document).on('click', '.delete-google-recaptcha-enterprise-keys-button', function (event) {
        let notification = confirm_dialog_google_recaptcha_keys.notification;
        if (confirm(notification)) {
            event.preventDefault();
            const data = {
                'action': 'delete_google_recaptcha_enterprise_keys',
				'nonce': delete_google_recaptcha_enterprise_keys.nonce
            };

            jQuery.post(delete_google_recaptcha_enterprise_keys.ajaxurl, data);
            $('input[name="onoffice-settings-captcha-enterprise-projectid"]').val('');
            $('input[name="onoffice-settings-captcha-enterprise-sitekey"]').val('');
			$('input[name="onoffice-settings-captcha-enterprise-apikey"]').val('');
        } else {
            event.preventDefault();
        }
    });

	$(document).on('click', '.oo-poststuff #send_form', function(event) {
		const $input = $(`[name="${screenDataHandleNotification.name}"]`);
		if ($input.length) {
			event.preventDefault();
			const title = ($input.val() ?? '').toString().trim();
			validateTitleBeforeSaving(title);
		}
	});
	function validateTitleBeforeSaving(title){
		if (title.length === 0) {
			showNotification(onOfficeSaveNameMessage.view_save_empty_name_message, true).insertAfter('.wp-header-end');
			return false;
		}
		const urlParams = new URLSearchParams(window.location.search);
		let pageId = urlParams.get('id');
		const data = {
			'action': screenDataHandleNotification.action,
			'name': title,
			'id': pageId
		};

		$.get(screenDataHandleNotification.ajaxurl, data, function(response) {
			if (response.success) {
				$('#onoffice-ajax').submit();
			} else {
				showNotification(onOfficeSaveNameMessage.view_save_same_name_message, true).insertAfter('.wp-header-end');
			}
		}, 'json');
	}

	function showNotification(message, checkSubmit) {
		$('html, body').animate({ scrollTop: 0 }, 1000);
		if (checkSubmit) {
			$('.notice-error-name-message').remove();
		}

		return generateNotificationNameMessage(message);
	}

	function generateNotificationNameMessage(message) {
		return $(`
			<div class="notice notice-error is-dismissible notice-error-name-message">
				<p>${message}</p>
				<button type="button" class="notice-dismiss notice-save-view"></button>
			</div>
		`);
	}
});