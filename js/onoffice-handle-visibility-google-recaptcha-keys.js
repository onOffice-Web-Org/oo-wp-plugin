jQuery(document).ready(function ($) {
	function showOrHideGoogleRecaptchaKey(inputSelector, toggleSelector) {
		const inputElement = $(inputSelector);
		const toggleElement = $(toggleSelector);

		if (inputElement.attr('type') === 'password') {
			inputElement.attr('type', 'text');
			toggleElement.removeClass('dashicons-visibility').addClass('dashicons-hidden');
		} else if (inputElement.attr('type') === 'text') {
			inputElement.attr('type', 'password');
			toggleElement.removeClass('dashicons-hidden').addClass('dashicons-visibility');
		}
	}

	// TODO: remove later, when Enterprise reCAPTCHA is fully rolled out
    // Classic reCAPTCHA fields
    $('.oo-icon-eye-secret-key').on('click', function () {
        showOrHideGoogleRecaptchaKey('input[name="onoffice-settings-captcha-secretkey"]', '.oo-icon-eye-secret-key');
    });

    $('.oo-icon-eye-site-key').on('click', function () {
        showOrHideGoogleRecaptchaKey('input[name="onoffice-settings-captcha-sitekey"]', '.oo-icon-eye-site-key');
    });

    // Enterprise reCAPTCHA fields
    $('.oo-icon-eye-enterprise-projectid').on('click', function () {
        showOrHideGoogleRecaptchaKey('input[name="onoffice-settings-captcha-enterprise-projectid"]', '.oo-icon-eye-enterprise-projectid');
    });

    $('.oo-icon-eye-enterprise-sitekey').on('click', function () {
        showOrHideGoogleRecaptchaKey('input[name="onoffice-settings-captcha-enterprise-sitekey"]', '.oo-icon-eye-enterprise-sitekey');
    });
    $('.oo-icon-eye-enterprise-apikey').on('click', function () {
        showOrHideGoogleRecaptchaKey('input[name="onoffice-settings-captcha-enterprise-apikey"]', '.oo-icon-eye-enterprise-apikey');
    });
});