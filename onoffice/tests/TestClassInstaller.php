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

use onOffice\WPlugin\Installer;
use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers onOffice\WPlugin\Installer
 *
 * Please note $wpdb won't create any tables in testing mode
 *
 */

class TestClassInstaller
	extends WP_UnitTestCase
{
	/** amount of tables created */
	const NUM_NEW_TABLES = 7;

	/** @var string[] */
	private $_createQueries = [];

	/** @var string[] */
	private $_dropQueries = [];


	/**
	 *
	 * @return array
	 *
	 */

	public function testInstall(): array
	{
		add_filter('query', [$this, 'saveCreateQuery'], 1);
		Installer::install();
		remove_filter('query', [$this, 'saveCreateQuery'], 1);

		$this->assertGreaterThanOrEqual(self::NUM_NEW_TABLES, count($this->_createQueries));

		$dbversion = get_option('oo_plugin_db_version', null);
		$this->assertEquals(12, $dbversion);
		return $this->_createQueries;
	}


	/**
	 *
	 * @depends testInstall
	 * @param array $createQueries from testInstall test.
	 *
	 */

	public function testUninstall(array $createQueries)
	{
		add_filter('query', [$this, 'saveDropQuery'], 1);
		Installer::deinstall();
		remove_filter('query', [$this, 'saveDropQuery'], 1);

		$this->assertGreaterThanOrEqual(self::NUM_NEW_TABLES, count($this->_dropQueries));

		// assert that as many tables have been removed as have been created
		$uniqueCreateQueries = array_unique($createQueries);
		$uniqueDropQueries = array_unique($this->_dropQueries);

		$this->assertEquals(count($uniqueCreateQueries), count($uniqueDropQueries));

		$dbversion = get_option('oo_plugin_db_version', null);
		$this->assertNull($dbversion);

		$this->assertFalse(get_option('oo_plugin_db_version'));
		$this->assertFalse(get_option('onoffice-default-view'));
		$this->assertFalse(get_option('onoffice-favorization-enableFav'));
		$this->assertFalse(get_option('onoffice-favorization-favButtonLabelFav'));
		$this->assertFalse(get_option('onoffice-settings-apisecret'));
		$this->assertFalse(get_option('onoffice-settings-apikey'));
	}


	/**
	 *
	 * @param string $query
	 * @return string
	 *
	 */

	public function saveCreateQuery(string $query): string
	{
		if (__String::getNew($query)->startsWith('CREATE TABLE')) {
			$this->_createQueries []= $query;
		}

		return $query;
	}


	/**
	 *
	 * @param string $query
	 * @return string
	 *
	 */

	public function saveDropQuery(string $query): string
	{
		if (__String::getNew($query)->startsWith('DROP TABLE')) {
			$this->_dropQueries []= $query;
		}

		return $query;
	}
}
