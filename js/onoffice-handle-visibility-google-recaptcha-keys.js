jQuery(document).ready(function ($) {
	function showOrHiddenGoogleRecaptchaKey(inputSelector, toggleSelector) {
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

	$('.oo-icon-eye-secret-key').on('click', function () {
		showOrHiddenGoogleRecaptchaKey('input[name="onoffice-settings-captcha-secretkey"]', '.oo-icon-eye-secret-key');
	});

	$('.oo-icon-eye-site-key').on('click', function () {
		showOrHiddenGoogleRecaptchaKey('input[name="onoffice-settings-captcha-sitekey"]', '.oo-icon-eye-site-key');
	});
});