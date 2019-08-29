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

use onOffice\WPlugin\WP\WPScreenFactory;
use WP_Screen;
use WP_UnitTestCase;


/**
 *
 */

class TestClassWPScreenFactory
	extends WP_UnitTestCase
{
	/**
	 *
	 * @expectedException Exception
	 * @expectedExceptionMessage Current Screen is not available
	 *
	 */

	public function testGetCurrentScreenUnavailable()
	{
		$pWPScreenFactory = new WPScreenFactory();
		$pWPScreenFactory->getCurrentScreen();
	}


	/**
	 *
	 */

	public function testGetCurrentScreen()
	{
		set_current_screen(__CLASS__);
		$pWPScreenFactory = new WPScreenFactory();
		$this->assertInstanceOf(WP_Screen::class, $pWPScreenFactory->getCurrentScreen());
	}
}
