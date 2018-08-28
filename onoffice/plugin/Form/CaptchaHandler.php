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
	/** */
	const SITE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

	/** */
	const RECAPTCHA_RESPONSE_PARAM = 'g-recaptcha-response';


	/** @var string */
	private $_token = '';

	/** @var string */
	private $_secret = '';


	/**
	 *
	 * @param string $token
	 * @param string $secret
	 *
	 */

	public function __construct(string $token, string $secret)
	{
		$this->_token = $token;
		$this->_secret = $secret;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function checkCaptcha(): bool
	{
		$returnVal = false;

		if ($this->_token !== '') {
			$url = $this->buildFullUrl();
			$curlResource = curl_init($url);
			$response = curl_exec($curlResource);
			$returnVal = $this->getResult($response);
		}

		return $returnVal;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function buildFullUrl(): string
	{
		$parameters = http_build_query([
			'secret' => $this->_secret,
			'response' => $this->_token,
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
		return $result['success'] ?? false;
	}
}
