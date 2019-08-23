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

use onOffice\WPlugin\WP\WPScriptStyleTest;
use WP_UnitTestCase;

/**
 *
 */

class TestClassWPScriptStyleTest
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testDefaults()
	{
		$pWPScriptStyleTest = new WPScriptStyleTest();
		$this->assertEmpty($pWPScriptStyleTest->getRegisteredScripts());
		$this->assertEmpty($pWPScriptStyleTest->getRegisteredStyles());
		$this->assertEmpty($pWPScriptStyleTest->getEnqueuedScripts());
		$this->assertEmpty($pWPScriptStyleTest->getEnqueuedStyles());
		$this->assertEmpty($pWPScriptStyleTest->getLocalizedScripts());
	}


	/**
	 *
	 * @return WPScriptStyleTest
	 *
	 */

	public function testRegisteredScripts(): WPScriptStyleTest
	{
		$pWPScriptStyleTest = new WPScriptStyleTest();
		$pWPScriptStyleTest->registerScript('testhandle1', 'src/test.js', ['jquery'], 1.03, true);
		$pWPScriptStyleTest->registerScript('testhandle2', 'src/test1.js', ['twbs'], 2.1);
		$this->assertEquals([
			'testhandle1' => [
				'src' => 'src/test.js',
				'deps' => ['jquery'],
				'ver' => 1.03,
				'inFooter' => true,
			],
			'testhandle2' => [
				'src' => 'src/test1.js',
				'deps' => ['twbs'],
				'ver' => 2.1,
				'inFooter' => false,
			],
		], $pWPScriptStyleTest->getRegisteredScripts());
		return $pWPScriptStyleTest;
	}


	/**
	 *
	 * @return WPScriptStyleTest
	 *
	 */

	public function testRegisteredStyles(): WPScriptStyleTest
	{
		$pWPScriptStyleTest = new WPScriptStyleTest();
		$pWPScriptStyleTest->registerStyle('testhandlecss', 'src/test.css', ['twbs'], 1.01);
		$this->assertEquals([
			'testhandlecss' => [
				'src' => 'src/test.css',
				'deps' => ['twbs'],
				'ver' => 1.01,
				'media' => 'all',
			],
		], $pWPScriptStyleTest->getRegisteredStyles());

		return $pWPScriptStyleTest;
	}


	/**
	 *
	 * @depends testRegisteredScripts
	 * @expectedException \Exception
	 * @expectedExceptionMessage Script testhandle1 already registered
	 * @param WPScriptStyleTest $pWPScriptStyleTest
	 *
	 */

	public function testRegisterScriptDouble(WPScriptStyleTest $pWPScriptStyleTest)
	{
		$pWPScriptStyleTest->registerScript('testhandle1', '/test/src.js');
	}


	/**
	 *
	 * @depends testRegisteredStyles
	 * @expectedException \Exception
	 * @expectedExceptionMessage Style testhandlecss already registered
	 * @param WPScriptStyleTest $pWPScriptStyleTest
	 *
	 */

	public function testRegisterStyleDouble(WPScriptStyleTest $pWPScriptStyleTest)
	{
		$pWPScriptStyleTest->registerStyle('testhandlecss', '/test/src.css');
	}


	/**
	 *
	 * @depends testRegisteredScripts
	 * @param WPScriptStyleTest $pWPScriptStyleTest
	 *
	 */

	public function testEnqueueScripts(WPScriptStyleTest $pWPScriptStyleTest)
	{
		$pWPScriptStyleTest->enqueueScript('testhandle1');
		$this->assertEquals(['testhandle1'], $pWPScriptStyleTest->getEnqueuedScripts());
	}


	/**
	 *
	 * @depends testRegisteredStyles
	 * @param WPScriptStyleTest $pWPScriptStyleTest
	 *
	 */

	public function testEnqueueStyles(WPScriptStyleTest $pWPScriptStyleTest)
	{
		$pWPScriptStyleTest->enqueueStyle('testhandlecss');
		$this->assertEquals(['testhandlecss'], $pWPScriptStyleTest->getEnqueuedStyles());
	}


	/**
	 *
	 * @expectedException \Exception
	 * @expectedExceptionMessage Script testNonEnqueuedJs not registered
	 *
	 */

	public function testEnqueueNonRegisteredScript()
	{
		$pWPScriptStyleTest = new WPScriptStyleTest();
		$pWPScriptStyleTest->enqueueScript('testNonEnqueuedJs');
	}


	/**
	 *
	 * @expectedException \Exception
	 * @expectedExceptionMessage Style testNonEnqueuedCss not registered
	 *
	 */

	public function testEnqueueNonRegisteredStyle()
	{
		$pWPScriptStyleTest = new WPScriptStyleTest();
		$pWPScriptStyleTest->enqueueStyle('testNonEnqueuedCss');
	}


	/**
	 *
	 * @depends testRegisteredScripts
	 * @param WPScriptStyleTest $pWPScriptStyleTest
	 *
	 */

	public function testLocalizeScript(WPScriptStyleTest $pWPScriptStyleTest)
	{
		$pWPScriptStyleTest->localizeScript('testhandle1', 'global_js_var1', ['hello' => 'world']);
		$this->assertEquals([
			'testhandle1' => ['name' => 'global_js_var1', 'data' => ['hello' => 'world']],
		], $pWPScriptStyleTest->getLocalizedScripts());
	}


	/**
	 *
	 * @expectedException \Exception
	 * @expectedExceptionMessage Script nonRegistered not registered
	 *
	 */

	public function testLocalizeScriptNonRegistered()
	{
		$pWPScriptStyleTest = new WPScriptStyleTest();
		$pWPScriptStyleTest->localizeScript('nonRegistered', 'global_js_var1', ['hello' => 'world']);
	}
}