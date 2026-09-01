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

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Filter\ReferenceEstateFilterBuilder;
use WP_UnitTestCase;

/**
 * Hiding reference estates must not depend on `referenz` carrying a value:
 * estates whose `referenz` is NULL are no reference estates, yet they satisfy
 * neither `referenz = 0` nor `referenz = 1`. Excluding the known reference
 * estates by Id keeps them in the list.
 */
class TestClassReferenceEstateFilterBuilder
	extends WP_UnitTestCase
{
	use ReferenceEstateIdsMockTrait;

	/** @var SDKWrapperMocker */
	private $_pSDKWrapperMocker = null;

	/** Filter every caller starts from. */
	const BASE_FILTER = ['veroeffentlichen' => [['op' => '=', 'val' => 1]]];

	/**
	 *
	 */
	public function setUp(): void
	{
		parent::setUp();
		ReferenceEstateFilterBuilder::resetCache();
		$this->_pSDKWrapperMocker = new SDKWrapperMocker();
	}

	/**
	 *
	 */
	public function tearDown(): void
	{
		ReferenceEstateFilterBuilder::resetCache();
		parent::tearDown();
	}

	/**
	 * The reported defect: hiding reference estates must exclude them by Id,
	 * so an estate whose `referenz` is NULL survives the filter.
	 */
	public function testHideExcludesReferenceEstatesById()
	{
		$this->addReferenceEstatesLookupResponse($this->_pSDKWrapperMocker, [12, 34, 56]);
		$pBuilder = new ReferenceEstateFilterBuilder($this->_pSDKWrapperMocker);

		$filter = $pBuilder->addFilter(self::BASE_FILTER, DataListView::HIDE_REFERENCE_ESTATE);

		$this->assertEquals([
			'veroeffentlichen' => [['op' => '=', 'val' => 1]],
			'Id' => [['op' => 'NOT IN', 'val' => [12, 34, 56]]],
		], $filter);
		$this->assertArrayNotHasKey('referenz', $filter);
	}

	/**
	 * Without any reference estate there is nothing to exclude. Adding a
	 * condition anyway could only drop estates that should be listed.
	 */
	public function testHideAddsNoConditionWithoutReferenceEstates()
	{
		$this->addReferenceEstatesLookupResponse($this->_pSDKWrapperMocker, []);
		$pBuilder = new ReferenceEstateFilterBuilder($this->_pSDKWrapperMocker);

		$filter = $pBuilder->addFilter(self::BASE_FILTER, DataListView::HIDE_REFERENCE_ESTATE);

		$this->assertEquals(self::BASE_FILTER, $filter);
	}

	/**
	 * Showing reference estates alongside the others restricts nothing.
	 */
	public function testShowLeavesFilterUntouched()
	{
		$pBuilder = new ReferenceEstateFilterBuilder($this->_pSDKWrapperMocker);

		$filter = $pBuilder->addFilter(self::BASE_FILTER, DataListView::SHOW_REFERENCE_ESTATE);

		$this->assertEquals(self::BASE_FILTER, $filter);
	}

	/**
	 * `referenz = 1` is a positive match on a value that is set, so it stays.
	 */
	public function testShowOnlyStillMatchesOnTheField()
	{
		$pBuilder = new ReferenceEstateFilterBuilder($this->_pSDKWrapperMocker);

		$filter = $pBuilder->addFilter(self::BASE_FILTER, DataListView::SHOW_ONLY_REFERENCE_ESTATE);

		$this->assertEquals([
			'veroeffentlichen' => [['op' => '=', 'val' => 1]],
			'referenz' => [['op' => '=', 'val' => 1]],
		], $filter);
	}

	/**
	 * A failing lookup must not take the estate list down with it. The legacy
	 * filter still hides every estate that carries a value.
	 */
	public function testFailingLookupFallsBackToLegacyFilter()
	{
		// No response registered: the lookup fails and the fallback applies.
		$pBuilder = new ReferenceEstateFilterBuilder($this->_pSDKWrapperMocker);

		$filter = $pBuilder->addFilter(self::BASE_FILTER, DataListView::HIDE_REFERENCE_ESTATE);

		$this->assertEquals([
			'veroeffentlichen' => [['op' => '=', 'val' => 1]],
			'referenz' => [['op' => '=', 'val' => 0]],
		], $filter);
	}

	/**
	 * The detail view's access restriction uses the same NULL safe exclusion.
	 */
	public function testHideFilterIsReusedByTheDetailViewRestriction()
	{
		$this->addReferenceEstatesLookupResponse($this->_pSDKWrapperMocker, [7]);
		$pBuilder = new ReferenceEstateFilterBuilder($this->_pSDKWrapperMocker);

		$filter = $pBuilder->addHideFilter([]);

		$this->assertEquals(['Id' => [['op' => 'NOT IN', 'val' => [7]]]], $filter);
	}

	/**
	 * The ids are resolved once and then reused, so listing pages do not issue
	 * an additional API request each.
	 */
	public function testIdsAreResolvedOnlyOnce()
	{
		$this->addReferenceEstatesLookupResponse($this->_pSDKWrapperMocker, [12]);
		$pBuilder = new ReferenceEstateFilterBuilder($this->_pSDKWrapperMocker);

		$pBuilder->addFilter(self::BASE_FILTER, DataListView::HIDE_REFERENCE_ESTATE);
		$requestsAfterFirst = count($this->_pSDKWrapperMocker->getRequestArray());

		$pBuilder->addFilter(self::BASE_FILTER, DataListView::HIDE_REFERENCE_ESTATE);
		(new ReferenceEstateFilterBuilder($this->_pSDKWrapperMocker))
			->addFilter(self::BASE_FILTER, DataListView::HIDE_REFERENCE_ESTATE);

		$this->assertSame($requestsAfterFirst, count($this->_pSDKWrapperMocker->getRequestArray()));
	}

	/**
	 * An existing Id restriction must survive, the exclusion is added on top.
	 */
	public function testExistingIdConditionIsKept()
	{
		$this->addReferenceEstatesLookupResponse($this->_pSDKWrapperMocker, [12]);
		$pBuilder = new ReferenceEstateFilterBuilder($this->_pSDKWrapperMocker);

		$filter = $pBuilder->addFilter(
			['Id' => [['op' => 'in', 'val' => [1, 2, 12]]]],
			DataListView::HIDE_REFERENCE_ESTATE);

		$this->assertEquals(['Id' => [
			['op' => 'in', 'val' => [1, 2, 12]],
			['op' => 'NOT IN', 'val' => [12]],
		]], $filter);
	}
}
