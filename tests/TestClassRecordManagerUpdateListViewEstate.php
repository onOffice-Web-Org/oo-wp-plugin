<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

use onOffice\WPlugin\Record\RecordManagerUpdateListViewEstate;
use WP_UnitTestCase;
use wpdb;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassRecordManagerUpdateListViewEstate
	extends WP_UnitTestCase
{
	/** @var RecordManagerUpdateListViewEstate */
	private $_pSubject = null;

	/** @var \wpdb */
	private $_pWPDB = null;

	/**
	 *
	 * @before
	 *
	 */
	public function prepare()
	{
		$this->_pWPDB = $this->getMockBuilder(wpdb::class)
			->setConstructorArgs(['testUser', 'testPassword', 'testDB', 'testHost'])
			->getMock();
		$this->_pWPDB->prefix = 'testPrefix_';
		$this->_pSubject = new RecordManagerUpdateListViewEstate(30);
	}

	/**
	 *
	 */
	public function testInsertAdditionalValues()
	{
		$recordData = [
			'name' => 'estate list',
			'filterId' => '7',
			'list_type' => 'default',
			'template' => 'oo-wp-plugin/templates.dist/estate/default.php'
		];

		$this->assertEquals(true, $this->_pSubject->updateEstateListViewItem($recordData));
	}
}
