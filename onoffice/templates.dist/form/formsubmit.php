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
?>
	<script>
		function onSubmit() {
			document.getElementById(<?php echo json_encode($pForm->getGenericSetting('formId')); ?>).submit();
		}
	</script>

	<button class="g-recaptcha"
			data-sitekey="<?php echo esc_attr($key); ?>"
			data-callback="onSubmit"><?php echo esc_html($pForm->getGenericSetting('submitButtonLabel')); ?></button>
<?php
} else {
?>

<input type="submit" value="<?php echo esc_html($pForm->getGenericSetting('submitButtonLabel')); ?>">

<?php
}