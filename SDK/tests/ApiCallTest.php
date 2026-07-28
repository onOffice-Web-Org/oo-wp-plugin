<?php

namespace Tests\onOffice\SDK;

use onOffice\SDK\Cache\onOfficeSDKCache;
use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\SDK\internal\ApiCall;
use onOffice\SDK\internal\HttpFetch;
use ReflectionMethod;

class ApiCallTest extends \PHPUnit\Framework\TestCase
{
	public function testCallByRawData()
	{
		$apiCall = new ApiCall();

		$result = $apiCall->callByRawData(
			'someActionId',
			'someResourceId',
			'someIdentifier',
			'someResourceType',
			[]
		);

		$this->assertEquals(0, $result);
	}

	public function testSendRequests()
	{
		$apiCall = new ApiCall();

		$httpFetch = $this->getMockBuilder(HttpFetch::class)
			->disableOriginalConstructor()
			->onlyMethods(['send'])
			->getMock();

		$id = $apiCall->callByRawData(
			'someActionId',
			'someResourceId',
			'someIdentifier',
			'someResourceType',
			[]
		);

		$array = [
			'response' => [
				'results' => [
					0 => [
						'status' => [
							'errorcode' => 0
						]
					]
				]
			]
		];

		$httpFetch
			->expects($this->once())
			->method('send')
			->willReturn(json_encode($array));

		$apiCall->sendRequests(
			'someToken',
			'someSecret',
			$httpFetch
		);
	}

	public function testSendRequestsWithoutCallByRawData()
	{
		$apiCall = new ApiCall();

		$httpFetch = $this->getMockBuilder(HttpFetch::class)
			->disableOriginalConstructor()
			->onlyMethods(['send'])
			->getMock();


		$httpFetch
			->expects($this->never())
			->method('send');

		$apiCall->sendRequests(
			'someToken',
			'someSecret',
			$httpFetch
		);
	}

