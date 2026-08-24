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
use onOffice\WPlugin\Record\RecordManagerFactory;
use WP_UnitTestCase;

/**
 * Complements TestClassFiresConfigChangedHook (which only tests the extracted
 * trait method in isolation): this class exercises the REAL bulk-action
 * closures registered by AdminPageEstate::preOutput() end to end, proving the
 * hook actually fires when an estate list view is really duplicated/deleted
 * through the actual admin code path - not just when the trait method is
 * called directly.
 *
 * AdminPageEstate::preOutput() builds its own internal DI container using the
 * real RecordManager* classes, which cannot be swapped for mocks from the
 * outside. This test therefore runs against real plugin DB tables (created
 * here via dbDelta(), mirroring the schema in Installer\DatabaseChanges).
 *
 * IMPORTANT: dbDelta()/CREATE TABLE triggers an implicit MySQL commit, which
 * would break WP_UnitTestCase's shared transaction/rollback mechanism for
 * every other test in the suite if run in the main test process. Every test
 * method here therefore uses @runInSeparateProcess, exactly like the existing
 * TestClassInstaller::testInstall() (which mutates similarly global,
 * persistent state).
 */
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
		$this->createTablesIfMissing();
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


	/**
	 * Schema copied verbatim from Installer\DatabaseChanges' create-table
	 * methods for the tables RecordManagerDuplicateListViewEstate/
	 * RecordManagerDeleteListViewEstate/RecordManagerReadListViewEstate touch.
	 * Deliberately NOT calling DatabaseChanges::install() itself, since that
	 * also runs a long chain of unrelated legacy migration/update routines
	 * with side effects unrelated to this test.
	 */
	private function createTablesIfMissing(): void
	{
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$prefix = $wpdb->prefix;
		$charsetCollate = $wpdb->get_charset_collate();

		dbDelta("CREATE TABLE {$prefix}oo_plugin_listviews (
			`listview_id` int(11) NOT NULL AUTO_INCREMENT,
			`name` varchar(191) NOT NULL,
			`filterId` int(11),
			`sortby` tinytext NOT NULL,
			`sortorder` enum('ASC','DESC') NOT NULL DEFAULT 'ASC',
			`show_status` tinyint(1) NOT NULL DEFAULT '1',
			`list_type` ENUM('default', 'reference', 'favorites', 'units') NOT NULL DEFAULT 'default',
			`template` tinytext NOT NULL,
			`expose` tinytext,
			`recordsPerPage` INT( 10 ) NOT NULL DEFAULT '10',
			`random` tinyint(1) NOT NULL DEFAULT '0',
			`country_active` tinyint(1) NOT NULL DEFAULT '1',
			`zip_active` tinyint(1) NOT NULL DEFAULT '1',
			`city_active` tinyint(1) NOT NULL DEFAULT '0',
			`street_active` tinyint(1) NOT NULL DEFAULT '1',
			`radius_active` tinyint(1) NOT NULL DEFAULT '1',
			`radius` INT( 10 ) NULL DEFAULT NULL,
			`geo_order` VARCHAR( 255 ) NOT NULL DEFAULT 'street,zip,city,country,radius',
			`sortBySetting` ENUM('0','1','2') NOT NULL DEFAULT '0',
			`sortByUserDefinedDefault` VARCHAR(200) NOT NULL,
			`sortByUserDefinedDirection` ENUM('0','1') NOT NULL DEFAULT '0',
			`show_reference_estate` tinyint(1) NOT NULL DEFAULT '0',
			`page_shortcode` tinytext NOT NULL,
			`show_map` tinyint(1) NOT NULL DEFAULT '1',
			`show_price_on_request` tinyint(1) NOT NULL DEFAULT '0',
			`markedPropertiesSort` VARCHAR( 255 ) NOT NULL DEFAULT '',
			`sortByTags` tinytext NOT NULL,
			`sortByTagsDirection` enum('ASC','DESC') NOT NULL DEFAULT 'ASC',
			PRIMARY KEY (`listview_id`),
			UNIQUE KEY `name` (`name`)
		) $charsetCollate;");

		dbDelta("CREATE TABLE {$prefix}oo_plugin_fieldconfig (
			`fieldconfig_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`listview_id` int(11) NOT NULL,
			`order` int(11) NOT NULL,
			`fieldname` tinytext NOT NULL,
			`filterable` tinyint(1) NOT NULL DEFAULT '0',
			`hidden` tinyint(1) NOT NULL DEFAULT '0',
			`availableOptions` tinyint(1) NOT NULL DEFAULT '0',
			`convertTextToSelectForCityField` tinyint(1) NOT NULL DEFAULT '0',
			`rangeFieldDisplayMode` varchar(20) DEFAULT 'range',
			PRIMARY KEY (`fieldconfig_id`)
		) $charsetCollate;");

		dbDelta("CREATE TABLE {$prefix}oo_plugin_picturetypes (
			`picturetype_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`listview_id` int(11) NOT NULL,
			`picturetype` tinytext NOT NULL,
			PRIMARY KEY (`picturetype_id`)
		) $charsetCollate;");

		dbDelta("CREATE TABLE {$prefix}oo_plugin_sortbyuservalues (
			`sortbyvalue_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`listview_id` int(11) NOT NULL,
			`sortbyuservalue` varchar(100) NOT NULL,
			PRIMARY KEY (`sortbyvalue_id`)
		) $charsetCollate;");

		dbDelta("CREATE TABLE {$prefix}oo_plugin_listview_contactperson (
			`contactperson_id` int(11) NOT NULL AUTO_INCREMENT,
			`listview_id` int(11) NOT NULL,
			`order` int(11) NOT NULL,
			`fieldname` tinytext NOT NULL,
			PRIMARY KEY (`contactperson_id`)
		) $charsetCollate;");
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
