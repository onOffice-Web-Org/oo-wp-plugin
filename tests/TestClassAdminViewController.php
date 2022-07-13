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

use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\Controller\AdminViewController;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Gui\AdminPageAjax;
use onOffice\WPlugin\Gui\AdminPageEstate;
use onOffice\WPlugin\Gui\AdminPageEstateDetail;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Types\FieldsCollection;
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
	/** @var Container */
	private $_pContainer;
	/**
	 * @before
	 */
	public function prepare()
	{
		$pWPScreen = WP_Screen::get('admin_page_onoffice');
		$pWPScreen->in_admin('site');
		set_current_screen($pWPScreen);

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
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
		$pAdminViewController->add_actions();
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
		$this->assertEquals(['update-duplicate-check-warning-option', 'admin-js', 'oo-copy-shortcode'], wp_scripts()->queue);
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
		$this->assertCount(1, wp_scripts()->queue);
	}

	public function testAdminPageAjax()
	{
		$adminPageEstateMock = $this->getMockBuilder(AdminPageEstate::class)
			->disableOriginalConstructor()
			->setMethods(['getSelectedAdminPage'])
			->getMock();
		$pageAjax = new AdminPageEstateDetail('page-detail');
		$adminPageEstateMock->method('getSelectedAdminPage')->willReturn($pageAjax);
		$this->_pContainer->set(AdminPageEstate::class, $adminPageEstateMock);
		$pAdminViewController = $this->_pContainer->get(AdminViewController::class, ['page-setting']);
		$this->assertNull($pAdminViewController->onInit());
	}

	public function testDisplayAPIError()
	{
		$fieldNamesMock = $this->getMockBuilder(Fieldnames::class)
			->disableOriginalConstructor()
			->setMethods(['loadLanguage'])
			->getMock();
		$fieldNamesMock->method('loadLanguage')->willReturn(true);

		$pAdminViewController = $this->getMockBuilder(AdminViewController::class)
			->disableOriginalConstructor()
			->setMethods(['getField'])
			->getMock();
		$pAdminViewController->method('getField')->willReturn($fieldNamesMock);
		$this->assertNull($pAdminViewController->displayAPIError());
	}

	public function testDisplayAPIErrorThrowException()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();

		$exception = $pContainer->get(APIClientCredentialsException::class);
		$fieldNamesMock = $this->getMockBuilder(Fieldnames::class)
			->disableOriginalConstructor()
			->setMethods(['loadLanguage'])
			->getMock();
		$fieldNamesMock->method('loadLanguage')->will($this->throwException($exception));

		$pAdminViewController = $this->getMockBuilder(AdminViewController::class)
			->disableOriginalConstructor()
			->setMethods(['getField'])
			->getMock();
		$pAdminViewController->method('getField')->willReturn($fieldNamesMock);
		$pAdminViewController->displayAPIError();
		$this->expectOutputString('<div class="notice notice-error"><p>It looks like you did not enter any valid API credentials. Please consider reviewing your <a href="admin.php?page=onoffice-settings">API token and secret</a>.</p></div>');
	}

	public function testDisplayDeactivateDuplicateCheckWarningWithoutOption()
	{
		$pAdminViewController = new AdminViewController();
		$this->assertNull($pAdminViewController->displayDeactivateDuplicateCheckWarning());
	}

	public function testDisplayDeactivateDuplicateCheckWarningWithOption()
	{
		add_option('onoffice-duplicate-check-warning', '1');
		$pAdminViewController = new AdminViewController();
		$pAdminViewController->displayDeactivateDuplicateCheckWarning();
		$this->expectOutputString('<div class="notice notice-error duplicate-check-notify is-dismissible"><p>'
			. 'We have deactivated the plugin&#039;s duplicate check for all of your forms, because the duplicate '
			. 'check can unintentionally overwrite address records. This function will be removed in the future. '
			. 'The option has been deactivated for these forms: Contact, Interest, Owner</p></div>');
	}

	public function testDisplayUsingEmptyDefaultEmailError()
	{
		add_option('onoffice-settings-default-email', false);
		$recordManagerReadForm = $this->getMockBuilder(RecordManagerReadForm::class)
			->getMock();
		$recordManagerReadForm->method('getCountDefaultRecipientRecord')->will($this->returnValue('1'));

		$pAdminViewController = $this->getMockBuilder(AdminViewController::class)
			->disableOriginalConstructor()
			->setMethods(['getRecordManagerReadForm'])
			->getMock();

		$pAdminViewController->method('getRecordManagerReadForm')
			->willReturn($recordManagerReadForm);
		$pAdminViewController->displayUsingEmptyDefaultEmailError();
		$this->expectOutputString('<div class="notice notice-error"><p>The onOffice plugin is missing a default email address. You have forms that use the default email and they will currently not send emails. Please add a default email address in the <a href="admin.php?page=onoffice-settings">plugin settings</a> to dismiss this warning.</p></div>');
	}

	public function testGetField()
	{
		$pAdminViewController = new AdminViewController();
		$this->assertInstanceOf(Fieldnames::class, $pAdminViewController->getField());
	}
}
