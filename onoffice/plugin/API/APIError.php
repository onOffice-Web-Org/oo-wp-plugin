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

namespace onOffice\WPlugin\API;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class APIError
{
	const STRUCTURE_MISSING_TOKEN = 13;
	const HANDLER_NOT_AUTHENTICATED = 22;
	const OAUTH_CLIENT_REGISTRATION_FAILED = 28;
	const OAUTH_CLIENT_INFO_RETRIEVAL_FAILED = 30;
	const OAUTH_USRCREDS_INVALID_CREDENTIALS = 40;
	const OAUTH_USRCREDS_UNKNOWN_CUSTOMER = 41;
	const OAUTH_USRCREDS_INACTIVE_CUSTOMER = 42;
	const OAUTH_USRCREDS_UNKNOWN_USER = 43;
	const OAUTH_RTOKEN_INVALID_TOKEN = 45;
	const OAUTH_RTOKEN_UNKNOWN_CLIENT = 47;
	const OAUTH_RTOKEN_ACCESS_REVOKED = 48;
	const WRONG_SECRET = 49;
	const HMAC_INVALID = 137;


	/**
	 *
	 * @return array
	 *
	 */

	public function getCredentialErrorCodes(): array
	{
		return [
			self::STRUCTURE_MISSING_TOKEN,
			self::HANDLER_NOT_AUTHENTICATED,
			self::OAUTH_CLIENT_REGISTRATION_FAILED,
			self::OAUTH_CLIENT_INFO_RETRIEVAL_FAILED,
			self::OAUTH_USRCREDS_INVALID_CREDENTIALS,
			self::OAUTH_USRCREDS_UNKNOWN_CUSTOMER,
			self::OAUTH_USRCREDS_INACTIVE_CUSTOMER,
			self::OAUTH_USRCREDS_UNKNOWN_USER,
			self::OAUTH_RTOKEN_INVALID_TOKEN,
			self::OAUTH_RTOKEN_UNKNOWN_CLIENT,
			self::OAUTH_RTOKEN_ACCESS_REVOKED,
			self::WRONG_SECRET,
			self::HMAC_INVALID,
		];
	}


	/**
	 *
	 * @param int $errorCode
	 * @return bool
	 *
	 */

	public function isCredentialError(int $errorCode): bool
	{
		return in_array($errorCode, $this->getCredentialErrorCodes());
	}
}
