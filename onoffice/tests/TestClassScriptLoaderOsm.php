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

declare (strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\ScriptLoader\ScriptLoaderMapOsm;
use onOffice\WPlugin\WP\WPScriptStyleTest;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassScriptLoaderOsm
	extends WP_UnitTestCase
{
	/** @var array */
	private $_scriptsExpectation = [
		'leaflet-script' => [
			'src' => 'http://example.org/wp-content/plugins/onoffice-test/third_party/leaflet/leaflet.js',
			'deps' => [],
			'ver' => false,
			'inFooter' => false,
		],
	];

	/** @var array */
	private $_stylesExpectation = [
		'leaflet-style' => [
			'src' => 'http://example.org/wp-content/plugins/onoffice-test/third_party/leaflet/leaflet.css',
			'deps' => [],
			'ver' => false,
			'media' => 'all',
		],
	];


	/**
	 *
	 */

	public function testRegister()
	{
		$pWPScriptStyle = new WPScriptStyleTest();

		$pScriptLoader = new ScriptLoaderMapOsm('/onoffice-test/index.php');
		$pScriptLoader->register($pWPScriptStyle);
		$this->assertEquals($this->_scriptsExpectation, $pWPScriptStyle->getRegisteredScripts());
		$this->assertEquals($this->_stylesExpectation, $pWPScriptStyle->getRegisteredStyles());
	}


	/**
	 *
	 */

	public function testEnqueue()
	{
		$pWPScriptStyle = new WPScriptStyleTest();

		$pScriptLoader = new ScriptLoaderMapOsm('/onoffice-test/index.php');
		$pScriptLoader->register($pWPScriptStyle);
		$pScriptLoader->enqueue($pWPScriptStyle);
		$this->assertEquals(['leaflet-script'], $pWPScriptStyle->getEnqueuedScripts());
		$this->assertEquals(['leaflet-style'], $pWPScriptStyle->getEnqueuedStyles());
	}
}
