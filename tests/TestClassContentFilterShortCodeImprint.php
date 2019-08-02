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

use Exception;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeImprint;
use onOffice\WPlugin\Impressum;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Utility\Logger;
use WP_UnitTestCase;


/**
 *
 */

class TestClassContentFilterShortCodeImprint
	extends WP_UnitTestCase
{
	/** @var ContentFilterShortCodeImprint */
	private $_pContentFilterShortCodeImprint = null;

	/** @var Impressum */
	private $_pImpressum = null;

	/** @var Logger */
	private $_pLogger = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pImpressum = $this->getMockBuilder(Impressum::class)
			->setConstructorArgs([new SDKWrapper()])
			->setMethods(['load'])
			->getMock();
		$this->_pLogger = $this->getMockBuilder(Logger::class)
			->getMock();
		$this->_pContentFilterShortCodeImprint = new ContentFilterShortCodeImprint
			($this->_pImpressum, $this->_pLogger);
	}


	/**
	 *
	 */

	public function testReplaceShortCodes()
	{
		$this->addLoadMethod();

		$this->assertEquals('testResult1',
			$this->_pContentFilterShortCodeImprint->replaceShortCodes(['asd']));
		$this->assertEquals('testResult2',
			$this->_pContentFilterShortCodeImprint->replaceShortCodes(['test']));
	}


	/**
	 *
	 */

	public function testReplaceShortCodesEmpty()
	{
		$this->addLoadMethod();
		$this->assertEquals('', $this->_pContentFilterShortCodeImprint->replaceShortCodes([]));
		$this->assertEquals('',
			$this->_pContentFilterShortCodeImprint->replaceShortCodes(['asd', 'test']));
	}


	/**
	 *
	 */

	public function testReplaceShortCodesException()
	{
		$this->addLoadMethod();
		$pException = new Exception('test');
		$this->_pImpressum->method('load')->will($this->throwException($pException));
		$this->_pLogger->expects($this->once())
			->method('logErrorAndDisplayMessage')
			->with($pException)
			->will($this->returnValue('Caught Exception'));
		$this->assertEquals('Caught Exception',
			$this->_pContentFilterShortCodeImprint->replaceShortCodes(['boom']));
	}


	/**
	 *
	 */

	public function testGetTag()
	{
		$this->assertEquals('oo_basicdata', $this->_pContentFilterShortCodeImprint->getTag());
	}


	/**
	 *
	 */

	private function addLoadMethod()
	{
		$this->_pImpressum->method('load')->will($this->returnValue([
			'asd' => 'testResult1',
			'test' => 'testResult2',
		]));
	}
}
