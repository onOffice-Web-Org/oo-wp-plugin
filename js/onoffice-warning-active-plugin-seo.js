jQuery(document).ready(function ($) {
	$(document).on('click', '.active-plugin-seo .notice-dismiss', function (event) {
		event.preventDefault();
		var data = {
			'action': 'update_active_plugin_seo_option',
		};
		// alert(data);
		jQuery.post(warning_active_plugin_vars.ajaxurl, data);
	});

});