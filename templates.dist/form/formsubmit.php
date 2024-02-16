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
/** @var \onOffice\WPlugin\Form $pForm */
if ($pForm->needsReCaptcha() && $key !== '') {
	$formId = $pForm->getGenericSetting('formId');
	$pFormNo = $pForm->getFormNo();
?>
	<script>
		function submitForm<?php echo $pFormNo; ?>() {
			const selectorFormById = `form[id="onoffice-form"] input[name="oo_formno"][value="<?php echo $pFormNo; ?>"]`;
			const form = document.querySelector(selectorFormById).parentElement;
			const submitButtonElement = form.querySelector('.submit_button');
			form.submit();
			submitButtonElement.disabled = true;
			submitButtonElement.classList.add('onoffice-unclickable-form');
		}
	</script>
	<div class="g-recaptcha"
		data-sitekey="<?php echo esc_attr($key); ?>" 
		data-callback="submitForm<?php echo $pFormNo; ?>" data-size="invisible">
	</div>
	<button class="submit_button">
		<?php echo esc_html($pForm->getGenericSetting('submitButtonLabel')); ?>
	</button>
	<script>
		(function() {
			const selectorFormById = `form[id="onoffice-form"] input[name="oo_formno"][value="<?php echo $pFormNo; ?>"]`;
			const form = document.querySelector(selectorFormById).parentElement;
			const submitButtonElement = form.querySelector('.submit_button');
			onOffice.captchaControl(form, submitButtonElement);
		})();
	</script>
<?php
} else {
?>

<input type="submit" value="<?php echo esc_html($pForm->getGenericSetting('submitButtonLabel')); ?>">

<?php
}