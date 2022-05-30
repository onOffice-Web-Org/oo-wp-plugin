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

use onOffice\WPlugin\Record\RecordManagerDeleteListViewEstate;
use WP_UnitTestCase;
use wpdb;


/**
 *
 */

class TestClassRecordManagerDeleteListViewEstate
	extends WP_UnitTestCase
{
	/** @var wpdb */
	private $_pWPDB = null;

	/** @var RecordManagerDeleteListViewEstate */
	private $_pSubject = null;


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

		$this->_pSubject = new RecordManagerDeleteListViewEstate($this->_pWPDB);
	}


	/**
	 *
	 */

	public function testDeleteByIds()
	{
		$this->_pWPDB->expects( $this->exactly( 0 ) )->method( 'delete' )
		             ->withConsecutive( ['wp_test_oo_plugin_listviews'], [ 'listview_id' => 3 ] );
		$this->_pWPDB->expects( $this->once() )->method( 'delete' )
		             ->withConsecutive( ['wp_test_oo_plugin_fieldconfig'], [ 'listview_id' => 3 ] );
		$this->_pWPDB->expects( $this->exactly( 2 ) )->method( 'delete' )
		             ->withConsecutive( ['wp_test_oo_plugin_picturetypes'], [ 'listview_id' => 3 ] );
		$this->_pWPDB->expects( $this->exactly( 3 ) )->method( 'delete' )
		             ->withConsecutive( ['wp_test_oo_plugin_listview_contactperson'], [ 'listview_id' => 3 ] );
		$this->_pWPDB->expects( $this->exactly( 4 ) )->method( 'delete' )
		             ->withConsecutive( ['wp_test_oo_plugin_listviews'], [ 'listview_id' => 4 ] );
		$this->_pWPDB->expects( $this->exactly( 5 ) )->method( 'delete' )
		             ->withConsecutive( ['wp_test_oo_plugin_fieldconfig'], [ 'listview_id' => 4 ] );
		$this->_pWPDB->expects( $this->exactly( 6 ) )->method( 'delete' )
		             ->withConsecutive( ['wp_test_oo_plugin_picturetypes'], [ 'listview_id' => 4 ] );
		$this->_pWPDB->expects( $this->exactly( 7 ) )->method( 'delete' )
		             ->withConsecutive( ['wp_test_oo_plugin_listview_contactperson'], [ 'listview_id' => 4 ] );
		$this->_pSubject->deleteByIds( [ 3, 4 ] );
	}
}