	public function testSendRequestsWithoutProperresponse()
	{
		$this->expectException(HttpFetchNoResultException::class);

		$apiCall = new ApiCall();

		$httpFetch = $this->getMockBuilder(HttpFetch::class)
			->disableOriginalConstructor()
			->onlyMethods(['send'])
			->getMock();

		$apiCall->callByRawData(
			'someActionId',
			'someResourceId',
			'someIdentifier',
			'someResourceType',
			[]
		);

		$array = [
			'response' => []
		];

		$httpFetch
			->expects($this->once())
			->method('send')
			->willReturn(json_encode($array));

		$apiCall->sendRequests(
			'someToken',
			'someSecret',
			$httpFetch
		);
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testSetApiVersion()
	{
		$apiCall = new ApiCall();
		$apiCall->setApiVersion('v1');
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testAddCache()
	{
		$cache = $this->getMockBuilder(onOfficeSDKCache::class)
			->getMock();

		$apiCall = new ApiCall();
		$apiCall->addCache($cache);
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testRemoveCacheInstances()
	{
		$cache = $this->getMockBuilder(onOfficeSDKCache::class)
			->getMock();

		$apiCall = new ApiCall();
		$apiCall->addCache($cache);
		$apiCall->removeCacheInstances();
	}

	public function testGetErrors()
	{
		$apiCall = new ApiCall();
		$result = $apiCall->getErrors();

		$this->assertEquals([], $result);
	}


	/**
	 * filterRecords should auto-generate raw records and types when they are missing.
	 */
	public function testFilterRecords_generatesMissingRawAndTypes()
	{
		$apiCall = new ApiCall();
		$response = [
			'data' => [
				'records' => [
					[
						'id' => 1,
						'elements' => ['objektart' => 'haus'],
					],
				],
			],
		];

		$method = new ReflectionMethod(ApiCall::class, 'filterRecords');
		$method->setAccessible(true);
		$method->invokeArgs($apiCall, [&$response, []]);

		$this->assertArrayHasKey('raw', $response);
		$this->assertArrayHasKey('data', $response['raw']);
		$this->assertArrayHasKey('records', $response['raw']['data']);
		$this->assertCount(1, $response['raw']['data']['records']);
		$this->assertEquals(1, $response['raw']['data']['records'][0]['id']);
		$this->assertArrayHasKey('types', $response);
		$this->assertIsArray($response['types']);
	}


	/**
	 * filterRecords should not crash when geo filter has no min/max keys.
	 */
	public function testFilterRecords_geoFilterNullSafeMinMax()
	{
		$apiCall = new ApiCall();
		$response = [
			'data' => [
				'records' => [
					[
						'id' => 1,
						'elements' => ['laengengrad' => 7.0, 'breitengrad' => 51.0],
					],
				],
			],
			'raw' => [
				'data' => [
					'records' => [
						['id' => 1, 'elements' => ['laengengrad' => 7.0, 'breitengrad' => 51.0]],
					],
				],
			],
			'types' => [],
		];

		$method = new ReflectionMethod(ApiCall::class, 'filterRecords');
		$method->setAccessible(true);

		$filter = ['geo' => [['op' => 'geo', 'val' => 10, 'loc' => '7.0,51.0']]];
		$method->invokeArgs($apiCall, [&$response, $filter]);

		$this->assertCount(1, $response['data']['records']);
	}


	/**
	 * filterRecords should filter out records beyond the geo radius.
	 */
	public function testFilterRecords_geoFilterRemovesDistantRecords()
	{
		$apiCall = new ApiCall();
		$response = [
			'data' => [
				'records' => [
					[
						'id' => 1,
						'elements' => ['laengengrad' => 7.0, 'breitengrad' => 51.0],
					],
					[
						'id' => 2,
						'elements' => ['laengengrad' => 13.4, 'breitengrad' => 52.5],
					],
				],
			],
			'raw' => [
				'data' => [
					'records' => [
						['id' => 1, 'elements' => ['laengengrad' => 7.0, 'breitengrad' => 51.0]],
						['id' => 2, 'elements' => ['laengengrad' => 13.4, 'breitengrad' => 52.5]],
					],
				],
			],
			'types' => [],
		];

		$method = new ReflectionMethod(ApiCall::class, 'filterRecords');
		$method->setAccessible(true);

		$filter = ['geo' => [['op' => 'geo', 'val' => 1, 'loc' => '7.0,51.0']]];
		$method->invokeArgs($apiCall, [&$response, $filter]);

		$this->assertCount(1, $response['data']['records']);
		$this->assertEquals(1, $response['data']['records'][0]['id']);
		$this->assertArrayHasKey('geo_distance', $response['data']['records'][0]['elements']);
	}


	/**
	 * applyListCacheFiltering should not require raw/types when geo filter is present.
	 */
	public function testApplyListCacheFiltering_geoFilterBypassesRawTypesCheck()
	{
		$apiCall = new ApiCall();
		$response = [
			'data' => [
				'meta' => ['cntabsolute' => 1],
				'records' => [
					[
						'id' => 1,
						'elements' => ['laengengrad' => 7.0, 'breitengrad' => 51.0, 'kaufpreis' => 100000],
					],
				],
			],
		];

		$method = new ReflectionMethod(ApiCall::class, 'applyListCacheFiltering');
		$method->setAccessible(true);

		$params = [
			'formatoutput' => true,
			'filter' => ['geo' => [['op' => 'geo', 'val' => 10, 'loc' => '7.0,51.0']]],
			'listlimit' => 20,
			'listoffset' => 0,
		];

		$result = $method->invokeArgs($apiCall, [$response, $params]);

		$this->assertArrayHasKey('data', $result);
		$this->assertArrayHasKey('records', $result['data']);
	}

	/**
	 * Geo max should cap the complete result before pagination is applied.
	 */
	public function testApplyListCacheFiltering_geoMaxCapsTotalBeforePagination()
	{
		$apiCall = new ApiCall();
		$records = [];
		for ($id = 1; $id <= 5; $id++) {
			$records[] = [
				'id' => $id,
				'elements' => [
					'laengengrad' => 7.0 + ($id / 1000),
					'breitengrad' => 51.0,
				],
			];
		}

		$response = [
			'data' => [
				'meta' => ['cntabsolute' => 5],
				'records' => $records,
			],
		];

		$method = new ReflectionMethod(ApiCall::class, 'applyListCacheFiltering');
		$method->setAccessible(true);

		$params = [
			'formatoutput' => true,
			'filter' => ['geo' => [[
				'op' => 'geo',
				'val' => 200,
				'loc' => '7.0,51.0',
				'max' => 3,
			]]],
			'listlimit' => 2,
			'listoffset' => 2,
		];

		$result = $method->invokeArgs($apiCall, [$response, $params]);

		$this->assertEquals(3, $result['data']['meta']['cntabsolute']);
		$this->assertCount(1, $result['data']['records']);
		$this->assertEquals(3, $result['data']['records'][0]['id']);
	}


	/**
	 * applyListCacheFiltering should early-return when no geo filter and raw/types missing.
	 */
	public function testApplyListCacheFiltering_withoutGeoFilterRequiresRawTypes()
	{
		$apiCall = new ApiCall();
		$response = [
			'data' => [
				'meta' => ['cntabsolute' => 1],
				'records' => [
					[
						'id' => 1,
						'elements' => ['kaufpreis' => 100000],
					],
				],
			],
		];

		$method = new ReflectionMethod(ApiCall::class, 'applyListCacheFiltering');
		$method->setAccessible(true);

		$params = [
			'formatoutput' => true,
			'filter' => ['kaufpreis' => [['op' => '=', 'val' => 100000]]],
			'listlimit' => 20,
			'listoffset' => 0,
		];

		$originalResponse = $response;
		$result = $method->invokeArgs($apiCall, [$response, $params]);

		$this->assertEquals($originalResponse, $result);
	}


	/**
	 * A live miss that returns only a partial page of a list-cache request (records <
	 * cntabsolute) must NOT be written to the cache: the listname key ignores listoffset,
	 * so a partial page would truncate the list on the next read.
	 */
	public function testWriteCacheForResponses_skipsPartialListCachePage()
	{
		$apiCall = new ApiCall();

		$cache = $this->getMockBuilder(onOfficeSDKCache::class)
			->disableOriginalConstructor()
			->getMock();
		$cache->method('getHttpResponseByParameterArray')->willReturn(null);
		$cache->expects($this->never())->method('write');
		$apiCall->addCache($cache);

		$this->queueListCacheRequest($apiCall, 'Alle Immobilien - Karte');
		$apiCall->sendRequests('someToken', 'someSecret',
			$this->httpFetchReturning($this->buildEstateListResult(500, 800)));
	}


	/**
	 * A live miss that returns the complete list (records == cntabsolute) IS written to
	 * the cache.
	 */
	public function testWriteCacheForResponses_writesFullListCacheSet()
	{
		$apiCall = new ApiCall();

		$cache = $this->getMockBuilder(onOfficeSDKCache::class)
			->disableOriginalConstructor()
			->getMock();
		$cache->method('getHttpResponseByParameterArray')->willReturn(null);
		$cache->expects($this->once())->method('write');
		$apiCall->addCache($cache);

		$this->queueListCacheRequest($apiCall, 'Kleine Liste');
		$apiCall->sendRequests('someToken', 'someSecret',
			$this->httpFetchReturning($this->buildEstateListResult(120, 120)));
	}


	/**
	 * A NON-list request (no params_list_cache) always caches, even when it returns fewer
	 * records than cntabsolute — the skip only applies to list-cache requests.
	 */
	public function testWriteCacheForResponses_nonListRequestAlwaysCaches()
	{
		$apiCall = new ApiCall();

		$cache = $this->getMockBuilder(onOfficeSDKCache::class)
			->disableOriginalConstructor()
			->getMock();
		$cache->method('getHttpResponseByParameterArray')->willReturn(null);
		$cache->expects($this->once())->method('write');
		$apiCall->addCache($cache);

		// No params_list_cache in the request parameters.
		$apiCall->callByRawData(
			'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'', '', 'estate',
			['listlimit' => 500, 'listoffset' => 0, 'formatoutput' => true]
		);
		$apiCall->sendRequests('someToken', 'someSecret',
			$this->httpFetchReturning($this->buildEstateListResult(500, 800)));
	}


	/**
	 * When cntabsolute is missing/invalid the shape cannot be judged, so the conservative
	 * default is to cache normally.
	 */
	public function testWriteCacheForResponses_invalidMetaCachesNormally()
	{
		$apiCall = new ApiCall();

		$cache = $this->getMockBuilder(onOfficeSDKCache::class)
			->disableOriginalConstructor()
			->getMock();
		$cache->method('getHttpResponseByParameterArray')->willReturn(null);
		$cache->expects($this->once())->method('write');
		$apiCall->addCache($cache);

		$result = $this->buildEstateListResult(500, 800);
		unset($result['data']['meta']); // no cntabsolute -> cannot tell -> cache normally

		$this->queueListCacheRequest($apiCall, 'Liste ohne Meta');
		$apiCall->sendRequests('someToken', 'someSecret', $this->httpFetchReturning($result));
	}


	/**
	 * Queue a list-cache estate read (listname + params_list_cache) on the given ApiCall.
	 *
	 * @param ApiCall $apiCall
	 * @param string $listName
	 */
	private function queueListCacheRequest(ApiCall $apiCall, string $listName): void
	{
		$apiCall->callByRawData(
			'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'',
			'',
			'estate',
			[
				'listname' => $listName,
				'params_list_cache' => ['listname' => $listName],
				'listlimit' => 500,
				'listoffset' => 0,
				'formatoutput' => true,
			]
		);
	}


	/**
	 * Build an HttpFetch mock whose send() returns a single-result API response.
	 *
	 * @param array $result
	 * @return HttpFetch
	 */
	private function httpFetchReturning(array $result): HttpFetch
	{
		$httpFetch = $this->getMockBuilder(HttpFetch::class)
			->disableOriginalConstructor()
			->onlyMethods(['send'])
			->getMock();
		$httpFetch->method('send')->willReturn(json_encode([
			'response' => ['results' => [0 => $result]],
		]));

		return $httpFetch;
	}


	/**
	 * Build a cacheable estate list HTTP result with $recordCount records and a
	 * cntabsolute of $cntAbsolute.
	 *
	 * @param int $recordCount
	 * @param int $cntAbsolute
	 * @return array
	 */
	private function buildEstateListResult(int $recordCount, int $cntAbsolute): array
	{
		$records = [];
		for ($i = 0; $i < $recordCount; $i++) {
			$records[] = ['id' => $i + 1, 'type' => 'estate', 'elements' => []];
		}

		return [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:read',
			'resourceid' => '',
			'resourcetype' => 'estate',
			'cacheable' => true,
			'identifier' => '',
			'data' => [
				'meta' => ['cntabsolute' => $cntAbsolute],
				'records' => $records,
			],
			'status' => ['errorcode' => 0, 'message' => 'OK'],
		];
	}
}
