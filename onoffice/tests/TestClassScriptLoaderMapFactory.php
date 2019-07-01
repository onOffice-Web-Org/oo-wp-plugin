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

use DI\Container;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderMapFactory;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderMapGoogleMaps;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderMapOsm;
use onOffice\WPlugin\Types\MapProvider;
use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use onOffice\WPlugin\WP\WPScriptStyleBase;
use onOffice\WPlugin\WP\WPScriptStyleTest;
use WP_UnitTestCase;

/**
 *
 */

class TestClassScriptLoaderMapFactory
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testBuildForMapProvider()
	{
		$pContainer = new Container();
		$pContainer->set(WPScriptStyleBase::class, new WPScriptStyleTest());
		$pContainer->set(WPOptionWrapperBase::class, new WPOptionWrapperTest());
		$pWPOptionsWrapper = new WPOptionWrapperTest();
		$pMapProvider = new MapProvider($pWPOptionsWrapper);
		$pScriptLoaderMapFactory = new ScriptLoaderMapFactory($pContainer);
		$this->assertInstanceOf(ScriptLoaderMapOsm::class,
			$pScriptLoaderMapFactory->buildForMapProvider($pMapProvider));
		$pWPOptionsWrapper->addOption('onoffice-maps-mapprovider', MapProvider::GOOGLE_MAPS);
		$this->assertInstanceOf(ScriptLoaderMapGoogleMaps::class,
			$pScriptLoaderMapFactory->buildForMapProvider($pMapProvider));
		$pWPOptionsWrapper->updateOption('onoffice-maps-mapprovider', MapProvider::OPEN_STREET_MAPS);
		$this->assertInstanceOf(ScriptLoaderMapOsm::class,
			$pScriptLoaderMapFactory->buildForMapProvider($pMapProvider));
	}
}
