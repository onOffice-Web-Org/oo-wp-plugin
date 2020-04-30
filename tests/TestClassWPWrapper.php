<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

use onOffice\WPlugin\WP\WPWrapper;
use WP;
use WP_UnitTestCase;

class TestClassWPWrapper
	extends WP_UnitTestCase
{
	/**
	 * @covers \onOffice\WPlugin\WP\WPWrapper::getWP
	 */
	public function testGetWP()
	{
		$pWPWrapper= new WPWrapper();
		global $wp;

		$this->assertInstanceOf(WP::class, $pWPWrapper->getWP());
		$this->assertSame($wp, $pWPWrapper->getWP());
	}

	/**
	 * @covers \onOffice\WPlugin\WP\WPWrapper::getRequest
	 */
	public function testGetRequest()
	{
		$pWPWrapper= new WPWrapper();
		global $wp;

		$this->assertSame($wp->request, $pWPWrapper->getRequest());
	}
}