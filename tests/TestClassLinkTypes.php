<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use Closure;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use onOffice\WPlugin\Types\LinksTypes;
use onOffice\WPlugin\Types\MovieLinkTypes;
use TypeError;
use WP_UnitTestCase;

class TestClassLinkTypes
	extends WP_UnitTestCase
{
	/**
	 *
	 */
	public function testIsOguloLink()
	{
		$this->assertTrue(call_user_func( LinksTypes::class.'::isOguloLink', 'Ogulo-Link'));
		$this->assertTrue(call_user_func( LinksTypes::class.'::isObjectLink', 'Objekt-Link'));
		$this->assertTrue(call_user_func( LinksTypes::class.'::isLink', 'Link'));
	}

}
