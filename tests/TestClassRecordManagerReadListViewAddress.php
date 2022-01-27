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

namespace onOffice\tests;

use Closure;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassRecordManagerReadListViewAddress
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testConstruct()
	{
		$pRecordManager = new RecordManagerReadListViewAddress();
		$pMainTable = $pRecordManager->getMainTable();
		$pIdColumnMain = $pRecordManager->getIdColumnMain();

		$this->assertEquals('oo_plugin_listviews_address', $pMainTable);
		$this->assertEquals('listview_address_id', $pIdColumnMain);
	}
}
