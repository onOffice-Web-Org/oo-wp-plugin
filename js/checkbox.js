var onOffice = onOffice || {};
onOffice.checkboxAdmin = function() {
	this._isInitialRun = true;
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
				invert: false,
				checkOnActive: true
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
			},
			{
				element: "select[name=oopluginsortbyuservalues-sortbyuservalue]",
				invert: true
			},
			{
				element: "input[name=oopluginlistviews-sortBySetting]",
				invert: true
			},
			{
				element: "select[name=oopluginlistviews-sortByUserDefinedDefault]",
				invert: true
			},
			{
				element: "select[name=oopluginlistviews-sortByUserDefinedDirection]",
				invert: true
			}
		],

		// view: form
		"input[name=oopluginforms-defaultrecipient]": [
			{
				element: "input[name=oopluginforms-recipient]",
				invert: true
			}
		]
	};
};

onOffice.checkboxAdmin.prototype.changeCbStatus = function(topElement) {
	var $ = jQuery;
	var instance = this;
	var toggleChild = function(receivers, mainElement, fromOnChange) {
		for (var i in receivers) {
			var receiver = receivers[i];
			var receiverElement = mainElement.parent().parent().find(receiver.element);
			var invert = receiver.invert;

			if (receiverElement.length) {
				var isChosen = receiverElement[0].classList.contains("chosen-select");
				if (mainElement.prop('checked')) {
					if (!invert) {
						receiverElement[0].checked = receiverElement[0].checked || (receiver.checkOnActive && (instance._isInitialRun||fromOnChange));
						receiverElement.removeAttr('disabled');
					} else {
						receiverElement.prop('disabled', 'disabled');
						receiverElement.removeAttr('checked');
						if (isChosen) {
							receiverElement.trigger("chosen:updated");
						}
					}
				} else {
					if (!invert) {
						receiverElement.prop('disabled', 'disabled');
						receiverElement.removeAttr('checked');
					} else {
						receiverElement.removeAttr('disabled');
						if (isChosen) {
							receiverElement.trigger("chosen:updated");
						}
					}
				}
			}
		}
		instance._isInitialRun = false;
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
				toggleChild(receivers, mainElement, true);
			};

			mainElement.ready(function() {
				toggleChild(receivers, mainElement, false);
			}).change(callback);
		});
	}
};

jQuery(document).ready(function() {
	var cbAdmin = new onOffice.checkboxAdmin();
	cbAdmin.changeCbStatus(this);
});
