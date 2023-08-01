<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace onOffice\WPlugin\Form;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class CaptchaDataChecker
{
	/**
	 *
	 */

	public static function addHook()
	{
		add_action('wp_ajax_check_captcha_data', function() {
			$pCaptchaDataChecker = new CaptchaDataChecker();
			$pCaptchaDataChecker->check();
		});
	}


	/**
	 *
	 */

	public static function registerScripts()
	{
		$siteKey = get_option('onoffice-settings-captcha-sitekey', '');
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';

		if ($siteKey !== '') {
			wp_register_script('onoffice-captchacontrol', plugins_url
				('/js/onoffice-captchacontrol.js', $pluginPath), 'google-recaptcha');

			wp_enqueue_script('onoffice-captchacontrol');
		}
	}


	/**
	 *
	 */

	public function check()
	{
		$token = filter_input(INPUT_POST, CaptchaHandler::RECAPTCHA_RESPONSE_PARAM);
		$secret = get_option('onoffice-settings-captcha-secretkey', '');

		$errors = [];
		$pCaptchaHandler = new CaptchaHandler($token, $secret);
		$result = $pCaptchaHandler->checkCaptcha();

		if (!$result) {
			$errors = $pCaptchaHandler->getErrorCodes();
		}

		echo json_encode([
			'result' => $result,
			'error-codes' => $errors,
		]);

		wp_die();
	}
}
