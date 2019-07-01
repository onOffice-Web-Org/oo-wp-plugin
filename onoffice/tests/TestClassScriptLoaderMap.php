<?php

/**
 *
 *    Copyright (C) 2018-2019 onOffice GmbH
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
use onOffice\WPlugin\ScriptLoader\ScriptLoaderMap;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderMapFactory;
use onOffice\WPlugin\Types\MapProvider;
use onOffice\WPlugin\WP\WPScriptStyleBase;
use onOffice\WPlugin\WP\WPScriptStyleTest;
use WP_UnitTestCase;


/**
 *
 */

class TestClassScriptLoaderMap
	extends WP_UnitTestCase
{
	/** @var MapProvider */
	private $_pMapProvider = null;

	/** @var ScriptLoaderMap */
	private $_pSubject = null;

	/** @var Container */
	private $_pContainer = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pMapProvider = $this->getMockBuilder(MapProvider::class)
			->setMethods(['getActiveMapProvider'])
			->getMock();
		$this->_pContainer = new Container;
		$this->_pContainer->set(WPScriptStyleBase::class, new WPScriptStyleTest());
		$pScriptLoaderMapFactory = new ScriptLoaderMapFactory($this->_pContainer);
		$this->_pSubject = new ScriptLoaderMap($this->_pMapProvider, $pScriptLoaderMapFactory);
	}


	/**
	 *
	 */

	public function testRegister()
	{
		$pWPScriptStyle = $this->_pContainer->get(WPScriptStyleBase::class);
		$this->assertEmpty($pWPScriptStyle->getRegisteredScripts());
		$this->assertEmpty($pWPScriptStyle->getRegisteredStyles());

		$this->_pMapProvider->expects($this->once())->method('getActiveMapProvider')
			->will($this->returnValue(MapProvider::OPEN_STREET_MAPS));
		$this->_pSubject->register();

		$this->assertGreaterThan(0, count($pWPScriptStyle->getRegisteredScripts()));
		$this->assertGreaterThan(0, count($pWPScriptStyle->getRegisteredStyles()));
	}


	/**
	 *
	 */

	public function testEnqueue()
	{
		$pWPScriptStyle = $this->_pContainer->get(WPScriptStyleBase::class);
		$this->_pSubject->register();
		$this->_pSubject->enqueue();
		$this->assertSame(['leaflet-script'], $pWPScriptStyle->getEnqueuedScripts());
		$this->assertSame(['leaflet-style'], $pWPScriptStyle->getEnqueuedStyles());
	}
}
