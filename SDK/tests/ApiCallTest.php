<?php

namespace Tests\onOffice\SDK;

use onOffice\SDK\Cache\onOfficeSDKCache;
use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\SDK\internal\ApiAction;
use onOffice\SDK\internal\ApiCall;
use onOffice\SDK\internal\HttpFetch;
use onOffice\SDK\internal\Request;
use onOffice\SDK\internal\Response;
use ReflectionMethod;
use ReflectionProperty;

class ApiCallTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * ApiCall::$_inMemoryCache is static, so responses cached by one test are visible to
	 * every later test in the same process. Without this reset a test that queues the
	 * same parameters as an earlier one gets an in-memory cache hit, skips the HTTP call
	 * and silently tests nothing, which makes results depend on execution order.
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$inMemoryCache = new ReflectionProperty(ApiCall::class, '_inMemoryCache');
		$inMemoryCache->setAccessible(true);
		$inMemoryCache->setValue(null, []);
	}


	/**
	 * @return onOfficeSDKCache&\PHPUnit\Framework\MockObject\MockObject
	 */
	private function createCacheMock()
	{
		// onOfficeSDKCache declares a constructor, so the generated mock must not call it.
		return $this->getMockBuilder(onOfficeSDKCache::class)
			->disableOriginalConstructor()
			->getMock();
	}


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
		$cache = $this->createCacheMock();

		$apiCall = new ApiCall();
		$apiCall->addCache($cache);
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testRemoveCacheInstances()
	{
		$cache = $this->createCacheMock();

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
	 * @param array $parameters e.g. ['listname' => 'default']
	 * @param array $responseData
	 * @return Response
	 */
	private function buildCacheableResponse(array $parameters, array $responseData): Response
	{
		$pApiAction = new ApiAction('someActionId', 'someResourceType', $parameters, 'someResourceId', 'someIdentifier');
		$pRequest = new Request($pApiAction);

		return new Response($pRequest, $responseData);
	}


	/**
	 * @param array $parameters request parameters, e.g. ['listname' => 'default']
	 * @param array $data the data part of the API response
	 * @return array [$cacheMock, $invokeWriteCache]
	 */
	private function prepareWriteCacheForResponses(array $parameters, array $data): array
	{
		$apiCall = new ApiCall();

		$cache = $this->createCacheMock();
		$apiCall->addCache($cache);

		$response = $this->buildCacheableResponse($parameters, [
			'actionid' => 'someActionId',
			'resourcetype' => 'someResourceType',
			'cacheable' => true,
			'data' => $data,
		]);

		$requestId = $response->getRequest()->getRequestId();
		$responsesProperty = new ReflectionProperty(ApiCall::class, '_responses');
		$responsesProperty->setAccessible(true);
		$responsesProperty->setValue($apiCall, [$requestId => $response]);

		$invokeWriteCache = function () use ($apiCall, $requestId) {
			$method = new ReflectionMethod(ApiCall::class, 'writeCacheForResponses');
			$method->setAccessible(true);
			$method->invokeArgs($apiCall, [[$requestId]]);
		};

		return [$cache, $invokeWriteCache];
	}


	/**
	 * A partial page (fewer records than cntabsolute) for a listname request must not
	 * be written to the cache, since the listname cache key must always hold the
	 * complete record set.
	 */
	public function testWriteCacheForResponses_skipsPartialListPage()
	{
		list($cache, $invokeWriteCache) = $this->prepareWriteCacheForResponses(
			['listname' => 'default'],
			[
				'records' => [['id' => 1], ['id' => 2]],
				'meta' => ['cntabsolute' => 5],
			]
		);

		$cache->expects($this->never())->method('write');

		$invokeWriteCache();
	}


	/**
	 * Some resource types report cntabsolute as an array. The partial page must still
	 * be recognized.
	 */
	public function testWriteCacheForResponses_skipsPartialListPageWithCntAbsoluteAsArray()
	{
		list($cache, $invokeWriteCache) = $this->prepareWriteCacheForResponses(
			['listname' => 'default'],
			[
				'records' => [['id' => 1], ['id' => 2]],
				'meta' => ['cntabsolute' => [5]],
			]
		);

		$cache->expects($this->never())->method('write');

		$invokeWriteCache();
	}


	/**
	 * A response for a page beyond the first can never hold the complete record set,
	 * so it must be skipped even when the response carries no usable cntabsolute.
	 */
	public function testWriteCacheForResponses_skipsListPageBeyondFirstPageWithoutCntAbsolute()
	{
		list($cache, $invokeWriteCache) = $this->prepareWriteCacheForResponses(
			['listname' => 'default', 'listoffset' => 20, 'listlimit' => 20],
			[
				'records' => [['id' => 21], ['id' => 22]],
				'meta' => [],
			]
		);

		$cache->expects($this->never())->method('write');

		$invokeWriteCache();
	}


	/**
	 * A complete page (records count equals cntabsolute) for a listname request
	 * must be written to the cache.
	 */
	public function testWriteCacheForResponses_writesCompleteListPage()
	{
		$data = [
			'records' => [['id' => 1], ['id' => 2]],
			'meta' => ['cntabsolute' => 2],
		];

		list($cache, $invokeWriteCache) = $this->prepareWriteCacheForResponses(
			['listname' => 'default', 'listoffset' => 0],
			$data
		);

		$cache->expects($this->once())
			->method('write')
			->with($this->anything(), $this->callback(function ($value) use ($data) {
				return unserialize($value)['data'] === $data;
			}));

		$invokeWriteCache();
	}


	/**
	 * Requests without a listname parameter must be cached regardless of how the
	 * record count compares to cntabsolute, since the partial-page guard only
	 * applies to listname cache entries.
	 */
	public function testWriteCacheForResponses_writesNonListResponseEvenIfPartial()
	{
		list($cache, $invokeWriteCache) = $this->prepareWriteCacheForResponses(
			[],
			[
				'records' => [['id' => 1]],
				'meta' => ['cntabsolute' => 5],
			]
		);

		$cache->expects($this->once())->method('write');

		$invokeWriteCache();
	}


	/**
	 * Without a listname the cache key covers all parameters including listoffset, so
	 * paged responses stay cacheable.
	 */
	public function testWriteCacheForResponses_writesNonListResponseBeyondFirstPage()
	{
		list($cache, $invokeWriteCache) = $this->prepareWriteCacheForResponses(
			['listoffset' => 20, 'listlimit' => 20],
			[
				'records' => [['id' => 21], ['id' => 22]],
				'meta' => ['cntabsolute' => 50],
			]
		);

		$cache->expects($this->once())->method('write');

		$invokeWriteCache();
	}
}
