var onOffice = onOffice || {};

(function($) {
	var paging = function(parentDiv) {
		this._pageClass = 'lead-lightbox';
		this._parentDiv = parentDiv;
		this._pages = $('#' + parentDiv + ' .lead-lightbox').length;
		this.hideAllButNth(1);
		this._formId = null;
	};


	paging.prototype.forward = function() {
		if (this.checkBeforeForward() === false) {
			return;
		}

		for (var i = 1; i < this._pages; i++) {
			var current = $('#' + this._parentDiv + ' .lead-page-' + i);
			var next = $('#' + this._parentDiv + ' .lead-page-' + (i + 1));
			if (current.is(':visible') && i < this._pages) {
				current.hide();
				next.show();
				this._getButtonBack().show();
				if (i === this._pages - 1) {
					this._getButtonNext().hide();
				}
				break;
			}

		}
	};


	paging.prototype.back = function() {
		for (var i = this._pages; i > 1; i--) {
			var current = $('#' + this._parentDiv + ' .lead-page-' + i);
			var previous = $('#' + this._parentDiv + ' .lead-page-' + (i - 1));
			if (current.is(':visible') && i > 1) {
				this._getButtonNext().show();
				current.hide();
				previous.show();
				if (i === 2) {
					this._getButtonBack().hide();
				}
				break;
			}
		}
	};


	paging.prototype.checkBeforeForward = function() {
		if (this._formId !== null) {
			var formElement = document.getElementById(this._formId);
			if (!this.forwardAllowed()) {
				if (this._isMSIE()) {
					this.reportValidityIE();
				} else {
					formElement.reportValidity();
				}
				return false;
			}
		}

		return true;
	};


	paging.prototype.forwardAllowed = function() {
		var result = true;
		var currentPage = $('#' + this._parentDiv + ' .' + this._pageClass + ':visible');
		currentPage.find('input:required').each(function(_, input) {
			result = result && input.checkValidity();
		});
		return result;
	};


	paging.prototype.hideAllButNth = function(n) {
		var allPagesSelector = this.getAllPagesSelector();
		$(allPagesSelector).each(function(_, element) {
			$(element).hide();
		});

		$('#' + this._parentDiv + ' .lead-page-' + n).show();
	};


	paging.prototype.setup = function() {
		var me = this;
		this._getButtonBack().click(function() {
			$('#TB_ajaxContent').scrollTop(0);
			me.back();
		}).hide();

		this._getButtonNext().click(function() {
			$('#TB_ajaxContent').scrollTop(0);
			me.forward();
		});

		if (this._isMSIE()) {
			var submitButton = $('#' + this._formId + ' input[type="submit"]');
			submitButton.on('click', function() {
				me.reportValidityIE();
			});
		}
	};


	paging.prototype._isMSIE = function() {
		var userAgent = window.navigator.userAgent;
		var iePosition = userAgent.indexOf("MSIE ");

		return iePosition > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./);
	};


	paging.prototype.reportValidityIE = function() {
		var page = $('#' + this._parentDiv + ' .lead-lightbox:visible');
		page.find('input:required')
			.css('border-color', '#ff5555')
			.css('background-color', '#fff0f0');
		page.find('input:required:valid')
			.css('border-color', 'inherit')
			.css('background-color', 'inherit');
	};


	paging.prototype._getButtonNext = function() {
		return $('#' + this._parentDiv + ' .leadform-forward').first();
	};


	paging.prototype._getButtonBack = function() {
		return $('#' + this._parentDiv + ' .leadform-back').first();
	};


	paging.prototype.getAllPagesSelector = function() {
		return '#' + this._parentDiv + ' .' + this._pageClass;
	};


	paging.prototype.setFormId = function(formId) {
		this._formId = formId || null;
	};


	onOffice.paging = paging;
})($);