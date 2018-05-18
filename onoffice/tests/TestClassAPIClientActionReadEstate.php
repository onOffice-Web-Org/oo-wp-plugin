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

use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\API\APIClientActionReadEstate;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers APIClientActionReadAddress
 *
 */

class TestClassAPIClientActionReadEstate
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testSendRequest()
	{
		$pApiCall = $this->sendRequest($this->getResultReadEstate());

		$this->assertTrue($pApiCall->getResultStatus());
		$this->assertEquals($this->getExpectedRecordResult(), $pApiCall->getResultRecords());
	}


	/**
	 *
	 * @param array $apiResult
	 * @return APIClientActionReadEstate
	 *
	 */

	private function sendRequest(array $apiResult)
	{
		$pSDKMocker = new SDKWrapperMocker();
		$pApiCall = new APIClientActionReadEstate($pSDKMocker);
		$parameters = array(
			'data' => array('kaufpreis', 'lage'),
			'listlimit' => 2,
			'listoffset' => 0
		);
		$pApiCall->setParameters($parameters);
		$pApiCall->addRequestToQueue();
		$pSDKMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parameters, null, $apiResult);

		$pSDKMocker->sendRequests();

		return $pApiCall;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getResultReadEstate()
	{
		$resultStr = '
		{
			"actionid": "urn:onoffice-de-ns:smart:2.5:smartml:action:read",
			"resourceid": "",
			"resourcetype": "estate",
			"cacheable": true,
			"identifier": "",
			"data": {
				"meta": {
					"cntabsolute": 1173
				},
				"records": [
					{
						"id": 6,
						"type": "estate",
						"elements": {
							"Id": "6",
							"kaufpreis": "299000.00",
							"lage": "Top Lage, hier haben Sie alles vor der T\u00fcr, was das wohnen angenehm macht. Attraktive Einkaufsm\u00f6glichkeiten, s\u00e4mtliche Einrichtungen f\u00fcr die Gesundheitsvorsorge und gepflegte Gastronomie, alles problemlos zu Fu\u00df zu erreichen."
						}
					},
					{
						"id": 10,
						"type": "estate",
						"elements": {
							"Id": "10",
							"kaufpreis": "330000.00",
							"lage": ""
						}
					}
				]
			},
			"status": {
				"errorcode": 0,
				"message": "OK"
			}
		}';

		return json_decode($resultStr, true);
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getExpectedRecordResult()
	{
		return array(
			array(
				"id" => 6,
				"type" => "estate",
				"elements" => array(
					"Id" => "6",
					"kaufpreis" => "299000.00",
					"lage" => "Top Lage, hier haben Sie alles vor der Tür, was das wohnen angenehm macht. "
						."Attraktive Einkaufsmöglichkeiten, sämtliche Einrichtungen für die Gesundheitsvorsorge "
						."und gepflegte Gastronomie, alles problemlos zu Fuß zu erreichen."
					)
			),
			array(
				"id" => 10,
				"type" => "estate",
				"elements" => array(
					"Id" => "10",
					"kaufpreis" => "330000.00",
					"lage" => ""
				),
			),
		);
	}
}
