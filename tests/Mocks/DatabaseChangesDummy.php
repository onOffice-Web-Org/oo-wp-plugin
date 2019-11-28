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

declare(strict_types=1);

namespace onOffice\tests\Mocks;

use onOffice\WPlugin\Installer\DatabaseChangesInterface;
use wpdb;

class DatabaseChangesDummy
	implements DatabaseChangesInterface
{
	private $_dbVersion = 0;

	/**
	 *
	 */
	public function install()
	{
	}

	/**
	 * @return mixed
	 */
	public function getDbVersion()
	{
		return $this->_dbVersion;
	}

	/**
	 *
	 * Callback for plugin uninstall hook
	 *
	 * @global wpdb $wpdb
	 *
	 */
	public function deinstall()
	{
	}

	/**
	 * @param int $dbVersion
	 */
	public function setDbVersion(int $dbVersion)
	{
		$this->_dbVersion = $dbVersion;
	}
}