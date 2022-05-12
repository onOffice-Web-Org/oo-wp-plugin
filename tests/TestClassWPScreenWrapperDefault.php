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
use onOffice\WPlugin\WP\WPScreenWrapperDefault;
use WP_UnitTestCase;
use function get_current_screen;
use function set_current_screen;

/**
 *
 */

class TestClassWPScreenWrapperDefault
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetID()
	{
		set_current_screen('testscreen01337');
		$pWPScreenFactory = $this->getMockBuilder(WPScreenFactory::class)
			->getMock();
		$pWPScreenFactory->expects($this->once())->method('getCurrentScreen')
			->will($this->returnValue(get_current_screen()));
		$pWPScreenWrapperDefault = new WPScreenWrapperDefault($pWPScreenFactory);
		$this->assertSame('testscreen01337', $pWPScreenWrapperDefault->getID());
	}


	/**
	 *
	 */

	public function testGetOptionPerPage()
	{
		set_current_screen('testscreen_perpage');
		add_screen_option('per_page', array('option' => 'per_page_option_test'));
		$pWPScreenFactory = new WPScreenFactory();
		$optionPerPage = $pWPScreenFactory->getCurrentScreen()->get_option('per_page');
		$this->assertNotEmpty($optionPerPage);
		$this->assertSame('per_page_option_test', $optionPerPage['option']);
	}
}
