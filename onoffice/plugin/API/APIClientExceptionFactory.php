<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\WPlugin\API;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class APIClientExceptionFactory
{
	/**
	 *
	 * @param APIClientActionGeneric $pAPIClientAction
	 * @return ApiClientException
	 *
	 */

	public function createExceptionByAPIClientAction(APIClientActionGeneric $pAPIClientAction): ApiClientException
	{
		$pApiError = new APIError();
		$code = $pAPIClientAction->getErrorCode();

		if ($pApiError->isCredentialError($code)) {
			return new APIClientCredentialsException($pAPIClientAction);
		} elseif ($code === 500) {
			return new APIEmptyResultException($pAPIClientAction);
		}

		return new ApiClientException($pAPIClientAction);
	}
}