$(document).ready(function() {
	var configuration = {
		// view: create new [contact] form
		"input[name=oopluginforms-createaddress]": [
			"input[name=oopluginforms-checkduplicates]"
		],

		// view: estate list
		"input[name^=oopluginfieldconfig-filterable]": [
			"input[name^=oopluginfieldconfig-hidden]"
		]
	};

	for (var sender in configuration) {
		var mainElements = $(this).find(sender);
		if (!mainElements.length) {
			continue;
		}

		mainElements.each(function(i) {
			var mainElement = $(mainElements[i]);
			var receivers = configuration[sender];
			var callback = function() {
				toggleChild(receivers, mainElement);
			};

			mainElement.ready(callback).change(callback);
		});
	}


	var toggleChild = function(receivers, mainElement) {
		for (var i in receivers) {
			var receiver = receivers[i];
			var receiverElement = mainElement.parent().parent().find(receiver);
			if (receiverElement.length) {
				if (mainElement.attr('checked')) {
					receiverElement.removeAttr('disabled');
				} else {
					receiverElement.attr('disabled', 'disabled');
					receiverElement.removeAttr('checked');
				}
			}
		}
	};
});