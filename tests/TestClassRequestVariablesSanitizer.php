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

namespace onOffice\tests;

use onOffice\WPlugin\RequestVariablesSanitizer;
use WP_UnitTestCase;

/**
 *
 */

class TestClassRequestVariablesSanitizer
	extends WP_UnitTestCase
{

	/**
	 *
	 * @covers onOffice\WPlugin\RequestVariablesSanitizer::getFilteredGet
	 * @covers onOffice\WPlugin\RequestVariablesSanitizer::getFiltered
	 *
	 * WordPress: Auto adds slashes to $_POST, $_GET, $_REQUEST, $_COOKIE
	 *
	 */

	public function testGetFilteredGet()
	{
		$_GET = ['abc' => '\*abc', 'qwe' => 1];

		$pInstance = new RequestVariablesSanitizer();
		$result = $pInstance->getFilteredGet('abc');

		$this->assertEquals('*abc', $result);
	}



	/**
	 *
	 * @covers onOffice\WPlugin\RequestVariablesSanitizer::getFilteredPost
	 * @covers onOffice\WPlugin\RequestVariablesSanitizer::getFiltered
	 *
	 */

	public function testGetFilteredPost()
	{
		$_POST = ['abc' => 'abc', 'qwe' => 1];

		$pInstance = new RequestVariablesSanitizer();
		$result = $pInstance->getFilteredPost('abc');

		$this->assertEquals('abc', $result);
	}

	/**
	 *
	 * @covers onOffice\WPlugin\RequestVariablesSanitizer::sanitizeFilterString
	 *
	 */
	public function testFilterInputString()
	{
		$value = RequestVariablesSanitizer::sanitizeFilterString('<b>abcßÖÄÜüöäüABC</b>');
		$this->assertEquals('abcßÖÄÜüöäüABC', $value);

		$value = RequestVariablesSanitizer::sanitizeFilterString('"');
		$this->assertEquals('&#34;', $value);

		$value = RequestVariablesSanitizer::sanitizeFilterString('\'');
		$this->assertEquals('&#39;', $value);

	}
}
