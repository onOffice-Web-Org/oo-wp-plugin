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

use onOffice\WPlugin\API\APIError;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassAPIError
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetCredentialErrorCodes()
	{
		$pAPIError = new APIError();
		$errorCodes = $pAPIError->getCredentialErrorCodes();
		$expectedResult = [13, 22, 28, 30, 40, 41, 42, 43, 45, 47, 48, 49, 137];
		$this->assertEquals($expectedResult, $errorCodes);
	}


	/**
	 *
	 */

	public function testIsCredentialErrorCode()
	{
		$pAPIError = new APIError();

		$this->assertFalse($pAPIError->isCredentialError(0));
		$this->assertFalse($pAPIError->isCredentialError(1));
		$this->assertTrue($pAPIError->isCredentialError(13));
		$this->assertTrue($pAPIError->isCredentialError(22));
		$this->assertTrue($pAPIError->isCredentialError(28));
	}
}