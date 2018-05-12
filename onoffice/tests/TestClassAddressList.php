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
use onOffice\WPlugin\AddressList;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers AddressList
 *
 */

class TestClassAddressList
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetRows()
	{
		$pAddressList = $this->getNewAddressList();
		$records = $pAddressList->getRows(true);
		$expectationRecords = $this->getExpectedRecords();

		foreach ($expectationRecords as $recordId => $values) {
			$this->assertArrayHasKey($recordId, $records);
			$pRecord = $records[$recordId];

			foreach ($values as $key => $value) {
				$this->assertEquals($value, $pRecord[$key]);
			}
		}
	}


	/**
	 *
	 */

	public function testGetRecordsById()
	{
		$pAddressList = $this->getNewAddressList();
		$expectedRecords = $this->getExpectedRecords();
		$record1 = $expectedRecords[13];
		$record2 = $expectedRecords[37];

		$this->assertEquals($record1, $pAddressList->getAddressById(13));
		$this->assertEquals($record2, $pAddressList->getAddressById(37));
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getExpectedRecords()
	{
		return array(
			13 => array(
				'Name' => 'Firestone',
				'KdNr' => 9,
				'Vorname' => 'Fred',
				'id' => 13,
			),
			37 => array(
				'Name' => 'Fleißig',
				'KdNr' => 12,
				'Vorname' => 'Heinrich',
				'id' => 37,
			),
		);
	}


	/**
	 *
	 * @return AddressList
	 *
	 */

	private function getNewAddressList()
	{
		$pSDKWrapper = new SDKWrapperMocker();
		$response = $this->getResponseGetRows();
		$parameters = array(
			'recordids' => array(13, 37),
			'data' => array('Name', 'KdNr', 'Vorname'),
		);

		$pSDKWrapper->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'address', '', $parameters, null, $response);

		$pAddressList = new AddressList();
		$pAddressList->setSDKWrapper($pSDKWrapper);
		$pAddressList->loadAdressesById(array(13, 37), array('Name', 'KdNr', 'Vorname'));
		return $pAddressList;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getResponseGetRows()
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
