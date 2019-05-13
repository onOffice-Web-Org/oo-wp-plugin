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

use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\Controller\Exception\UnknownFilterException;
use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\Utility\LoggerEnvironment;
use WP_UnitTestCase;
use function wp_get_current_user;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassLogger
	extends WP_UnitTestCase
{
	/** @var Logger */
	private $_pLogger = null;

	/** @var LoggerEnvironment */
	private $_pEnvironment = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pEnvironment = $this->getMock(LoggerEnvironment::class);
		$this->_pEnvironment->expects($this->once())->method('log')->with($this->anything());

		$pUserCapabilities = $this->getMockBuilder(UserCapabilities::class)
			->setMethods(['getCapabilityForRule'])
			->getMock();
		$pUserCapabilities
			->method('getCapabilityForRule')
			->with(UserCapabilities::RULE_DEBUG_OUTPUT)
			->will($this->returnValue('edit_pages'));
		$this->_pEnvironment
			->method('getUserCapabilities')
			->will($this->returnValue($pUserCapabilities));

		$this->_pLogger = new Logger($this->_pEnvironment);
		wp_get_current_user()->remove_cap('edit_pages');
	}


	/**
	 *
	 */

	public function testLogErrorAndDisplayMessageWithoutDebugRight()
	{
		$this->assertEquals('', $this->_pLogger->logErrorAndDisplayMessage(new \Exception));
	}


	/**
	 *
	 */

	public function testLogErrorAndDisplayMessageAPIClientCredentialsException()
	{
		wp_get_current_user()->add_cap('edit_pages');

		$pApiClientAction = $this->getMockBuilder(APIClientActionGeneric::class)
			->disableOriginalConstructor()
			->getMock();
		$pException = new APIClientCredentialsException($pApiClientAction);
		$expectation = '<big><strong>Please configure your onOffice API credentials first!</strong></big>';

		$this->assertEquals($expectation, $this->_pLogger->logErrorAndDisplayMessage($pException));
	}


	/**
	 *
	 */

	public function testLogErrorAndDisplayMessageDefault()
	{
		wp_get_current_user()->add_cap('edit_pages');

		$pException = new UnknownViewException('Test123');
		$expectation = '<pre><u><strong>[onOffice-Plugin]</strong> An error occured:</u>'
			.'<p>onOffice\WPlugin\DataView\UnknownViewException: Test123 in';

		$this->assertStringStartsWith($expectation, $this->_pLogger->logErrorAndDisplayMessage($pException));
	}


	/**
	 *
	 */

	public function testLogError()
	{
		$pException = new UnknownFilterException('test from '.__CLASS__, 13);
		$this->_pLogger->logError($pException);
	}
}
