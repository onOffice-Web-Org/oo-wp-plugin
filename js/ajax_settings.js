var onOffice = onOffice || {};
onOffice.settings = onOffice_loc_settings;
const LIST_SCREEN_RELOAD = ["admin_page_onoffice-editlistview","admin_page_onoffice-editform"];
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
		var current_screen = onOffice.settings.current_screen;
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
			var getUrl = window.location.href;

			var getUrlPageEdit = getUrl.split( '&' );
			var urlPageEdit = '';
			if (responseCode === true) {
				$('#onoffice-notice-wrapper').append('<div class="notice notice-success is-dismissible"><p>' +
					message + '</p></div>');

				onOffice.sortByUserSelection();
				onOffice.generateSortByUserDefinedDefault();
				if (getUrlPageEdit.length != 0 && LIST_SCREEN_RELOAD.includes(current_screen))
				{
					urlPageEdit = getUrlPageEdit[0] + "&id=" + onOffice.settings.record_id;
					window.location.replace(urlPageEdit);
				}
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
			if (inputName === "dummy_key" || inputName.includes("dummy_key")) {
			    return;
            }
			var elementValue = proto._getValueOfElement(elem);

			if (elementValue === null) {
				return;
			}

            var inputContainsArray = /\[\]$/.test(inputNameFull);
            var inputContainsObject = /\[[^\]]+\]/.test(inputNameFull);

            if (inputContainsArray) {
                inputName = inputNameFull.match(/(.+)\[\]/)[1];
                var previousEntry = proto._getValueOfNestedObjectByKeys(inputNameFull, values) || [];

                if (Array.isArray(previousEntry)) {
                    previousEntry.push(elementValue);
                } else {
                    // get highest numeric key
                    var highestKey = Object.keys(previousEntry).reduce(function(a, b) {
                        return parseInt(a,10) > parseInt(b,10) ? a : b
                    });

                    if (isNaN(highestKey)) {
                        highestKey = -1;
                    }
                    previousEntry[highestKey+1] = elementValue;
                }

                elementValue = previousEntry;
            }

            if (inputContainsObject) {
                var nestedParameters = proto._getNestedParameterNamesOfString(inputNameFull);
                var recentObject = values;

                nestedParameters.forEach(function(parameter, i) {
                    if (i !== (nestedParameters.length - 1)) {
                        recentObject[parameter] = recentObject[parameter] || {};
                        recentObject = recentObject[parameter];
                    } else {
                        recentObject[parameter] = elementValue;
                    }
                });
            } else {
                values[inputName] = elementValue;
            }
        });

		return values;
	};

	this._getNestedParameterNamesOfString = function(string) {
        var inputNameArray = string.match(/([^\[]+)/)[0];

        var nestedParameterNameMatch = string.match(/\[(.+)\]/);
        var result = [inputNameArray];

        if (nestedParameterNameMatch && nestedParameterNameMatch[1]) {
            var nestedParameterName = nestedParameterNameMatch[1];
            result = result.concat(nestedParameterName.split('][').filter(function(value) {
                return value !== "";
            }));
        }
        return result;
    };

	this._getValueOfNestedObjectByKeys = function(nestedName, object) {
        var nestedParameters = this._getNestedParameterNamesOfString(nestedName);
        var recentObject = object;
        nestedParameters.forEach(function(parameter, i) {
            if (i !== (nestedParameters.length - 1)) {
                recentObject[parameter] = recentObject[parameter] || {};
                recentObject = recentObject[parameter];
            } else {
                recentObject = recentObject[parameter] || [];
            }
        });
        return recentObject;
    };

	this._getValueOfElement = function(element) {
        let value = null;
        switch (element.type) {
			case 'radio':
			case 'checkbox':
				if (element.checked) {
					value = element.value;
				}
				break;
			case 'select-multiple':
				value = Array();
				for (var i = 0; i < element.options.length; i++) {
					if (element.options[i].selected ==true){
						value.push(element.options[i].value);
					}
				}
				break;
			default:
				value = element.value;
		}
		return value;
	};
}).call(onOffice.ajaxSaver.prototype);

