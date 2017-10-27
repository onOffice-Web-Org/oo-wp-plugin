jQuery(document).ready(function() {
	var $ = jQuery;

	var registerEventPerForm = function(suffix) {
		suffix = suffix || '';
		var select = $('div.bulkactions select[name=action' + suffix + ']');
		var button = $(document).find('#doaction' + suffix);

		var onClick = function() {
			var message = onoffice_listviewlist_settings.confirmdialog;
			if (select.find('option:selected').val() === 'bulk_delete' && confirm(message)) {
				return true;
			}

			return false;
		};

		button.on('click', onClick);
	};

	registerEventPerForm();
	registerEventPerForm('2');
});