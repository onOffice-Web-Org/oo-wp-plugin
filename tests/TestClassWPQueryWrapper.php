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

use onOffice\WPlugin\WP\WPQueryWrapper;
use WP_Query;
use WP_UnitTestCase;


/**
 *
 */

class TestClassWPQueryWrapper
	extends WP_UnitTestCase
{
	/**
	 *
	 */
	public function testGetWPQuery()
	{
		$pWPQueryWrapper = new WPQueryWrapper();
		$this->assertInstanceOf(WP_Query::class, $pWPQueryWrapper->getWPQuery());
	}

	/**
	 *
	 */
	public function testGetWpQueryOnlyWithPage()
	{
		global $wp_query;
		$wp_query = new WP_Query(['page' => 2]);
		$pWPQueryWrapper = new WPQueryWrapper();
		$this->assertEquals(2, $pWPQueryWrapper->getWPQuery()->get('paged'));
		wp_reset_query();
	}

	/**
	 *
	 */
	public function testGetWpQueryWithPagedAndPage()
	{
		global $wp_query;
		$wp_query = new WP_Query(['page' => 2, 'paged'=> 3]);
		$pWPQueryWrapper = new WPQueryWrapper();
		$this->assertEquals(3, $pWPQueryWrapper->getWPQuery()->get('paged'));
		wp_reset_query();
	}

	/**
	 *
	 */
	public function testGetWpQueryWithMultiplePage()
	{
		$_GET = [
			'page_of_id_4' => 4
		];
		global $wp_query;
		$wp_query = new WP_Query(['page' => 2, 'paged'=> 3]);
		$pWPQueryWrapper = new WPQueryWrapper();
		$this->assertEquals(4, $pWPQueryWrapper->getWPQuery(4)->get('paged'));
		wp_reset_query();
	}
}