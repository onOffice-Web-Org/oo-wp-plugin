var onOffice = onOffice || {};
onOffice.settings = onOffice_loc_settings;

onOffice.ajaxSaver = function(outerDiv) {
	if (typeof $ === 'undefined') {
		$ = jQuery;
	}
	this._outerDiv = $('#' + outerDiv);
};

(function() {
	this.register = function() {
		var proto = this;
		this._outerDiv.find('#send_ajax').on('click', function() {
			window.scrollTo(0, 0);
			proto.save();
		});
	};

	this.save = function() {
		var data = {};
		var values = this._getValues();
		data.action = onOffice.settings.action;
		data.nonce = onOffice.settings.nonce;
		data.values = JSON.stringify(values);
		var mergeElement = onOffice.settings.merge;
		for (var i in mergeElement) {
			var newKey = mergeElement[i];
			data[newKey] = onOffice.settings[newKey];
		}

		jQuery.post(onOffice.settings.ajax_url, data, function(response) {
			var responseCode, responseMessageKey;

			try {
				var jsonResponse = JSON.parse(response);
				responseCode = jsonResponse.result;
				responseMessageKey = jsonResponse.messageKey;
				onOffice.settings.record_id = jsonResponse.record_id;
			} catch (e) {
				responseCode = false;
			}

			var message = onOffice.settings[responseMessageKey];
			if (responseCode === true) {
				$('#onoffice-notice-wrapper').append('<div class="notice notice-success is-dismissible"><p>' +
					message + '</p></div>');

				onOffice.sortByUserSelection();
				onOffice.generateSortByUserDefinedDefault();

			} else {
				$('#onoffice-notice-wrapper').append('<div class="notice notice-error is-dismissible"><p>' +
					message + '</p></div>');
			}
			$(document).trigger('wp-updates-notice-added');
		});
	};

	this._getValues = function() {
		var values = {};
		var proto = this;
		this._outerDiv.find('.onoffice-input:not([data-onoffice-ignore=true])').each(function(i, elem) {
			var inputNameFull = $(elem).attr('name');
			var inputName = inputNameFull;
			var elementValue = proto._getValueOfElement(elem);

			if (elementValue === null) {
				return;
			}

			var inputContainsArray = /\[\]$/.test(inputNameFull);
			var inputContainsObject = /\[[^\]]*\]$/.test(inputNameFull);

			if (inputContainsArray) { // array
				inputName = inputNameFull.replace(/\[\]$/, '');
				if (values[inputName] === undefined) {
					values[inputName] = [];
				}
				values[inputName].push(elementValue);
			} else if (inputContainsObject) {
				var inputNameArray = inputNameFull.match(/([^\[]+)/)[0];
				if (values[inputNameArray] === undefined) {
					values[inputNameArray] = {};
				}
				var nestedParameterName = inputNameFull.match(/\[(.+)\]$/)[1];
				var nestedParameters = nestedParameterName.split('][');
				var recentObject = values[inputNameArray];

				for (var i in nestedParameters) {
					if (i !== ((nestedParameters.length - 1) + "")) {
						recentObject[nestedParameters[i]] = recentObject[nestedParameters[i]] || {};
						recentObject = recentObject[nestedParameters[i]];
					} else {
						recentObject[nestedParameters[i]] = elementValue;
					}
				}
			} else {
				values[inputName] = elementValue;
			}
		});

		return values;
	};

	this._getValueOfElement = function(element) {
		var value = null;
		switch ($(element).attr('type')) {
			case 'radio':
				if ($(element).attr('selected')) {
					value = $(element).val();
				}
				break;
			case 'checkbox':
				if ($(element).attr('checked')) {
					value = $(element).val();
				}
				break;
			default:
				value = $(element).val();
		}
		return value;
	};
}).call(onOffice.ajaxSaver.prototype);

