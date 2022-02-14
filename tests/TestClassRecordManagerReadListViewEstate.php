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
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassRecordManagerReadListViewEstate
	extends WP_UnitTestCase
{
	private $_pRecordManagerReadListViewEstate = null;

	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pWPDBMock = $this->getMockBuilder(\wpdb::class)
			->disableOriginalConstructor()
			->setMethods(['get_results'])
			->getMock();

		$this->_pRecordManagerReadListViewEstate = $this->getMockBuilder(RecordManagerReadListViewEstate::class)
			->getMock();
	}
	/**
	 *
	 */

	public function testConstruct()
	{
		$pRecordManager = new RecordManagerReadListViewEstate();
		$pClosureReadValues = Closure::bind(function() {
			return [
				$this->getMainTable(),
				$this->getIdColumnMain(),
			];
		}, $pRecordManager, RecordManagerReadListViewEstate::class);

		$this->assertEquals(['oo_plugin_listviews', 'listview_id'], $pClosureReadValues());
	}

	public function testGetRecords()
	{
		$pFieldsForm = $this->_pRecordManagerReadListViewEstate->getRecords();
		$this->assertEquals(null,$pFieldsForm);
	}

	public function testGetRecordsSortedAlphabetically()
	{
		$pFieldsFormSortAlphabe = $this->_pRecordManagerReadListViewEstate->getRecordsSortedAlphabetically();
		$this->assertEquals([],$pFieldsFormSortAlphabe);
	}
}
