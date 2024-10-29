var onOffice = onOffice || {};
onOffice.checkboxAdmin = function() {
	this._isInitialRun = true;
	this._configuration = {
		// view: address list
		"input[name^=oopluginaddressfieldconfig-filterable]": [
			{
				element: "input[name^=oopluginaddressfieldconfig-hidden]",
				invert: false
			},
			{
				element: "input[name^=oopluginaddressfieldconfig-convertInputTextToSelectForField]",
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
			},
			{
				element: "input[name^=oopluginfieldconfig-convertTextToSelectForCityField]",
				invert: false
			}
		],

		// view: form
		"input[name=oopluginforms-defaultrecipient]": [
			{
				element: "input[name=oopluginforms-recipient]",
				invert: true
			}
		],
		"input[name^=oopluginformfieldconfig-hiddenfield]": [
			{
				element: "input[name^=oopluginformfieldconfig-required]",
				invert: true
			},
		],
		"input[name^=oopluginformfieldconfig-required]": [
			{
				element: "input[name^=oopluginformfieldconfig-hiddenfield]",
				invert: true
			}
		],

		// view: create new [task] form
		"input[name=oopluginformtaskconfig-enablecreatetask]": [
			{
				element: "select[name=oopluginformtaskconfig-responsibility]",
				invert: false
			},
			{
				element: "select[name=oopluginformtaskconfig-processor]",
				invert: false
			},
			{
				element: "select[name=oopluginformtaskconfig-type]",
				invert: false,
				required: true
			},
			{
				element: "select[name=oopluginformtaskconfig-priority]",
				invert: false
			},
			{
				element: "input[name=oopluginformtaskconfig-subject]",
				invert: false
			},
			{
				element: "textarea[name=oopluginformtaskconfig-description]",
				invert: false
			},
			{
				element: "select[name=oopluginformtaskconfig-status]",
				invert: false
			},
		],

		// view: create activity form
		"input[name=oopluginformactivityconfig-writeactivity]": [
			{
				element: "select[name=oopluginformactivityconfig-actionkind]",
				invert: false
			},
			{
				element: "select[name=oopluginformactivityconfig-actiontype]",
				invert: false
			},
			{
				element: "select[name^=oopluginformactivityconfig-characteristic]",
				invert: false
			},
			{
				element: "textarea[name=oopluginformactivityconfig-remark]",
				invert: false
			},
			{
				element: "select[name^=oopluginformactivityconfig-origincontact]",
				invert: false
			},
			{
				element: "select[name^=oopluginformactivityconfig-advisorylevel]",
				invert: false
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
			let required = receiver.required;

			if (receiverElement.length) {
				if (mainElement.prop('checked')) {
					if (!invert) {
						receiverElement[0].checked = receiverElement[0].checked || (receiver.checkOnActive && (instance._isInitialRun||fromOnChange));
						receiverElement.removeAttr('disabled');
					} else {
						receiverElement.prop('disabled', 'disabled');
						receiverElement.removeAttr('checked');
					}
					if (required) {
						receiverElement.attr('required', 'required');
					}
				} else {
					if (!invert) {
						receiverElement.prop('disabled', 'disabled');
						receiverElement.removeAttr('checked');
					} else {
						receiverElement.removeAttr('disabled');
					}
					if (required) {
						receiverElement.attr('required', 'required');
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
