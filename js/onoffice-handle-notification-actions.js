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
});