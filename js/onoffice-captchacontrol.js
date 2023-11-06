var onOffice = onOffice || {};

var CaptchaCallback = function() {
	jQuery('.g-recaptcha').each(function(index, el) {
		var widgetId = grecaptcha.render(el, {
			'sitekey' : jQuery(el).attr('data-sitekey')
			,'size' : jQuery(el).attr('data-size')
			,'callback' : jQuery(el).attr('data-callback')
		});
		jQuery(this).attr('data-widget-id', widgetId);
	});
};
(function() {
	onOffice.captchaControl = function(formElement, submitButtonElement) {
		submitButtonElement.onclick = function(event) {
			const recaptcha = formElement.querySelector('.g-recaptcha');
			const widgetId = recaptcha.getAttribute('data-widget-id');
			event.preventDefault();
			if (!formElement.checkValidity() && !_isMSIE()) {
				formElement.reportValidity();
			} else {
				window.grecaptcha.execute(widgetId);
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
				.attr("src", "https://www.google.com/recaptcha/api.js?onload=CaptchaCallback&render=explicit")
				.attr("async", false)
				.attr("id", "recaptcha-script")
				.appendTo("head");
		}
	}
	$(`#onoffice-form :input, .oo-form :input`).on("focus select2:open", addRecaptchaScript);
});