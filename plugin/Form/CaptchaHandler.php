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
		$response = wp_remote_post(self::SITE_VERIFY_URL, [
			'body' => [
				'secret' => $this->_secret,
				'response' => $this->_captchaResponse,
			],
		]);

		if (is_wp_error($response)) {
			return false;
		}

		$responseBody = wp_remote_retrieve_body($response);
		$returnVal = $this->getResult($responseBody);

		return $returnVal;
	} // @codeCoverageIgnoreEnd

	


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
}
