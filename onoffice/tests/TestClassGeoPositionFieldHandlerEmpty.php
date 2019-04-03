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

use onOffice\WPlugin\Controller\GeoPositionFieldHandlerEmpty;
use WP_UnitTestCase;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassGeoPositionFieldHandlerEmpty
	extends WP_UnitTestCase
{
	/** @var GeoPositionFieldHandlerEmpty */
	private $_pGeoPositionFieldHandlerEmpty = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pGeoPositionFieldHandlerEmpty = new GeoPositionFieldHandlerEmpty();
		$this->_pGeoPositionFieldHandlerEmpty->readValues();
	}


	/**
	 *
	 */

	public function testGetActiveFields()
	{
		$this->assertEquals([], $this->_pGeoPositionFieldHandlerEmpty->getActiveFields());
	}


	/**
	 *
	 */

	public function testGetActiveFieldsWithValue()
	{
		$this->assertEquals([], $this->_pGeoPositionFieldHandlerEmpty->getActiveFieldsWithValue());
	}
}
