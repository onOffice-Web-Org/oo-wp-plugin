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

use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassApiClientException
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testConstruct()
	{
		$pApiClientAction = $this->getNewApiClientAction('testActionId', 'testResourceType');

		$pException = new ApiClientException($pApiClientAction);
		$this->assertInstanceOf(\Exception::class, $pException);
	}


	/**
	 *
	 */

	public function testGetApiClientAction()
	{
		$parameters = [
			'testParam1' => [],
			'testParam2' => 'testValue2',
		];
		$pApiClientAction = $this->getNewApiClientAction('testActionId', 'testResourceType', $parameters);

		$pException = new ApiClientException($pApiClientAction);
		$this->assertEquals($pApiClientAction, $pException->getApiClientAction());
	}


	/**
	 *
	 * @param string $actionId
	 * @param string $resourceType
	 * @param array $parameters
	 * @return APIClientActionGeneric
	 *
	 */

	private function getNewApiClientAction(
		string $actionId, string $resourceType, array $parameters = []): APIClientActionGeneric
	{
		$pSDKWrapper = new SDKWrapper();
		$pApiClientAction = new APIClientActionGeneric($pSDKWrapper, $actionId, $resourceType);
		$pApiClientAction->setParameters($parameters);
		return $pApiClientAction;
	}
}
