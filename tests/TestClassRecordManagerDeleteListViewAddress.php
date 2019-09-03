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

use onOffice\WPlugin\Record\RecordManagerDeleteListViewAddress;
use WP_UnitTestCase;
use wpdb;


/**
 *
 */

class TestClassRecordManagerDeleteListViewAddress
	extends WP_UnitTestCase
{
	/** @var RecordManagerDeleteListViewAddress */
	private $_pSubject = null;

	/** @var wpdb */
	private $_pWPDB = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pWPDB = $this->getMockBuilder(wpdb::class)
			->setMethods(['delete'])
			->disableOriginalConstructor()
			->getMock();
		$this->_pWPDB->prefix = 'wp_test_';
		$this->_pSubject = new RecordManagerDeleteListViewAddress($this->_pWPDB);
	}


	/**
	 *
	 */

	public function testDeleteByIds()
	{
		$this->_pWPDB->expects($this->at(0))->method('delete')
			->with('wp_test_oo_plugin_listviews_address', ['listview_address_id' => 3]);
		$this->_pWPDB->expects($this->at(1))->method('delete')
			->with('wp_test_oo_plugin_address_fieldconfig', ['listview_address_id' => 3]);
		$this->_pWPDB->expects($this->at(2))->method('delete')
			->with('wp_test_oo_plugin_listviews_address', ['listview_address_id' => 4]);
		$this->_pWPDB->expects($this->at(3))->method('delete')
			->with('wp_test_oo_plugin_address_fieldconfig', ['listview_address_id' => 4]);
		$this->_pSubject->deleteByIds([3, 4]);
	}
}
