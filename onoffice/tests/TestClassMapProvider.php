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

use onOffice\WPlugin\Types\MapProvider;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassMapProvider
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetActiveMapProvider()
	{
		$pOptionWrapper = new onOffice\WPlugin\WP\WPOptionWrapperTest();

		$pMapProvider = new MapProvider($pOptionWrapper);
		$this->assertEquals(MapProvider::PROVIDER_DEFAULT, $pMapProvider->getActiveMapProvider());

		$pOptionWrapper->addOption('onoffice-maps-mapprovider', MapProvider::GOOGLE_MAPS);
		$this->assertEquals(MapProvider::GOOGLE_MAPS, $pMapProvider->getActiveMapProvider());

		$pOptionWrapper->updateOption('onoffice-maps-mapprovider', MapProvider::OPEN_STREET_MAPS);
		$this->assertEquals(MapProvider::OPEN_STREET_MAPS, $pMapProvider->getActiveMapProvider());
	}


	/**
	 *
	 */

	public function testConstants()
	{
		$this->assertEquals('osm', MapProvider::OPEN_STREET_MAPS);
		$this->assertEquals('google-maps', MapProvider::GOOGLE_MAPS);

		$this->assertEquals(MapProvider::OPEN_STREET_MAPS, MapProvider::PROVIDER_DEFAULT);
	}
}
