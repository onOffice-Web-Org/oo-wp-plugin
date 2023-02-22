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

use onOffice\WPlugin\Controller\MainPage;
use WP_UnitTestCase;

/**
 *
 */

class TestClassMainPage
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testRender()
	{
		$pMainPage = new MainPage();
		$this->assertStringContainsString('><div class="card"><h2>Connect your website to onOffice enterprise</h2>', $pMainPage->render());
		$this->assertStringContainsString('<p>You are ready to integrate all your real estate into your website and create forms that send data into onOffice enterprise.</p>', $pMainPage->render());
		$this->assertStringContainsString('<p>For help with setting up the plugin, read through our <a href="https://wp-plugin.onoffice.com/en/first-steps/">setup tutorial</a>.</p>', $pMainPage->render());
		$this->assertStringContainsString('<p>The <a href="https://wp-plugin.onoffice.com/en/">documentation website</a> also offers detailed explanations of the features. If you encounter a problem, you can send us a message using the <a href="https://wp-plugin.onoffice.com/en/support/">support form</a>.</p>', $pMainPage->render());
	}
}
