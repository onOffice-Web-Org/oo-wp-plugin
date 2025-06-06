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
				if (window.grecaptcha && typeof window.grecaptcha.execute === 'function') {
					window.grecaptcha.execute(grecaptchaId);
				}
			};
		};
	};

	var _isMSIE = function() {
		var userAgent = window.navigator.userAgent;
		var iePosition = userAgent.indexOf("MSIE ");

		return iePosition > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./);
	};
})();

function waitForUC() {
    return new Promise(resolve => {
        if (window.uc?.whitelisted?.value instanceof Set) {
            resolve(true); // UC already loaded
        } else {
            resolve(false);
        }
    });
}

function hasConsent(serviceIds = []) {
    const raw = uc?.whitelisted?.value;
    if (!(raw instanceof Set)) return false;

    const allowed = Array.from(raw)
        .flatMap(entry => entry.split('|').map(id => id.trim()));

    return serviceIds.some(id => allowed.includes(id));
}

jQuery(async function ($) {
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

    const hasUC = await waitForUC();

    if (hasUC) {
        if (hasConsent(["Hko_qNsui-Q", "cfADcn3E3"])) {
            addRecaptchaScript();
        }
    } else {
        $(`#onoffice-form :input, .oo-form :input`).on(
            "focus select2:open",
            addRecaptchaScript
        );
    }
});
