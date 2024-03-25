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
use onOffice\WPlugin\DataView\DataListView;
use WP_UnitTestCase;
use function __;

class TestClassSortListTypes
	extends WP_UnitTestCase
{
	/** @var DataListView */
	private $_pListView = null;

	/**
	 * @covers \onOffice\WPlugin\Controller\SortList\SortListTypes::getSortUrlPrameter
	 */
	public function testGetSortUrlParameter()
	{
		$this->_pListView = new DataListView(1, 'test');
		$expectedValues = ['sortby_id_1', 'sortorder_id_1'];
		$value = SortListTypes::getSortUrlPrameter($this->_pListView->getId());

		$this->assertEqualSets($expectedValues, $value);
	}

	/**
	 * @covers \onOffice\WPlugin\Controller\SortList\SortListTypes::getSortOrderMapping
	 */
	public function testGetSortOrderMapping()
	{
		$this->assertEquals( __('ascending', 'onoffice-for-wp-websites'), SortListTypes::getSortOrderMapping(1, 'ASC'));
		$this->assertEquals( __('highest first', 'onoffice-for-wp-websites'), SortListTypes::getSortOrderMapping(0, 'DESC'));
	}

	/**
	 *  @covers \onOffice\WPlugin\Controller\SortList\SortListTypes::getSortOrder
	 */
	public function testGetSortOrder()
	{
		$expectedValue = [
			0 => [
				'ASC' => __('lowest first', 'onoffice-for-wp-websites'),
				'DESC' => __('highest first', 'onoffice-for-wp-websites'),
			],
			1 => [
				'ASC' => __('ascending', 'onoffice-for-wp-websites'),
				'DESC' => __('descending', 'onoffice-for-wp-websites'),
			],
		];

		$this->assertEquals($expectedValue, SortListTypes::getSortOrder());
	}
}
