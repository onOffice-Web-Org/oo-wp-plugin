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

use onOffice\WPlugin\ScriptLoader\ScriptLoaderMapGoogleMaps;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use onOffice\WPlugin\WP\WPScriptStyleTest;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassScriptLoaderMapGoogleMaps
	extends WP_UnitTestCase
{
	/** @var ScriptLoaderMapGoogleMaps */
	private $_pSubject = null;

	/** @var WPScriptStyleTest */
	private $_pWPScriptStyle = null;

	/** @var WPOptionWrapperTest */
	private $_pWPOptionWrapper = null;

	/** @var array */
	private $_scriptsExpectation = [
		'google-maps' => [
			'deps' => [],
			'ver' => false,
			'inFooter' => false,
		],
		'gmapsinit' => [
			'deps' => ['google-maps'],
			'ver' => false,
			'inFooter' => false,
		],
	];


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pWPScriptStyle = new WPScriptStyleTest();
		$this->_pWPOptionWrapper = new WPOptionWrapperTest();
		$this->_pSubject = new ScriptLoaderMapGoogleMaps($this->_pWPScriptStyle, $this->_pWPOptionWrapper);
	}


	/**
	 *
	 */

	public function testRegister()
	{
		$this->_pWPOptionWrapper->addOption('onoffice-settings-googlemaps-key', 'abcdef123');
		$this->_pSubject->register();
		$registeredScripts = $this->_pWPScriptStyle->getRegisteredScripts();
		$this->assertArraySubset($this->_scriptsExpectation, $registeredScripts);
		$this->assertEmpty($this->_pWPScriptStyle->getRegisteredStyles());

		$this->assertStringStartsWith('http://example.org/wp-content/plugins/', $registeredScripts['gmapsinit']['src']);
		$this->assertStringEndsWith('/onoffice/js/gmapsinit.js', $registeredScripts['gmapsinit']['src']);

		$this->assertEquals('https://maps.googleapis.com/maps/api/js?key=abcdef123', $registeredScripts['google-maps']['src']);
	}


	/**
	 *
	 */

	public function testEnqueue()
	{
		$this->_pSubject->register();
		$this->_pSubject->enqueue();
		$this->assertEquals(['gmapsinit'], $this->_pWPScriptStyle->getEnqueuedScripts());
		$this->assertEquals([], $this->_pWPScriptStyle->getEnqueuedStyles());
	}
}