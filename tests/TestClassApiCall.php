<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

use onOffice\SDK\internal\ApiCall;
use onOffice\SDK\internal\HttpFetch;
use ReflectionProperty;
use WP_UnitTestCase;

/**
 * Cache-key handling in ApiCall::collectOrGatherRequests().
 *
 * A list request carries the parameters the cron warmed the cache with in
 * params_list_cache. The cache is looked up under those, so every page and every
 * search of one list shares the warmed entry and gets its own slice from
 * applyListCacheFiltering(). Duplicate detection must not share that key.
 *
 * Note: SDK/tests/ApiCallTest.php is not part of the phpunit.xml.dist test suite
 * (it neither lives in ./tests/ nor uses the Test prefix), so these live here to
 * actually run in CI.
 *
 * @covers onOffice\SDK\internal\ApiCall
 */
class TestClassApiCall
	extends WP_UnitTestCase
{
	/**
	 * ApiCall::$_inMemoryCache is static, so responses cached by one test would leak
	 * into the next and turn an intended HTTP call into a silent cache hit.
	 *
	 * @before
	 */
	public function resetInMemoryCache()
	{
		$pInMemoryCache = new ReflectionProperty(ApiCall::class, '_inMemoryCache');
		$pInMemoryCache->setAccessible(true);
		$pInMemoryCache->setValue(null, []);
	}


	/**
	 * Two searches on the same list share one cache entry on purpose - the entry the
	 * cron warmed with the list's default filter. Duplicate detection must NOT share
	 * that key: with a single key the second search is mistaken for a duplicate of the
	 * first and served the first search's records, i.e. the wrong estates.
	 */
	public function testDifferentSearchesOnTheSameListAreNotTreatedAsDuplicates()
	{
		$pApiCall = new ApiCall();
		$listCacheParameters = $this->buildListCacheParameters();

		$buy = $this->queueSearch($pApiCall, $listCacheParameters, ['vermarktungsart' => [['op' => 'in', 'val' => ['kauf']]]]);
		$rent = $this->queueSearch($pApiCall, $listCacheParameters, ['vermarktungsart' => [['op' => 'in', 'val' => ['miete']]]]);
		$this->assertNotSame($buy, $rent);

		$pApiCall->sendRequests('someToken', 'someSecret', $this->buildHttpFetch([
			$this->buildRecordsResult([11, 12]),
			$this->buildRecordsResult([21, 22]),
		]), false);

		$this->assertSame([11, 12], $this->recordIds($pApiCall->getResponse($buy)));
		$this->assertSame([21, 22], $this->recordIds($pApiCall->getResponse($rent)));
	}


	/**
	 * Same search queued twice is a real duplicate and must still collapse into one
	 * HTTP action.
	 */
	public function testTheSameSearchQueuedTwiceStillDeduplicates()
	{
		$pApiCall = new ApiCall();
		$listCacheParameters = $this->buildListCacheParameters();
		$search = ['vermarktungsart' => [['op' => 'in', 'val' => ['kauf']]]];

		$first = $this->queueSearch($pApiCall, $listCacheParameters, $search);
		$second = $this->queueSearch($pApiCall, $listCacheParameters, $search);

		$pHttpFetch = $this->buildHttpFetch([$this->buildRecordsResult([11, 12])], $this->once());
		$pApiCall->sendRequests('someToken', 'someSecret', $pHttpFetch, false);

		$this->assertSame([11, 12], $this->recordIds($pApiCall->getResponse($first)));
		$this->assertSame([11, 12], $this->recordIds($pApiCall->getResponse($second)));
	}


	/**
	 * Requests without params_list_cache must behave exactly as before the key split:
	 * both keys are identical, so identical parameters collapse into one HTTP action.
	 */
	public function testIdenticalRequestsWithoutListCacheStillDeduplicate()
	{
		$pApiCall = new ApiCall();
		$parameters = ['data' => ['Id'], 'listlimit' => 10];

		$first = $pApiCall->callByRawData('read', '', null, 'estate', $parameters);
		$second = $pApiCall->callByRawData('read', '', null, 'estate', $parameters);

		$pHttpFetch = $this->buildHttpFetch([$this->buildRecordsResult([7])], $this->once());
		$pApiCall->sendRequests('someToken', 'someSecret', $pHttpFetch, false);

		$this->assertSame([7], $this->recordIds($pApiCall->getResponse($first)));
		$this->assertSame([7], $this->recordIds($pApiCall->getResponse($second)));
	}


	/**
	 * Different requests without params_list_cache stay separate, too.
	 */
	public function testDifferentRequestsWithoutListCacheStaySeparate()
	{
		$pApiCall = new ApiCall();

		$first = $pApiCall->callByRawData('read', '', null, 'estate', ['data' => ['Id'], 'listlimit' => 10]);
		$second = $pApiCall->callByRawData('read', '', null, 'estate', ['data' => ['Id'], 'listlimit' => 20]);

		$pApiCall->sendRequests('someToken', 'someSecret', $this->buildHttpFetch([
			$this->buildRecordsResult([1]),
			$this->buildRecordsResult([2]),
		]), false);

		$this->assertSame([1], $this->recordIds($pApiCall->getResponse($first)));
		$this->assertSame([2], $this->recordIds($pApiCall->getResponse($second)));
	}


	/**
	 * The parameters SDKWrapper::renewCache() writes the formatted list entry under.
	 *
	 * @return array
	 */
	private function buildListCacheParameters(): array
	{
		return [
			'listname' => 'Alle Immobilien',
			'data' => ['Id'],
			'filter' => ['veroeffentlichen' => [['op' => '=', 'val' => 1]]],
			'outputlanguage' => 'DEU',
			'formatoutput' => true,
			'listlimit' => 500,
		];
	}


	/**
	 * Queue one paginated frontend list request with an active search, shaped like
	 * EstateList::getEstateParameters() builds it.
	 *
	 * @param ApiCall $pApiCall
	 * @param array $listCacheParameters
	 * @param array $searchFilter
	 * @return int request handle
	 */
	private function queueSearch(ApiCall $pApiCall, array $listCacheParameters, array $searchFilter): int
	{
		return $pApiCall->callByRawData('read', '', null, 'estate', [
			'listname' => $listCacheParameters['listname'],
			'data' => $listCacheParameters['data'],
			'filter' => array_merge($listCacheParameters['filter'], $searchFilter),
			'outputlanguage' => $listCacheParameters['outputlanguage'],
			'formatoutput' => true,
			'listlimit' => 16,
			'listoffset' => 0,
			'params_list_cache' => $listCacheParameters,
		]);
	}


	/**
	 * @param array $results One entry per expected API action, in queue order.
	 * @param mixed $sendExpectation Optional PHPUnit invocation rule for send().
	 * @return HttpFetch
	 */
	private function buildHttpFetch(array $results, $sendExpectation = null): HttpFetch
	{
		$pHttpFetch = $this->getMockBuilder(HttpFetch::class)
			->disableOriginalConstructor()
			->onlyMethods(['send'])
			->getMock();

		$pInvocation = $sendExpectation === null
			? $pHttpFetch->method('send')
			: $pHttpFetch->expects($sendExpectation)->method('send');
		$pInvocation->willReturn(json_encode(['response' => ['results' => $results]]));

		return $pHttpFetch;
	}


	/**
	 * @param int[] $ids
	 * @return array
	 */
	private function buildRecordsResult(array $ids): array
	{
		$records = [];
		foreach ($ids as $id) {
			$records[] = ['id' => $id, 'type' => 'estate', 'elements' => ['Id' => $id]];
		}

		return [
			'actionid' => 'read',
			'resourcetype' => 'estate',
			'status' => ['errorcode' => 0],
			'data' => ['meta' => ['cntabsolute' => count($ids)], 'records' => $records],
		];
	}


	/**
	 * @param array|null $response
	 * @return int[]
	 */
	private function recordIds($response): array
	{
		return array_map('intval', array_column($response['data']['records'] ?? [], 'id'));
	}
}
