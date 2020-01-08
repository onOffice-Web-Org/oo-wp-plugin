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

use onOffice\WPlugin\Controller\SortList\SortListTypes;
use WP_UnitTestCase;
use function __;

class TestClassSortListTypes
	extends WP_UnitTestCase
{
	/**
	 * @covers \onOffice\WPlugin\Controller\SortList\SortListTypes::getSortUrlPrameter
	 */
	public function testGetSortUrlParameter()
	{
		$expectedValues = ['sortorder', 'sortby'];
		$value = SortListTypes::getSortUrlPrameter();

		$this->assertEqualSets($expectedValues, $value);
	}

	/**
	 * @covers \onOffice\WPlugin\Controller\SortList\SortListTypes::getSortOrderMapping
	 */
	public function testGetSortOrderMapping()
	{
		$this->assertEquals( __('ascending', 'onoffice'), SortListTypes::getSortOrderMapping(1, 'ASC'));
		$this->assertEquals( __('highest first', 'onoffice'), SortListTypes::getSortOrderMapping(0, 'DESC'));
	}

	/**
	 *  @covers \onOffice\WPlugin\Controller\SortList\SortListTypes::getSortOrder
	 */
	public function testGetSortOrder()
	{
		$expectedValue = [
			0 => [
				'ASC' => __('lowest first', 'onoffice'),
				'DESC' => __('highest first', 'onoffice'),
			],
			1 => [
				'ASC' => __('ascending', 'onoffice'),
				'DESC' => __('descending', 'onoffice'),
			],
		];

		$this->assertEquals($expectedValue, SortListTypes::getSortOrder());
	}
}