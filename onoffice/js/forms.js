$(document).ready(function() {
	var receiver = $(document).find('body.admin_page_onoffice-editform input[name=oopluginforms-checkduplicates]');
	var callback = function() {
		toggleChild(receiver);
	};

	var elementCreateAddress = $(this).find('body.admin_page_onoffice-editform input[name=oopluginforms-createaddress]');
	elementCreateAddress.ready(callback).change(callback);

	var toggleChild = function(receiver) {
		if (receiver.length) {
			if (elementCreateAddress.attr('checked')) {
				receiver.removeAttr('disabled');
			} else {
				receiver.attr('disabled', 'disabled');
				receiver.removeAttr('checked');
			}
		}
	};
});