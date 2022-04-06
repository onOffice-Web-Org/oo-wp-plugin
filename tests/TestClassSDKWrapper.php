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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\Cache\onOfficeSDKCache;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Cache\DBCache;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Utility\SymmetricEncryption;
use onOffice\WPlugin\Utility\SymmetricEncryptionDefault;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use WP_UnitTestCase;

/**
 *
 * Test for class SDKWrapper
 *
 */

class TestClassSDKWrapper
	extends WP_UnitTestCase
{
	/** @var onOfficeSDK */
	private $_pMockSDK = null;

	/** @var WPOptionWrapperTest */
	private $_pMockWPOptions = null;

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var string */
	private $_expectedResult = '{
		"actionid": "urn:onoffice-de-ns:smart:2.5:smartml:action:read",
		"resourceid": "",
		"resourcetype": "impressum",
		"cacheable": true,
		"identifier": "",
		"data": {
			"meta": {
				"cntabsolute": null
			},
			"records": [
				{
					"id": "impressum",
					"type": null,
					"elements": {
						"country": "DEU"
					}
				}
			]
		},
		"status": {
			"errorcode": 0,
			"message": "OK"
		}
	}';


	/**
	 *
	 */

	public function testConstruct()
	{
		$pSDKWrapper = new SDKWrapper();
		$this->assertInstanceOf(onOfficeSDK::class, $pSDKWrapper->getSDK());
		$this->assertInstanceOf(WPOptionWrapperDefault::class, $pSDKWrapper->getWPOptionWrapper());
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pMockSDK = $this->getMockBuilder(onOfficeSDK::class)
			->setMethods(['call', 'getResponseArray', 'getErrors', 'callGeneric'])
			->getMock();
		$this->_pMockSDK->method('call')
			->with('testAction', '', null, 'testResource', [])
			->will($this->returnValue(2));
		$this->_pMockWPOptions = new WPOptionWrapperTest();
		$this->_pSDKWrapper = new SDKWrapper($this->_pMockSDK, $this->_pMockWPOptions);
		$encrypter = new SymmetricEncryptionDefault();
		$this->_pMockWPOptions->addOption('onoffice-settings-apikey', $encrypter->encrypt('test-key', ONOFFICE_CREDENTIALS_ENC_KEY));
		$this->_pMockWPOptions->addOption('onoffice-settings-apisecret', $encrypter->encrypt('test-secret', ONOFFICE_CREDENTIALS_ENC_KEY));
	}


	/**
	 *
	 * @return APIClientActionGeneric
	 *
	 */

	public function testAddRequestByApiAction(): APIClientActionGeneric
	{
		$pApiClientAction = new APIClientActionGeneric($this->_pSDKWrapper, 'testAction', 'testResource');
		$this->assertEquals(2, $this->_pSDKWrapper->addRequestByApiAction($pApiClientAction));
		return $pApiClientAction;
	}


	/**
	 *
	 */

	public function testGetCache()
	{
		$pDBCaches = $this->_pSDKWrapper->getCache();
		$this->assertCount(1, $pDBCaches);
		$this->assertInstanceOf(DBCache::class, $pDBCaches[0]);
		$this->assertInstanceOf(onOfficeSDKCache::class, $pDBCaches[0]);
	}


	/**
	 *
	 * @depends testAddRequestByApiAction
	 * @param APIClientActionGeneric $pAPIClientActionGeneric
	 *
	 */

	public function testSendRequests(APIClientActionGeneric $pAPIClientActionGeneric): SDKWrapper
	{
		$pSDKWrapper = $pAPIClientActionGeneric->getSDKWrapper();
		$pSDK = $pSDKWrapper->getSDK();
		$pSDK->expects($this->once())->method('getErrors')->will($this->returnValue([]));
		$pSDK->method('getResponseArray')->with(2)
			->will($this->returnValue(json_decode($this->_expectedResult, true)));
		$this->assertFalse($pAPIClientActionGeneric->getResultStatus());

		$pSDKWrapper->sendRequests();
		$this->assertTrue($pAPIClientActionGeneric->getResultStatus());
		$this->assertEquals([[
			'id' => 'impressum',
			'type' => null,
			'elements' => [
				'country' => 'DEU',
			]
		]], $pAPIClientActionGeneric->getResultRecords());
		return $pSDKWrapper;
	}


	/**
	 *
	 */

	public function testWithCurlOptions()
	{
		$pSDKWrapper = new SDKWrapper();
		$this->assertInstanceOf(SDKWrapper::class, $pSDKWrapper->withCurlOptions(['curlOP']));
	}
}