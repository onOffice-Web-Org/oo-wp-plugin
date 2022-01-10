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

use Exception;
use onOffice\WPlugin\Controller\AdminViewController;
use onOffice\WPlugin\Gui\AdminPageEstateDetail;
use WP_Screen;
use WP_UnitTestCase;
use function is_admin;
use function set_current_screen;
use function wp_scripts;


/**
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 *
 */

class TestClassAdminViewController
	extends WP_UnitTestCase
{
	/**
	 * @before
	 */
	public function prepare()
	{
		$pWPScreen = WP_Screen::get('admin_page_onoffice');
		$pWPScreen->in_admin('site');
		set_current_screen($pWPScreen);
	}

	/**
	 * @return AdminViewController
	 */
	public function testOnInit(): AdminViewController
	{
		$this->assertTrue(is_admin());
		$pAdminViewController = new AdminViewController();
		$this->assertNull($pAdminViewController->onInit());
		return $pAdminViewController;
	}

	/**
	 * @depends testOnInit
	 * @param AdminViewController $pAdminViewController
	 */
	public function testEnqueueAjax(AdminViewController $pAdminViewController)
	{
		wp_scripts()->registered = [];
		$pAdminViewController->enqueue_ajax('admin_page_onoffice-editlistviewaddress');
		$pAdminViewController->enqueue_ajax('admin_page_onoffice-editlistview');
		$pAdminViewController->enqueue_ajax('admin_page_onoffice-editunitlist');
		$this->assertCount(3, wp_scripts()->registered);
	}

	/**
	 * @depends testOnInit
	 * @global array $wp_actions
	 * @param AdminViewController $pAdminViewController
	 */
	public function testRegisterMenu(AdminViewController $pAdminViewController)
	{
		global $wp_filter;
		$wp_filter = [];
		$pAdminViewController->register_menu();
		$this->assertCount(5, $wp_filter);
	}

	/**
	 * @depends testOnInit
	 * @param AdminViewController $pAdminViewController
	 * @throws Exception
	 * @global array $wp_filter
	 */
	public function testAddAjaxHooks(AdminViewController $pAdminViewController)
	{
		global $wp_filter;
		$wp_filter = [];
		$pAdminViewController->add_ajax_actions();
		$this->assertCount(4, $wp_filter);
	}

	/**
	 * @depends testOnInit
	 * @param AdminViewController $pAdminViewController
	 */
	public function testPluginSettingsLink(AdminViewController $pAdminViewController)
	{
		$links = ['<a href="https://example.org/test">test</a>'];
		$result = $pAdminViewController->pluginSettingsLink($links);
		$expectedResult = [
			'<a href="http://example.org/wp-admin/admin.php?page=onoffice">Settings</a>',
			'<a href="https://example.org/test">test</a>',
		];
		$this->assertEquals($expectedResult, $result);
	}

	/**
	 * @depends testOnInit
	 * @param AdminViewController $pAdminViewController
	 * @throws Exception
	 * @global array $wp_filter
	 */
	public function testDisableHideMetaboxes(AdminViewController $pAdminViewController)
	{
		global $wp_filter;
		$wp_filter = [];
		$pAdminViewController->disableHideMetaboxes();
		$this->assertCount(1, $wp_filter);
	}

	/**
	 * @depends testOnInit
	 * @param AdminViewController $pAdminViewController
	 * @throws Exception
	 */
	public function testEnqueueCss(AdminViewController $pAdminViewController)
	{
		$pAdminViewController->enqueue_css();
		$this->assertCount(2, wp_styles()->queue);
	}

	/**
	 * @depends testOnInit
	 * @param AdminViewController $pAdminViewController
	 * @throws Exception
	 * @global array $wp_filter
	 */
	public function testEnqueueExtraJs(AdminViewController $pAdminViewController)
	{
		global $wp_filter;
		$wp_filter['admin_page_onoffice-editlistview'] = new \WP_Hook;

		/* @var $pWpHook WP_Hook */
		$pWpHook = $wp_filter['admin_page_onoffice-editlistview'];
		$adminPage = new AdminPageEstateDetail('admin_page_onoffice-editlistview');
		$pWpHook->callbacks = [[['function' => [$adminPage]]]];
		$pAdminViewController->enqueueExtraJs("admin_page_onoffice-editlistview");
		$this->assertEquals(['admin-js'], wp_scripts()->queue);
	}

	/**
	 * @depends testOnInit
	 * @param AdminViewController $pAdminViewController
	 * @throws Exception
	 * @global array $wp_filter
	 */
	public function testEnqueueExtraJsWithNullHook(AdminViewController $pAdminViewController)
	{
		global $wp_filter;
		$wp_filter['admin_page_onoffice-editlistview'] = new \WP_Hook;

		/* @var $pWpHook WP_Hook */
		$pWpHook = $wp_filter['admin_page_onoffice-editlistview'];
		$pWpHook->callbacks = [[['function' => ['a']]]];
		$pAdminViewController->enqueueExtraJs("admin_onoffice_test");
		$this->assertCount(0, wp_scripts()->queue);
	}
}
