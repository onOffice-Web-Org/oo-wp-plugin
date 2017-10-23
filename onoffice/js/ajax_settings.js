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
			proto.save();
		});
	};

	this.save = function() {
		var data = {};
		var values = this._getValues();
		data.action = onOffice.settings.action;
		data.nonce = onOffice.settings.nonce;
		data.values = JSON.stringify(values);

		jQuery.post(onOffice.settings.ajax_url, data, function(response) {
			alert('Got this from the server: ' + response);
		});
	};

	this._getValues = function() {
		var values = {};
		var proto = this;
		this._outerDiv.find('.onoffice-input').each(function(i, elem) {
			var inputNameFull = $(elem).attr('name');
			var inputName = inputNameFull;
			var elementValue = proto._getValueOfElement(elem);

			if (elementValue === null) {
				return;
			}

			var inputContainsArray = inputNameFull.match(/\[\]$/);

			if (inputContainsArray) { // array
				inputName = inputNameFull.replace(/\[\]$/, '');
				if (values[inputName] === undefined) {
					values[inputName] = [];
				}
				values[inputName].push(elementValue);
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

