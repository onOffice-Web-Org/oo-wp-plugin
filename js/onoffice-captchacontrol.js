var onOffice = onOffice || {};

(function() {
	onOffice.captchaControl = function(formElement, submitButtonElement) {
		this._formElement = formElement;
		var self = this;
		submitButtonElement.onclick = function(event) {
			event.preventDefault();
			if (!self._formElement.checkValidity() && !_isMSIE()) {
				self._formElement.reportValidity();
			} else {
				window.grecaptcha.execute();
			};
		};
	};

	var _isMSIE = function() {
		var userAgent = window.navigator.userAgent;
		var iePosition = userAgent.indexOf("MSIE ");

		return iePosition > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./);
	};
})();

jQuery(document).ready(function ($) {
	function addRecaptchaScript() {
		if (!$("#recaptcha-script").length) {
			$("<script>")
				.attr("src", "https://www.google.com/recaptcha/api.js")
				.attr("async", false)
				.attr("id", "recaptcha-script")
				.appendTo("head");
		}
	}
	$(`#onoffice-form :input, .oo-form :input`).on("focus select2:open", addRecaptchaScript);
});