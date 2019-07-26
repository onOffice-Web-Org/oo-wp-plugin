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

use onOffice\WPlugin\WP\WPScriptStyleDefault;
use WP_UnitTestCase;
use function wp_scripts;

/**
 *
 */

class TestClassWPScriptStyleDefault
	extends WP_UnitTestCase
{
	/** @var WPScriptStyleDefault */
	private $_pWPScriptStyleDefault = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pWPScriptStyleDefault = new WPScriptStyleDefault();
	}


	/**
	 *
	 * @covers onOffice\WPlugin\WP\WPScriptStyleDefault::enqueueScript
	 *
	 */

	public function testEnqueueScript()
	{
		wp_scripts()->queue = [];
		$this->_pWPScriptStyleDefault->enqueueScript(__CLASS__.'1.js', 'test/src/js/'.__CLASS__.'2', ['jquery'], 1, true);
		$this->assertSame([__CLASS__.'1.js'], wp_scripts()->queue);
	}


	/**
	 *
	 * @covers onOffice\WPlugin\WP\WPScriptStyleDefault::enqueueStyle
	 *
	 */

	public function testEnqueueStyle()
	{
		wp_styles()->queue = [];
		$this->_pWPScriptStyleDefault->enqueueStyle(__CLASS__.'1.css', 'test/src/css/'.__CLASS__.'2', ['bootstrap'], 1, 'screen');
		$this->assertSame([__CLASS__.'1.css'], wp_styles()->queue);
	}


	/**
	 *
	 * @covers onOffice\WPlugin\WP\WPScriptStyleDefault::registerScript
	 *
	 */

	public function testRegisterScript()
	{
		wp_scripts()->registered = [];
		$return = $this->_pWPScriptStyleDefault->registerScript(__CLASS__.'1.js', 'test/src/js/'.__CLASS__.'1', ['jquery'], 1, true);
		$this->assertTrue($return);
		$this->assertSame([__CLASS__.'1.js'], array_keys(wp_scripts()->registered));
	}


	/**
	 *
	 * @covers onOffice\WPlugin\WP\WPScriptStyleDefault::registerStyle
	 *
	 */

	public function testRegisterStyle()
	{
		wp_styles()->registered = [];
		$return = $this->_pWPScriptStyleDefault->registerStyle(__CLASS__.'1.css', 'test/src/css/'.__CLASS__.'1', ['bootstrap'], 1, 'screen');
		$this->assertTrue($return);
		$this->assertSame([__CLASS__.'1.css'], array_keys(wp_styles()->registered));
	}


	/**
	 *
	 * @covers onOffice\WPlugin\WP\WPScriptStyleDefault::localizeScript
	 * @depends testRegisterScript
	 *
	 */

	public function testLocalizeScript()
	{
		$return = $this->_pWPScriptStyleDefault->localizeScript(__CLASS__.'1.js', 'testName', ['a' => 'b']);
		$this->assertTrue($return);
		$this->assertSame('var testName = {"a":"b"};', wp_scripts()->registered[__CLASS__.'1.js']->extra['data']);
	}
}
