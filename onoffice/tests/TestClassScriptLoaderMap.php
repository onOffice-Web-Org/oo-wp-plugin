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

use onOffice\WPlugin\ScriptLoader\ScriptLoaderMap;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderMapEnvironmentTest;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderMapGoogleMaps;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderMapOsm;
use onOffice\WPlugin\Types\MapProvider;
use onOffice\WPlugin\WP\WPScriptStyleTest;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassScriptLoaderMap
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testConstructorOsm()
	{
		$pScriptloaderMapEnvironment = new ScriptLoaderMapEnvironmentTest();
		$pScriptloaderMapEnvironment->setMapProvider(MapProvider::OPEN_STREET_MAPS);
		$pScriptLoader = new ScriptLoaderMap($pScriptloaderMapEnvironment);
		$this->assertInstanceOf(ScriptLoaderMapOsm::class, $pScriptLoader->getSpecificMapLoader());
	}


	/**
	 *
	 */

	public function testConstructorGoogleMaps()
	{
		$pScriptloaderMapEnvironment = new ScriptLoaderMapEnvironmentTest();
		$pScriptloaderMapEnvironment->setMapProvider(MapProvider::GOOGLE_MAPS);
		$pScriptLoader = new ScriptLoaderMap($pScriptloaderMapEnvironment);
		$this->assertInstanceOf(ScriptLoaderMapGoogleMaps::class,
			$pScriptLoader->getSpecificMapLoader());
	}


	/**
	 *
	 */

	public function testRegister()
	{
		$pWPScriptStyle = new WPScriptStyleTest();

		$pScriptloaderMapEnvironment = new ScriptLoaderMapEnvironmentTest();
		$pScriptloaderMapEnvironment->setMapProvider(MapProvider::OPEN_STREET_MAPS);
		$pScriptLoader = new ScriptLoaderMap($pScriptloaderMapEnvironment);
		$pScriptLoader->register($pWPScriptStyle);

		$this->assertGreaterThan(0, count($pWPScriptStyle->getRegisteredScripts()));
		$this->assertGreaterThan(0, count($pWPScriptStyle->getRegisteredStyles()));
	}


	/**
	 *
	 */

	public function testEnqueue()
	{
		$pWPScriptStyle = new WPScriptStyleTest();

		$pScriptloaderMapEnvironment = new ScriptLoaderMapEnvironmentTest();
		$pScriptloaderMapEnvironment->setMapProvider(MapProvider::OPEN_STREET_MAPS);
		$pScriptLoader = new ScriptLoaderMap($pScriptloaderMapEnvironment);
		$pScriptLoader->register($pWPScriptStyle);
		$pScriptLoader->enqueue($pWPScriptStyle);
	}
}
