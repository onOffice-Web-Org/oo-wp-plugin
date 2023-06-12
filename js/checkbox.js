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

		// view: form
		"input[name=oopluginforms-defaultrecipient]": [
			{
				element: "input[name=oopluginforms-recipient]",
				invert: true
			}
		]
	};
	this._configurationForDefaultValue = {
		"input[name^=oopluginfieldconfig-filterable]": [
			{
				element: "input[name^=defaultvalue-lang]",
				invert: false
			},
			{
				element: "select[name^=language-language]",
				invert: false
			},
			{
				element: "input[name^=oopluginfieldconfigestatedefaultsvalues-value]",
				invert: false
			},
		],
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

	const toggleChildForDefaultValue = function (receivers, mainElement, fromOnChange) {
		for (let i in receivers) {
			const receiver = receivers[i];
			const receiverElement = mainElement.parent().parent().find(receiver.element);
			const invert = receiver.invert;

			if (receiverElement.length) {
				const radio = mainElement.parent().parent().find('.onoffice-input-radio')[0];
				const deleteButtonLang = mainElement.parent().parent().find('.deleteButtonLang')[0];
				const languageSelect = mainElement.parent().parent().find('select[name="language-language"]')[0];

				if (mainElement.prop('checked')) {
					if (!invert) {
						receiverElement[0].checked = receiverElement[0].checked || (receiver.checkOnActive && (instance._isInitialRun || fromOnChange));
						receiverElement.prop('readonly', false).removeClass('inactive');
						if (radio) {
							$(radio).removeClass('inactive-radio');
						}
						if (deleteButtonLang) {
							deleteButtonLang.style.pointerEvents = '';
						}
						if (languageSelect) {
							languageSelect.disabled = false;
						}
						mainElement.removeClass('highlighted');
						receiverElement.off('click');
					} else {
						if (radio) {
							$(radio).addClass('inactive-radio');
						} else {
							receiverElement.prop('readonly', true).addClass('inactive');
						}
					}
				} else {
					if (!invert) {
						if (radio) {
							$(radio).addClass('inactive-radio');
						}
						receiverElement.prop('readonly', true);
						receiverElement.addClass('inactive').on('click', function () {
							mainElement[0].scrollIntoView({behavior: 'smooth'});
							mainElement.addClass('highlighted');
							return false;
						});
						if (deleteButtonLang) {
							deleteButtonLang.style.pointerEvents = 'none';
						}
						if (languageSelect) {
							languageSelect.disabled = true;
						}
					} else {
						receiverElement.prop('readonly', false);
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
			const defaultValueReceivers = instance._configurationForDefaultValue[sender];
			var callback = function() {
				toggleChild(receivers, mainElement, true);
				toggleChildForDefaultValue(defaultValueReceivers, mainElement, true);
			};

			mainElement.ready(function() {
				toggleChild(receivers, mainElement, false);
				toggleChildForDefaultValue(defaultValueReceivers, mainElement, false);
			}).change(callback);
		});
	}
};

jQuery(document).ready(function() {
	var cbAdmin = new onOffice.checkboxAdmin();
	cbAdmin.changeCbStatus(this);
});
