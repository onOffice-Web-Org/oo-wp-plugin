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

use onOffice\WPlugin\Types\GeoCoordinates;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassGeoCoordinates
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testDefault()
	{
		$pGeoCoordinates = new GeoCoordinates(42.42, 13.37);
		$this->assertEquals(42.42, $pGeoCoordinates->getLatitude());
		$this->assertEquals(13.37, $pGeoCoordinates->getLongitude());
		$this->assertTrue($pGeoCoordinates->isValid());
	}


	/**
	 *
	 */

	public function testLongitude()
	{
		$pGeoCoordinates = new GeoCoordinates(42.42);
		$this->assertFalse($pGeoCoordinates->isValid());
	}


	/**
	 *
	 */

	public function testLatitude()
	{
		$pGeoCoordinates = new GeoCoordinates(null, 13.37);
		$this->assertFalse($pGeoCoordinates->isValid());
	}


	/**
	 *
	 */

	public function testRangeLatitude()
	{
		$pGeoCoordinatesInvalidNegative = new GeoCoordinates(-90.3, 13.37);
		$this->assertFalse($pGeoCoordinatesInvalidNegative->isValid());
		$pGeoCoordinatesValidNegative = new GeoCoordinates(-89.9, 13.37);
		$this->assertTrue($pGeoCoordinatesValidNegative->isValid());

		$pGeoCoordinatesInvalidPositive = new GeoCoordinates(91.5, 13.37);
		$this->assertFalse($pGeoCoordinatesInvalidPositive->isValid());
		$pGeoCoordinatesValidPositive = new GeoCoordinates(35.6851775,139.7526986);
		$this->assertTrue($pGeoCoordinatesValidPositive->isValid());
	}


	/**
	 *
	 */

	public function testRangeLongitude()
	{
		$pGeoCoordinatesInvalidNegative = new GeoCoordinates(42.42, -180.4);
		$this->assertFalse($pGeoCoordinatesInvalidNegative->isValid());
		$pGeoCoordinatesValidNegative = new GeoCoordinates(35.0427398, -85.2821235);
		$this->assertTrue($pGeoCoordinatesValidNegative->isValid());

		$pGeoCoordinatesInvalidPositive = new GeoCoordinates(42.42, 185.4);
		$this->assertFalse($pGeoCoordinatesInvalidPositive->isValid());
		$pGeoCoordinatesValidPositive = new GeoCoordinates(35.0427398, 85.2821235);
		$this->assertTrue($pGeoCoordinatesValidPositive->isValid());
	}


	/**
	 *
	 * Asserts that coordinate 0,0 is valid
	 *
	 */

	public function testPointZeroZero()
	{
		$pGeoCoordinatesInvalidNegative = new GeoCoordinates(0, 0);
		$this->assertTrue($pGeoCoordinatesInvalidNegative->isValid());
	}


	public function testExceptionUnsetLongitudeGetLatitude()
	{
		$this->expectException(\onOffice\WPlugin\Types\InvalidGeoCoordinatesException::class);
		$pGeoCoordinates = new GeoCoordinates(42.42);
		$pGeoCoordinates->getLatitude();
	}


	public function testExceptionUnsetLongitudeGetLongitude()
	{
		$this->expectException(\onOffice\WPlugin\Types\InvalidGeoCoordinatesException::class);
		$pGeoCoordinates = new GeoCoordinates(42.42);
		$pGeoCoordinates->getLongitude();
	}


	public function testExceptionUnsetLatitudeGetLatitude()
	{
		$this->expectException(\onOffice\WPlugin\Types\InvalidGeoCoordinatesException::class);
		$pGeoCoordinates = new GeoCoordinates(null, 13.37);
		$pGeoCoordinates->getLatitude();
	}


	public function testExceptionUnsetLatitudeGetLongitude()
	{
		$this->expectException(\onOffice\WPlugin\Types\InvalidGeoCoordinatesException::class);
		$pGeoCoordinates = new GeoCoordinates(null, 13.37);
		$pGeoCoordinates->getLongitude();
	}
}
