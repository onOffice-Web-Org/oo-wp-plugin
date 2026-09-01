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

namespace onOffice\WPlugin\Filter;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\SDKWrapper;
use Throwable;

/**
 * Builds the API filter for the "reference estates" setting of a list view.
 *
 * Hiding reference estates used to be expressed as `referenz = 0`. That is the
 * complement of `referenz = 1` only as long as every estate actually carries a
 * value. Estates whose `referenz` is NULL — typically records created by an
 * import rather than by hand — satisfy NEITHER comparison: they vanish from
 * "hide reference estates" AND from "show only reference estates", while
 * "show reference estates" (which sends no filter at all) still lists them.
 *
 * Excluding the known reference estates by Id instead is NULL-safe, because Id
 * is never NULL. `referenz = 1` stays a positive match on a value that is set,
 * so it keeps identifying reference estates reliably.
 */
class ReferenceEstateFilterBuilder
{
	/**
	 * Upper bound for the exclusion list. Beyond this the request payload grows
	 * unreasonably, so the legacy filter is used instead — an installation with
	 * that many reference estates is better served by a filter than by an Id list.
	 */
	const MAX_EXCLUDED_IDS = 1000;

	/** Transient holding the resolved ids, cleared together with the list caches. */
	const TRANSIENT_KEY = 'onoffice-reference-estate-ids';

	/** @var SDKWrapper */
	private $_pSDKWrapper;

	/**
	 * Reference estate ids, resolved once per PHP request. `null` means "not
	 * resolved yet", `false` means "resolving failed, use the legacy filter".
	 *
	 * @var int[]|false|null
	 */
	private static $_referenceEstateIds = null;

	/**
	 * @param SDKWrapper $pSDKWrapper
	 */
	public function __construct(SDKWrapper $pSDKWrapper)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
	}

	/**
	 * Adds the reference estate restriction for the given setting to a filter.
	 *
	 * @param array $filter Filter to extend
	 * @param string $showReferenceEstate One of the DataListView::*_REFERENCE_ESTATE constants
	 * @return array
	 */
	public function addFilter(array $filter, string $showReferenceEstate): array
	{
		if ($showReferenceEstate === DataListView::SHOW_ONLY_REFERENCE_ESTATE) {
			$filter['referenz'][] = ['op' => '=', 'val' => 1];
			return $filter;
		}

		if ($showReferenceEstate !== DataListView::HIDE_REFERENCE_ESTATE) {
			return $filter;
		}

		return $this->addHideFilter($filter);
	}

	/**
	 * Adds the restriction that hides reference estates. Used for the list view
	 * setting and for the detail view's access restriction alike.
	 *
	 * @param array $filter
	 * @return array
	 */
	public function addHideFilter(array $filter): array
	{
		$referenceIds = $this->getReferenceEstateIds();

		if ($referenceIds === false || count($referenceIds) > self::MAX_EXCLUDED_IDS) {
			$filter['referenz'][] = ['op' => '=', 'val' => 0];
			return $filter;
		}

		// Nothing to exclude: any additional condition would only risk dropping
		// estates whose referenz is NULL.
		if ($referenceIds !== []) {
			$filter['Id'][] = ['op' => 'NOT IN', 'val' => $referenceIds];
		}

		return $filter;
	}

	/**
	 * @return int[]|false false when the ids could not be resolved
	 */
	private function getReferenceEstateIds()
	{
		if (self::$_referenceEstateIds !== null) {
			return self::$_referenceEstateIds;
		}

		// The set changes rarely, but the lookup runs on every estate list page.
		// Cache it for as long as the list caches live, so no extra API request
		// is issued per page view.
		$cachedIds = get_transient(self::TRANSIENT_KEY);
		if (is_array($cachedIds)) {
			self::$_referenceEstateIds = $cachedIds;
			return self::$_referenceEstateIds;
		}

		try {
			self::$_referenceEstateIds = $this->fetchReferenceEstateIds();
			set_transient(self::TRANSIENT_KEY, self::$_referenceEstateIds, $this->getCacheTtl());
		} catch (Throwable $pException) {
			// A failing lookup must never take the estate list down with it;
			// the legacy filter still hides every estate that carries a value.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Needed to debug a silent fallback.
			error_log('onOffice: could not resolve reference estate ids, '
				. 'falling back to the referenz filter: ' . $pException->getMessage());
			self::$_referenceEstateIds = false;
		}

		return self::$_referenceEstateIds;
	}

	/**
	 * @return int[]
	 */
	private function fetchReferenceEstateIds(): array
	{
		$ids = [];
		$offset = 0;

		do {
			$pApiClientAction = new APIClientActionGeneric
				($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, onOfficeSDK::MODULE_ESTATE);
			$pApiClientAction->setParameters([
				'data' => ['Id'],
				'filter' => ['referenz' => [['op' => '=', 'val' => 1]]],
				'listlimit' => 500,
				'listoffset' => $offset,
				'formatoutput' => false,
			]);
			$pApiClientAction->addRequestToQueue()->sendRequests();

			$records = $pApiClientAction->getResultRecords();

			foreach ($records as $record) {
				$ids[] = (int) $record['id'];
			}

			$offset += 500;
		} while (count($records) === 500 && count($ids) <= self::MAX_EXCLUDED_IDS);

		return array_values(array_unique($ids));
	}

	/**
	 * Mirrors the TTL of the list caches, so the exclusion list and the cached
	 * lists it is applied to never drift apart by more than one interval.
	 *
	 * @return int
	 */
	private function getCacheTtl(): int
	{
		$cacheSchedule = get_option('onoffice-settings-duration-cache');

		if (is_string($cacheSchedule) && $cacheSchedule !== '') {
			$schedules = wp_get_schedules();
			if (isset($schedules[$cacheSchedule]['interval'])) {
				return (int) $schedules[$cacheSchedule]['interval'];
			}
		}

		return HOUR_IN_SECONDS;
	}

	/**
	 * Drops the resolved ids. Called when the plugin caches are cleared, and
	 * needed by tests that must not reuse the set of a previous run.
	 */
	public static function resetCache()
	{
		self::$_referenceEstateIds = null;

		if (function_exists('delete_transient')) {
			delete_transient(self::TRANSIENT_KEY);
		}
	}
}
