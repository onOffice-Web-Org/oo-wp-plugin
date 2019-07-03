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

use onOffice\WPlugin\WP\UnknownPageException;
use WP_UnitTestCase;

/**
 *
 */

class TestClassUnknownPageException
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testPrintFormatted()
	{
		$pUnknownPageException = new UnknownPageException('Testpage');
		$this->assertEquals('Bad path "Testpage". The Page was not found.',
			$pUnknownPageException->printFormatted());
	}
}
