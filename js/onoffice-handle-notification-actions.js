const onOfficeNoticeMessage = typeof onOffice_loc_settings !== 'undefined' ? onOffice_loc_settings : [];
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

	$(document).on('click', '#send_form', function(event) {
		event.preventDefault();
		const title = $(`[name="${shortcode.name}"]`).val().trim();
		if (title.length === 0) {
			showErrorNotice(onOfficeNoticeMessage.view_notice_empty_name_message, true);
			return;
		}
		const urlParams = new URLSearchParams(window.location.search);
		let pageId = urlParams.get('id');
		const data = {
			'action': page_type.form,
			'name': title,
			'id': pageId
		};

		$.post(name_error_message.ajaxurl, data, handleResponse);
	});

	function handleResponse(response) {
		const parsedResponse = typeof response === 'string' ? JSON.parse(response) : response;
		if (parsedResponse.success === true) {
			$('#onoffice-ajax').submit();
		} else {
			showErrorNotice(onOfficeNoticeMessage.view_notice_same_name_message, true);
		}
	}

	function showErrorNotice(message, checkSubmit) {
		if (checkSubmit === true) {
			$('.notice.notice-error.is-dismissible').remove();
		}
		if ($('.notice.notice-error').length === 0) {
			$('.wp-header-end').after(`
            <div class="notice notice-error is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss notice-save-view"></button>
            </div>
        `);
		}
	}
});