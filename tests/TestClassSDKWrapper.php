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
			->onlyMethods(['call', 'getResponseArray', 'getErrors', 'callGeneric','removeCacheInstances'])
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
		$pSDK->expects($this->once())->method('removeCacheInstances');
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

	/**
	 * Regression test for the >500 estates pagination bug.
	 *
	 * Cache build was loading every page with the same parameters (offset 0)
	 * because the listoffset was never recomputed for follow-up pages. This
	 * test exercises the private createCacheForList() and verifies that for a
	 * dataset of 1500 items split across 3 pages of 500, the offsets used for
	 * pages 2 and 3 are 500 and 1000 respectively, and that records from all
	 * pages end up merged in the result.
	 */
	public function testCreateCacheForListPaginatesWithCorrectOffsets()
	{
		$pMocker = new SDKWrapperMocker();
		$baseParams = [
			'data' => ['Id'],
			'filter' => [],
			'listlimit' => 500,
			'estatelanguage' => 'ENG',
			'outputlanguage' => 'ENG',
			'formatoutput' => true,
			'addMainLangId' => true,
		];

		$pMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ, 'estate', '', $baseParams, null,
			$this->buildPaginationResponse(range(1, 500), 1500)
		);
		$pMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ, 'estate', '', $baseParams + ['listoffset' => 500], null,
			$this->buildPaginationResponse(range(501, 1000), 1500)
		);
		$pMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ, 'estate', '', $baseParams + ['listoffset' => 1000], null,
			$this->buildPaginationResponse(range(1001, 1500), 1500)
		);

		$result = $this->invokeCreateCacheForList($pMocker, $baseParams, 'estate');

		$this->assertArrayHasKey('data', $result);
		$this->assertArrayHasKey('records', $result['data']);
		$this->assertCount(1500, $result['data']['records']);
		$ids = array_column($result['data']['records'], 'id');
		$this->assertSame(1, $ids[0]);
		$this->assertSame(500, $ids[499]);
		$this->assertSame(501, $ids[500]);
		$this->assertSame(1500, $ids[1499]);
	}

	/**
	 * Regression test: API occasionally returns cntabsolute as an array
	 * (instead of an int). Without the is_array() guard intval() of an array
	 * raised TypeErrors / produced 0 pages, causing missing data on customer
	 * sites with many estates.
	 */
	public function testCreateCacheForListHandlesCntabsoluteAsArray()
	{
		$pMocker = new SDKWrapperMocker();
		$baseParams = [
			'data' => ['Id'],
			'listlimit' => 500,
		];

		$page1Response = $this->buildPaginationResponse(range(1, 500), 1500);
		$page1Response['data']['meta']['cntabsolute'] = [1500];

		$pMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ, 'estate', '', $baseParams, null, $page1Response
		);
		$pMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ, 'estate', '', $baseParams + ['listoffset' => 500], null,
			$this->buildPaginationResponse(range(501, 1000), 1500)
		);
		$pMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ, 'estate', '', $baseParams + ['listoffset' => 1000], null,
			$this->buildPaginationResponse(range(1001, 1500), 1500)
		);

		$result = $this->invokeCreateCacheForList($pMocker, $baseParams, 'estate');

		$this->assertCount(1500, $result['data']['records']);
	}

	/**
	 * When cntabsolute fits inside a single page, no further requests should
	 * be issued. Guards against off-by-one regressions in the page loop.
	 */
	public function testCreateCacheForListDoesNotPaginateWhenSinglePage()
	{
		$pMocker = new SDKWrapperMocker();
		$baseParams = ['data' => ['Id'], 'listlimit' => 500];

		$pMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ, 'estate', '', $baseParams, null,
			$this->buildPaginationResponse(range(1, 50), 50)
		);

		$result = $this->invokeCreateCacheForList($pMocker, $baseParams, 'estate');

		$this->assertCount(50, $result['data']['records']);
		$this->assertCount(1, $pMocker->getRequestArray());
	}

	/**
	 * Issues exactly ceil(cntabsolute / 500) requests and uses
	 * monotonically increasing offsets 0, 500, 1000, ..., never collapsing
	 * back to 0.
	 */
	public function testCreateCacheForListRequestOffsetsAreMonotonic()
	{
		$pMocker = new SDKWrapperMocker();
		$baseParams = ['data' => ['Id'], 'listlimit' => 500];
		$total = 2300;
		$expectedRequests = (int) ceil($total / 500);

		$pMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ, 'estate', '', $baseParams, null,
			$this->buildPaginationResponse(range(1, 500), $total)
		);
		for ($page = 2; $page <= $expectedRequests; $page++) {
			$offset = 500 * ($page - 1);
			$end = min($offset + 500, $total);
			$pMocker->addResponseByParameters(
				onOfficeSDK::ACTION_ID_READ, 'estate', '', $baseParams + ['listoffset' => $offset], null,
				$this->buildPaginationResponse(range($offset + 1, $end), $total)
			);
		}

		$this->invokeCreateCacheForList($pMocker, $baseParams, 'estate');

		$rawRequests = $pMocker->getRequestArray();
		$this->assertCount($expectedRequests, $rawRequests);

		$offsets = [];
		foreach ($rawRequests as $rawRequest) {
			$decoded = json_decode($rawRequest, true);
			$params = $decoded[3] ?? [];
			$offsets[] = $params['listoffset'] ?? 0;
		}
		$this->assertSame([0, 500, 1000, 1500, 2000], $offsets);
	}

	/**
	 * Build a fake API response for pagination tests.
	 */
	private function buildPaginationResponse(array $ids, int $cntabsolute): array
	{
		$records = [];
		foreach ($ids as $id) {
			$records[] = ['id' => $id, 'type' => 'estate', 'elements' => ['Id' => $id]];
		}
		return [
			'actionid' => onOfficeSDK::ACTION_ID_READ,
			'resourceid' => '',
			'resourcetype' => 'estate',
			'cacheable' => true,
			'identifier' => '',
			'data' => [
				'meta' => ['cntabsolute' => $cntabsolute],
				'records' => $records,
			],
			'status' => ['errorcode' => 0, 'message' => 'OK'],
		];
	}

	/**
	 * Invoke the private SDKWrapper::createCacheForList() via reflection.
	 */
	private function invokeCreateCacheForList(SDKWrapperMocker $pMocker, array $params, string $module): array
	{
		$pReflection = new \ReflectionMethod(SDKWrapper::class, 'createCacheForList');
		$pReflection->setAccessible(true);
		return $pReflection->invoke($pMocker, $params, $module, 1);
	}
}