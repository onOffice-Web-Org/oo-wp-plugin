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

		this._displaySpan = document.createElement('span');
		this._element.appendChild(this._displaySpan);
		this.refreshlabel();
	};

	multiselect.prototype.show = function() {
		this._getChildDiv('onoffice-multiselect-popup').hidden = false;
		this.clearLabel();
	};

	multiselect.prototype.hide = function() {
		this._getChildDiv('onoffice-multiselect-popup').hidden = true;
		this.refreshlabel();
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


	multiselect.prototype.refreshlabel = function() {
		var selection = this._getSelection();
		var labels = [];

		for (var i in selection) {
			var key = selection[i];
			if (this._options[key] !== undefined) {
				labels.push(this._options[key]);
			}
		}

		this._displaySpan.textContent = labels.join(', ');
	};


	multiselect.prototype.clearLabel = function() {
		this._displaySpan.textContent = '';
	};


	multiselect.prototype._getSelection = function() {
		var childNodes = this._getChildDiv('onoffice-multiselect-popup').childNodes;
		var elements = [].slice.call(childNodes);
		var inputs = elements.filter(element =>
			element.nodeName === 'INPUT' && element.type === 'checkbox' && element.checked);
		return inputs.map(element => element.value);
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


