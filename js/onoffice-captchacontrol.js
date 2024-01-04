var onOffice = onOffice || {};

var CaptchaCallback = function () {
	const recaptchaElements = document.querySelectorAll('.g-recaptcha');
	recaptchaElements.forEach(function (element) {
		const sitekey = element.getAttribute('data-sitekey');
		const size = element.getAttribute('data-size');
		const callback = element.getAttribute('data-callback');

		const grecaptchaId = grecaptcha.render(element, {
			'sitekey': sitekey,
			'size': size,
			'callback': callback
		});

		element.setAttribute('data-grecaptcha-id', grecaptchaId);
	});
};

(function() {
	onOffice.captchaControl = function(formElement, submitButtonElement) {
		submitButtonElement.onclick = function(event) {
			const recaptcha = formElement.querySelector('.g-recaptcha');
			const grecaptchaId = recaptcha.getAttribute('data-grecaptcha-id');
			event.preventDefault();
			if (!formElement.checkValidity() && !_isMSIE()) {
				formElement.reportValidity();
			} else {
				window.grecaptcha.execute(grecaptchaId);
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
				.attr("async", "async")
				.attr("defer", "defer")
				.attr("id", "recaptcha-script")
				.appendTo("head");
		}
	}
	$(`#onoffice-form :input, .oo-form :input`).on("focus select2:open", addRecaptchaScript);
});