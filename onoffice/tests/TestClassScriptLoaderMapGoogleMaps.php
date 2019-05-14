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

use onOffice\WPlugin\ScriptLoader\ScriptLoaderMapGoogleMaps;
use onOffice\WPlugin\WP\WPScriptStyleTest;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassScriptLoaderMapGoogleMaps
	extends WP_UnitTestCase
{
	/** @var array */
	private $_scriptsExpectation = [
		'google-maps' => [
			'src' => 'https://maps.googleapis.com/maps/api/js?',
			'deps' => [],
			'ver' => false,
			'inFooter' => false,
		],
		'gmapsinit' => [
			'src' => 'http://example.org/wp-content/plugins/onoffice-test/js/gmapsinit.js',
			'deps' => ['google-maps'],
			'ver' => false,
			'inFooter' => false,
		],
	];


	/**
	 *
	 */

	public function testRegister()
	{
		$pWPScriptStyle = new WPScriptStyleTest();

		$pScriptLoader = new ScriptLoaderMapGoogleMaps('/onoffice-test/index.php');
		$pScriptLoader->register($pWPScriptStyle);
		$this->assertEquals($this->_scriptsExpectation, $pWPScriptStyle->getRegisteredScripts());
		$this->assertEquals([], $pWPScriptStyle->getRegisteredStyles());
	}


	/**
	 *
	 */

	public function testEnqueue()
	{
		$pWPScriptStyle = new WPScriptStyleTest();

		$pScriptLoader = new ScriptLoaderMapGoogleMaps('/onoffice-test/index.php');
		$pScriptLoader->register($pWPScriptStyle);
		$pScriptLoader->enqueue($pWPScriptStyle);
		$this->assertEquals(['gmapsinit'], $pWPScriptStyle->getEnqueuedScripts());
		$this->assertEquals([], $pWPScriptStyle->getEnqueuedStyles());
	}
}
