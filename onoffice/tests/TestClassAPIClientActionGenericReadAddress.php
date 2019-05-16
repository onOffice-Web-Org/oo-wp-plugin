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

use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;
use function json_decode;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassAPIClientActionGenericReadAddress
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testSendRequest()
	{
		$pApiCall = $this->sendRequest($this->getResponseGetByRecordId());

		$this->assertTrue($pApiCall->getResultStatus());
		$this->assertEquals($this->getExpectedRecordResult(), $pApiCall->getResultRecords());
	}


	/**
	 *
	 * @param array $apiResult
	 * @return APIClientActionGeneric
	 *
	 */

	private function sendRequest(array $apiResult)
	{
		$pSDKMocker = new SDKWrapperMocker();
		$pApiCall = new APIClientActionGeneric($pSDKMocker, onOfficeSDK::ACTION_ID_READ, 'address');
		$parameters = array(
			'recordids' => array(13, 37),
			'data' => array('Name', 'KdNr', 'Vorname'),
		);
		$pApiCall->setParameters($parameters);
		$pApiCall->addRequestToQueue();
		$pSDKMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'address', '', $parameters, null, $apiResult);

		$pSDKMocker->sendRequests();

		return $pApiCall;
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\API\APIEmptyResultException
	 *
	 */

	public function testSendRequestEmptyResponse()
	{
		$pApiCall = $this->sendRequest(array());
		$this->assertFalse($pApiCall->getResultStatus());
		$pApiCall->getResultRecords();
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\API\APIEmptyResultException
	 *
	 */

	public function testGetResultMetaEmptyResponse()
	{
		$pApiCall = $this->sendRequest(array());
		$this->assertFalse($pApiCall->getResultStatus());
		$pApiCall->getResultMeta();
	}


	/**
	 *
	 */

	public function testWithActionIdAndResourceType()
	{
		$pSDKWrapper = new SDKWrapper();
		$pApiClientActionOriginal = new APIClientActionGeneric($pSDKWrapper, 'testId', 'testType');
		$this->assertEquals('testId', $pApiClientActionOriginal->getActionId());
		$this->assertEquals('testType', $pApiClientActionOriginal->getResourceType());
		$this->assertSame($pSDKWrapper, $pApiClientActionOriginal->getSDKWrapper());
		$pApiClientActionCopy = $pApiClientActionOriginal->withActionIdAndResourceType('otherAction', 'otherResource');
		$this->assertEquals('otherAction', $pApiClientActionCopy->getActionId());
		$this->assertEquals('otherResource', $pApiClientActionCopy->getResourceType());
		$this->assertSame($pSDKWrapper, $pApiClientActionCopy->getSDKWrapper());
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getExpectedRecordResult()
	{
		return array(
			array (
				'id' => 13,
				'type' => 'address',
				'elements' =>
				array (
					'id' => 13,
					'Name' => 'Firestone',
					'KdNr' => 9,
					'Vorname' => 'Fred',
				),
			),
			array (
				'id' => 37,
				'type' => 'address',
				'elements' =>
				array(
					'id' => 37,
					'Name' => 'Fleißig',
					'KdNr' => 12,
					'Vorname' => 'Heinrich',
				),
			),
		);
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getResponseGetByRecordId()
	{
		$responseStr = '
		{
			"actionid": "urn:onoffice-de-ns:smart:2.5:smartml:action:read",
			"resourceid": "",
			"resourcetype": "address",
			"cacheable": true,
			"identifier": "",
			"data": {
				"meta": {
					"cntabsolute": null
				},
				"records": [
					{
						"id": 13,
						"type": "address",
						"elements": {
							"id": 13,
							"Name": "Firestone",
							"KdNr": 9,
							"Vorname": "Fred"
						}
					},
					{
						"id": 37,
						"type": "address",
						"elements": {
							"id": 37,
							"Name": "Fleißig",
							"KdNr": 12,
							"Vorname": "Heinrich"
						}
					}
				]
			},
			"status": {
				"errorcode": 0,
				"message": "OK"
			}
		}';

		return json_decode($responseStr, true);
	}
}
