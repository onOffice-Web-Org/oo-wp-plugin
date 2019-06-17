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

use DI\Container;
use Generator;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeBuilder;
use WP_UnitTestCase;

/**
 *
 */

class TestClassContentFilterShortCodeBuilder
	extends WP_UnitTestCase
{
	/** @var ContentFilterShortCodeBuilder */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pContainer = new Container();
		$this->_pSubject = new ContentFilterShortCodeBuilder($pContainer);
	}


	/**
	 *
	 */

	public function testBuildContentFilterShortCodes()
	{
		$classes = $this->_pSubject->getClasses();

		$pGenerator = $this->_pSubject->buildAllContentFilterShortCodes();
		$this->assertInstanceOf(Generator::class, $pGenerator);

		foreach ($pGenerator as $index => $pInstance) {
			$this->assertInstanceOf($classes[$index], $pInstance);
		}
	}


	/**
	 *
	 */

	public function testGetClasses()
	{
		$this->assertCount(4, $this->_pSubject->getClasses());
	}
}
