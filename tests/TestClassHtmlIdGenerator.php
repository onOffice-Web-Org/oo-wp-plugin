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

use onOffice\WPlugin\Utility\HtmlIdGenerator;
use WP_UnitTestCase;


/**
 *
 */

class TestClassHtmlIdGenerator
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGenerateByString()
	{
		$input = 'abc_DEF_/342/öüpß.,k';
		$this->assertSame('abcDEF342pk', HtmlIdGenerator::generateByString($input));
	}
}
