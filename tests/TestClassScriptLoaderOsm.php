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
			'deps' => [],
			'ver' => false,
			'inFooter' => false,
		],
	];


	/** @var array */
	private $_stylesExpectation = [
		'leaflet-style' => [
			'deps' => [],
			'ver' => false,
			'media' => 'all',
		],
	];


	/** @var WPScriptStyleTest */
	private $_pWPScriptStyle = null;

	/** @var ScriptLoaderMapOsm */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pWPScriptStyle = new WPScriptStyleTest();
		$this->_pSubject = new ScriptLoaderMapOsm($this->_pWPScriptStyle);
	}


	/**
	 *
	 */

	public function testRegister()
	{
		$this->_pSubject->register();
		$registeredScripts = $this->_pWPScriptStyle->getRegisteredScripts();
		$registeredStyles = $this->_pWPScriptStyle->getRegisteredStyles();
		$this->assertArraySubset($this->_stylesExpectation, $registeredStyles);
		$this->assertArraySubset($this->_scriptsExpectation, $registeredScripts);
		$this->assertStringStartsWith('http://example.org/wp-content/plugins/', $registeredScripts['leaflet-script']['src']);
		$this->assertStringEndsWith(getcwd().'/third_party/leaflet/leaflet.js', $registeredScripts['leaflet-script']['src']);
		$this->assertStringStartsWith('http://example.org/wp-content/plugins/', $registeredStyles['leaflet-style']['src']);
		$this->assertStringEndsWith(getcwd().'/third_party/leaflet/leaflet.css', $registeredStyles['leaflet-style']['src']);
	}


	/**
	 *
	 */

	public function testEnqueue()
	{
		$this->_pSubject->register();
		$this->_pSubject->enqueue();
		$this->assertEquals(['leaflet-script'], $this->_pWPScriptStyle->getEnqueuedScripts());
		$this->assertEquals(['leaflet-style'], $this->_pWPScriptStyle->getEnqueuedStyles());
	}
}
