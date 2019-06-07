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

namespace onOffice\WPlugin;

use DI\Container;
use Generator;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCode;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeBuilder;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeRegistrator;
use WP_UnitTestCase;

/**
 *
 */

class TestClassContentFilterShortCodeRegistrator
	extends WP_UnitTestCase
{
	/** @var ContentFilterShortCodeRegistrator */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pBuilder = $this->getMockBuilder(ContentFilterShortCodeBuilder::class)
			->setMethods(['buildAllContentFilterShortCodes'])
			->setConstructorArgs([new Container])
			->getMock();
		$pBuilder->expects($this->once())
			->method('buildAllContentFilterShortCodes')
			->will($this->returnCallback(function(): Generator {
				$pContentFilterShortCode1 = $this->getMockBuilder(ContentFilterShortCode::class)->getMock();
				$pContentFilterShortCode1->method('getTag')->will($this->returnValue('test1'));
				$pContentFilterShortCode2 = $this->getMockBuilder(ContentFilterShortCode::class)->getMock();;
				$pContentFilterShortCode2->method('getTag')->will($this->returnValue('test2'));
				yield $pContentFilterShortCode1;
				yield $pContentFilterShortCode2;
			}));
		$this->_pSubject = new ContentFilterShortCodeRegistrator($pBuilder);
	}


	/**
	 *
	 */

	public function testRegister()
	{
		$registeredShortCodesBefore = $this->getRegisteredShortCodes();
		$this->_pSubject->register();
		$registeredShortCodesAfter = $this->getRegisteredShortCodes();
		$diff = array_diff_key($registeredShortCodesAfter, $registeredShortCodesBefore);
		$this->assertCount(2, $diff);

		$this->assertArrayHasKey('test1', $diff);
		$this->assertArrayHasKey('test2', $diff);
	}


	/**
	 *
	 * @global array $shortcode_tags
	 * @return array
	 *
	 */

	private function getRegisteredShortCodes(): array
	{
		global $shortcode_tags;
		return $shortcode_tags ?? [];
	}
}
