var onOffice = onOffice || {};
(() => {
	function multiselect(element, options, preselected) {
		this._element = typeof(element) === 'string' ? document.getElementById(element) : element;
		this._options = options || {};
		this._name = this._element.getAttribute('data-name');
		this._load(preselected);
	};

	multiselect.prototype._load = function(preselected) {
		var divPopup = document.createElement('div');
		divPopup.hidden = true;
		divPopup.className = 'onoffice-multiselect-popup';

		var output = '';
		preselected = preselected || [];

		for (var key in this._options) {
			var value = this._options[key];
			var checked = preselected.indexOf(key) >= 0 ? ' checked' : '';

			output += '<input type="checkbox" name=' + this._name + '[] value="' + key +
				'" ' + checked + '>' + value + '<br>';
		}

		divPopup.innerHTML = output;

		var button = document.createElement('input');
		button.type = 'button';
		button.value = 'OK';
		button.onclick = () => this.hide();

		divPopup.appendChild(button);

		this._element.appendChild(divPopup);

		var divInputs = document.createElement('div');
		divInputs.className = 'onoffice-multiselect-inputs';

		this._element.appendChild(divInputs);
	};

	multiselect.prototype.toggle = function() {
		var element = this._getChildDiv('onoffice-multiselect-popup');
		element.hidden = !element.hidden;
	};

	multiselect.prototype.show = function() {
		this._getChildDiv('onoffice-multiselect-popup').hidden = false;
	};

	multiselect.prototype.hide = function() {
		this._getChildDiv('onoffice-multiselect-popup').hidden = true;
	};

	multiselect.prototype._getChildDiv = function(className) {
		var childnodes = Array.prototype.slice.call(this._element.childNodes);
		var divs = childnodes.filter(element =>
			element.nodeName.toLowerCase() === 'div' && element.className === className);

		if (divs.length > 0) {
			return divs[0];
		}

		throw new Error('child div not found');
	};

	onOffice.multiselect = multiselect;

})();

(function () {
	var divs = document.getElementsByClassName('multiselect');
	var divsArray = [].slice.call(divs);
	for (var i in divsArray) {
		var element = divsArray[i];
		var values = {};
		var presetValues = [];

		try {
			valuesString = element.getAttribute('data-values');
			values = JSON.parse(valuesString);
			presetString = element.getAttribute('data-selected');
			if (presetString !== null) {
				presetValues = JSON.parse(presetString);
			}
		} catch (Error) {}

		var instance = new onOffice.multiselect(element, values, presetValues);
		var subElements = [].slice.call(element.children);
		var editButtonArray = subElements.filter(element =>
			element.className === 'onoffice-multiselect-edit');
		var button = editButtonArray.pop();
		button.onclick = () => instance.show();
	}
})();


