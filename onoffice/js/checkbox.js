var onOffice = onOffice || {};

onOffice.checkboxAdmin = function() {
	this._configuration = {
		// view: address list
		"input[name^=oopluginaddressfieldconfig-filterable]": [
			{
				element: "input[name^=oopluginaddressfieldconfig-hidden]",
				invert: false
			}
		],

		// view: create new [contact] form
		"input[name=oopluginforms-createaddress]": [
			{
				element: "input[name=oopluginforms-checkduplicates]",
				invert: false
			},
			{
				element: "input[name=oopluginforms-newsletter]",
				invert: false
			}
		],

		// view: estate list
		"input[name^=oopluginfieldconfig-filterable]": [
			{
				element: "input[name^=oopluginfieldconfig-hidden]",
				invert: false
			},
			{
				element: "input[name^=oopluginfieldconfig-availableOptions]",
				invert: false
			}
		],

		// view: estate list
		"input[name=oopluginlistviews-random]": [
			{
				element: "select[name=oopluginlistviews-sortby]",
				invert: true
			},
			{
				element: "select[name=oopluginlistviews-sortorder]",
				invert: true
			}
		]
	};
};

onOffice.checkboxAdmin.prototype.changeCbStatus = function(topElement) {
	var instance = this;
	var toggleChild = function(receivers, mainElement) {
		for (var i in receivers) {
			var receiver = receivers[i];
			var receiverElement = mainElement.parent().parent().find(receiver.element);
			var invert = receiver.invert;
			if (receiverElement.length) {
				if (mainElement.attr('checked')) {
					if (!invert) {
						receiverElement.removeAttr('disabled');
					} else {
						receiverElement.attr('disabled', 'disabled');
						receiverElement.removeAttr('checked');
					}
				} else {
					if (!invert) {
						receiverElement.attr('disabled', 'disabled');
						receiverElement.removeAttr('checked');
					} else {
						receiverElement.removeAttr('disabled');
					}
				}
			}
		}
	};

	for (var sender in this._configuration) {
		var mainElements = $(topElement).find(sender);
		if (!mainElements.length) {
			continue;
		}

		mainElements.each(function(i) {
			var mainElement = $(mainElements[i]);
			var receivers = instance._configuration[sender];
			var callback = function() {
				toggleChild(receivers, mainElement);
			};

			mainElement.ready(callback).change(callback);
		});
	}
};

$(document).ready(function() {
	var cbAdmin = new onOffice.checkboxAdmin();
	cbAdmin.changeCbStatus(this);
});
