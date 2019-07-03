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

namespace onOffice\tests;

use onOffice\WPlugin\GeoPosition;
use WP_UnitTestCase;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassGeoPosition
	extends WP_UnitTestCase
{
	/** @var GeoPosition */
	private $_pGeoPosition = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pGeoPosition = new GeoPosition();
	}


	/**
	 *
	 * @covers onOffice\WPlugin\GeoPosition::getEstateSearchFields
	 *
	 */

	public function testGetEstateSearchFields()
	{
		$result = $this->_pGeoPosition->getEstateSearchFields();
		$expectedValue = [
			'country',
			'zip',
			'city',
			'street',
			'radius',
		];

		foreach ($expectedValue as $key => $value) {
			$this->assertEquals($value, $result[$key]);
		}
	}
}