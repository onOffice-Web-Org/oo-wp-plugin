var onOffice = onOffice || {};

(function($) {
	var paging = function(parentDiv) {
		this._parentDiv = parentDiv;
		this._pages = $('#' + parentDiv + ' .lead-lightbox').length;
		this._hideAllButFirst();
	};

	paging.prototype.forward = function() {
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

	paging.prototype._hideAllButFirst = function() {
		$('#' + this._parentDiv + ' .lead-lightbox').each(function(_, element) {
			$(element).hide();
		});

		$('#' + this._parentDiv + ' .lead-page-1').show();
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
	};


	paging.prototype._getButtonNext = function() {
		return $('#' + this._parentDiv + ' .leadform-forward').first();
	};

	paging.prototype._getButtonBack = function() {
		return $('#' + this._parentDiv + ' .leadform-back').first();
	};

	onOffice.paging = paging;
})($);