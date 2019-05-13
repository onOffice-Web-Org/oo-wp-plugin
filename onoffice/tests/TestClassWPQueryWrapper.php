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
	 * @global WP_Query $wp_query
	 *
	 */

	public function testGetWPQuery()
	{
		$pWPQueryWrapper = new WPQueryWrapper();
		global $wp_query;
		$this->assertInstanceOf(WP_Query::class, $pWPQueryWrapper->getWPQuery());
		$this->assertSame($wp_query, $pWPQueryWrapper->getWPQuery());
	}
}