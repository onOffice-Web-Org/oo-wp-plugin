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

class CaptchaHandler
{
	const SITE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

	const RECAPTCHA_RESPONSE_PARAM = 'g-recaptcha-response';

	const RECAPTCHA_V3_THRESHOLD = 0.5;


	/** @var string */
	private $_captchaResponse = '';

	/** @var string */
	private $_secret = '';

	/** @var array */
	private $_errorCodes = [];


	/**
	 *
	 * @param string $captchaResponse
	 * @param string $secret
	 *
	 */

	public function __construct(string $captchaResponse, string $secret)
	{
		$this->_captchaResponse = $captchaResponse;
		$this->_secret = $secret;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function checkCaptcha(): bool
	{
		// @codeCoverageIgnoreStart
		$url = $this->buildFullUrl();
		$curlResource = curl_init($url);
		curl_setopt($curlResource, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curlResource);
		$returnVal = $this->getResult($response);

		return $returnVal;
	} // @codeCoverageIgnoreEnd


	/**
	 *
	 * @return string
	 *
	 */

	private function buildFullUrl(): string
	{
		$parameters = http_build_query([
			'secret' => $this->_secret,
			'response' => $this->_captchaResponse,
		]);

		$url = self::SITE_VERIFY_URL.'?'.$parameters;

		return $url;
	}


	/**
	 *
	 * @param string $response
	 * @return bool
	 *
	 */

	public function getResult(string $response): bool
	{
		$result = json_decode($response, true);

		$this->_errorCodes = $result['error-codes'] ?? [];

		if (isset($result['score'])) {
			return $result['score'] > self::RECAPTCHA_V3_THRESHOLD;
		} else {
			return $result['success'] ?? false;
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getErrorCodes(): array
	{
		return $this->_errorCodes;
	}

	/**
	 *
	 */
	public static function registerScripts()
	{
		$siteKey = get_option('onoffice-settings-captcha-sitekey', '');

		if ($siteKey !== '') {
			wp_enqueue_script('onoffice-captchacontrol', plugins_url('/dist/onoffice-captchacontrol.min.js', ONOFFICE_PLUGIN_DIR.'/index.php'), array('jquery'), null, true);
			wp_print_scripts('onoffice-captchacontrol');	
		}
	}
}
