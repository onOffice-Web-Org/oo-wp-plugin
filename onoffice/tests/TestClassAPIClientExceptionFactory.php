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

declare (strict_types=1);

namespace onOffice\tests;

use Closure;
use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\API\APIClientExceptionFactory;
use onOffice\WPlugin\API\APIEmptyResultException;
use onOffice\WPlugin\API\APIError;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassAPIClientExceptionFactory
	extends WP_UnitTestCase
{
	/** @var APIClientExceptionFactory */
	private $_pFactory = null;

	/** @var APIClientActionGeneric */
	private $_pApiClientAction = null;


	/**
	 *
	 */

	public function testCreateExceptionByCode()
	{
		$pFactory = $this->_pFactory;
		$pApiError = new APIError();

		foreach ($pApiError->getCredentialErrorCodes() as $credentialErrorCode) {
			$this->setReturnCode($credentialErrorCode);
			$this->assertInstanceOf(APIClientCredentialsException::class,
				$pFactory->createExceptionByAPIClientAction($this->_pApiClientAction));
		}

		$this->setReturnCode(500);
		$this->assertInstanceOf(APIEmptyResultException::class,
			$pFactory->createExceptionByAPIClientAction($this->_pApiClientAction));

		$this->setReturnCode(2);
		$this->assertInstanceOf(ApiClientException::class,
			$pFactory->createExceptionByAPIClientAction($this->_pApiClientAction));
	}


	/**
	 *
	 * @before
	 *
	 */

	public function setupBeforeTest()
	{
		$pSDKWrapper = new SDKWrapperMocker();
		$this->_pApiClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'filters');
		$this->_pFactory = new APIClientExceptionFactory();
		$this->setReturnCode(0);
	}


	/**
	 *
	 * @param int $code
	 *
	 */

	private function setReturnCode(int $code)
	{
		$pClosureSet = function(int $code) {
			$this->_result['status']['errorcode'] = $code;
		};

		$pClosureExec = Closure::bind
			($pClosureSet, $this->_pApiClientAction, APIClientActionGeneric::class);
		$pClosureExec($code);
	}
}
