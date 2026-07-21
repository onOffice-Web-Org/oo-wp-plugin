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
}
