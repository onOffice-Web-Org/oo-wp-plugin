<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

use onOffice\WPlugin\Gui\AdminPageEstate;
use onOffice\WPlugin\Gui\Table\WP\ListTable;
use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use WP_UnitTestCase;

class TestClassAdminPageEstateBulkActionsFireConfigChangedHook
	extends WP_UnitTestCase
{
	/** @var array */
	private $_capturedCalls = [];


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		global $wpdb;
		$pDbChanges = new DatabaseChanges(new WPOptionWrapperTest(), $wpdb);
		$pDbChanges->install();
		$this->_capturedCalls = [];

		add_action('onoffice/config_changed', function ($type, $action, $recordId) {
			$this->_capturedCalls[] = [$type, $action, $recordId];
		}, 10, 3);

		$userId = self::factory()->user->create(['role' => 'administrator']);
		wp_set_current_user($userId);
	}


	/**
	 *
	 * @after
	 *
	 */

	public function cleanup()
	{
		remove_all_actions('onoffice/config_changed');
	}
	private function insertEstateListViewFixture(string $name): int
	{
		global $wpdb;
		$wpdb->insert($wpdb->prefix.'oo_plugin_listviews', [
			'name' => $name,
			'sortby' => 'objekttitel',
			'sortorder' => 'ASC',
			'list_type' => 'default',
			'template' => 'default.php',
			'recordsPerPage' => 20,
			'sortByUserDefinedDefault' => '',
			'page_shortcode' => '',
			'sortByTags' => '',
		]);

		return (int) $wpdb->insert_id;
	}


	private function createListTableMock(string $currentAction, string $plural)
	{
		$pTable = $this->getMockBuilder(ListTable::class)
			->disableOriginalConstructor()
			->onlyMethods(['current_action', 'getArgs'])
			->getMock();
		$pTable->method('current_action')->willReturn($currentAction);
		$pTable->method('getArgs')->willReturn(['plural' => $plural]);

		return $pTable;
	}


	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */

	public function testConfigChangedHookFiresWhenEstateListViewIsDuplicated()
	{
		$listviewId = $this->insertEstateListViewFixture('oo-test-estate-duplicate-'.uniqid());

		$_GET['listVewId'] = (string) $listviewId;
		$_REQUEST['_wpnonce'] = wp_create_nonce('bulk-estatelists');

		$pTable = $this->createListTableMock('bulk_duplicate', 'estatelists');

		$pAdminPage = new AdminPageEstate('onoffice-estates');
		$pAdminPage->preOutput();

		apply_filters('handle_bulk_actions-onoffice_page_onoffice-estates', 'unused', $pTable, []);

		$this->assertCount(1, $this->_capturedCalls);
		$this->assertSame(RecordManagerFactory::TYPE_ESTATE, $this->_capturedCalls[0][0]);
		$this->assertSame('duplicate', $this->_capturedCalls[0][1]);
	}


	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */

	public function testConfigChangedHookDoesNotFireWhenDuplicatingUnknownListView()
	{
		$_GET['listVewId'] = '999999999';
		$_REQUEST['_wpnonce'] = wp_create_nonce('bulk-estatelists');

		$pTable = $this->createListTableMock('bulk_duplicate', 'estatelists');

		$pAdminPage = new AdminPageEstate('onoffice-estates');
		$pAdminPage->preOutput();

		apply_filters('handle_bulk_actions-onoffice_page_onoffice-estates', 'unused', $pTable, []);

		$this->assertCount(0, $this->_capturedCalls);
	}


	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */

	public function testConfigChangedHookFiresWhenEstateListViewIsDeleted()
	{
		$listviewId = $this->insertEstateListViewFixture('oo-test-estate-delete-'.uniqid());

		$_REQUEST['_wpnonce'] = wp_create_nonce('bulk-estatelists');

		$pTable = $this->createListTableMock('bulk_delete', 'estatelists');

		$pAdminPage = new AdminPageEstate('onoffice-estates');
		$pAdminPage->preOutput();

		apply_filters('handle_bulk_actions-onoffice_page_onoffice-estates', 'unused', $pTable, [$listviewId]);

		$this->assertCount(1, $this->_capturedCalls);
		$this->assertSame(RecordManagerFactory::TYPE_ESTATE, $this->_capturedCalls[0][0]);
		$this->assertSame(RecordManagerFactory::ACTION_DELETE, $this->_capturedCalls[0][1]);
	}


	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */

	public function testConfigChangedHookDoesNotFireWhenDeletingUnknownListView()
	{
		$_REQUEST['_wpnonce'] = wp_create_nonce('bulk-estatelists');

		$pTable = $this->createListTableMock('bulk_delete', 'estatelists');

		$pAdminPage = new AdminPageEstate('onoffice-estates');
		$pAdminPage->preOutput();

		apply_filters('handle_bulk_actions-onoffice_page_onoffice-estates', 'unused', $pTable, [999999999]);

		$this->assertCount(0, $this->_capturedCalls);
	}
}
