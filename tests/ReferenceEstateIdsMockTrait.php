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

declare(strict_types=1);

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Filter\ReferenceEstateFilterBuilder;

/**
 * Hiding reference estates resolves their ids once and excludes them by Id.
 * Tests that exercise a list view with that setting have to account for the
 * lookup, otherwise it fails and the builder silently falls back to the legacy
 * `referenz = 0` filter — which would make the test pass without ever touching
 * the new behaviour.
 */
trait ReferenceEstateIdsMockTrait
{
	/** Ids the tests expect to be excluded. */
	const REFERENCE_ESTATE_IDS = [9001, 9002];

	/**
	 * Registers the answer to the lookup that resolves the reference estate ids.
	 *
	 * @param SDKWrapperMocker $pSDKWrapperMocker
	 * @param int[] $ids
	 * @param int $offset
	 */
	protected function addReferenceEstatesLookupResponse(
		SDKWrapperMocker $pSDKWrapperMocker, array $ids, int $offset = 0)
	{
		$records = [];

		foreach ($ids as $id) {
			$records[] = ['id' => $id, 'type' => 'estate', 'elements' => ['Id' => (string) $id]];
		}

		$pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'estate', '', [
				'data' => ['Id'],
				'filter' => ['referenz' => [['op' => '=', 'val' => 1]]],
				'listlimit' => 500,
				'listoffset' => $offset,
				'formatoutput' => false,
			], null, [
				'actionid' => onOfficeSDK::ACTION_ID_READ,
				'resourceid' => '',
				'resourcetype' => 'estate',
				'cacheable' => true,
				'identifier' => '',
				'data' => [
					'meta' => ['cntabsolute' => count($ids)],
					'records' => $records,
				],
				'status' => ['errorcode' => 0, 'message' => 'OK'],
			]);
	}

	/**
	 * Resolves the ids up front on a throwaway wrapper, so the wrapper a test
	 * asserts against never sees the lookup and its recorded request counts stay
	 * about the calls the test actually cares about. Mirrors production, where
	 * the transient keeps the lookup out of the per page request cycle.
	 *
	 * @param int[] $ids
	 */
	protected function warmReferenceEstateIds(array $ids = self::REFERENCE_ESTATE_IDS)
	{
		ReferenceEstateFilterBuilder::resetCache();

		$pWarmupMocker = new SDKWrapperMocker();
		$this->addReferenceEstatesLookupResponse($pWarmupMocker, $ids);

		(new ReferenceEstateFilterBuilder($pWarmupMocker))->addHideFilter([]);
	}

	/**
	 * The filter condition "hide reference estates" is expected to produce.
	 *
	 * @param int[] $ids
	 * @return array
	 */
	protected static function expectedHideReferenceCondition(
		array $ids = self::REFERENCE_ESTATE_IDS): array
	{
		return [['op' => 'NOT IN', 'val' => $ids]];
	}

	/**
	 * Keeps the resolved ids from leaking into test classes that do not warm
	 * them themselves.
	 *
	 * @after
	 */
	protected function resetReferenceEstateIds()
	{
		ReferenceEstateFilterBuilder::resetCache();
	}
}
