<?php

/**
 *
 *    Copyright (C) 2018  onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

$key = get_option('onoffice-settings-captcha-sitekey', '');

if ($pForm->needsReCaptcha() && $key !== '') {
	$formId = $pForm->getGenericSetting('formId');
?>
	<script>
		function onSubmit() {
			var element = document.getElementById(<?php echo json_encode($formId); ?>);
			element.submit();
		}
	</script>

	<div id='recaptcha' class="g-recaptcha"
		data-sitekey="<?php echo esc_attr($key); ?>"
		data-callback="onSubmit"
		data-size="invisible"></div>
	<button class="submit_button"><?php echo esc_html($pForm->getGenericSetting('submitButtonLabel')); ?></button>
	<script>
	(function() {
		var formId = <?php echo json_encode($formId); ?>;
		var formElement = document.getElementById(formId);
		var submitButtonElement = formElement.getElementsByClassName('submit_button')[0];
		onOffice.captchaControl(formElement, submitButtonElement);
	})();


	</script>
<?php
} else {
?>

<input type="submit" value="<?php echo esc_html($pForm->getGenericSetting('submitButtonLabel')); ?>">

<?php
}