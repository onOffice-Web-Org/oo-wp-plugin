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

use Closure;
use onOffice\WPlugin\Controller\InputVariableReaderConfigFieldnames;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;
use function update_option;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassInputVariableReaderConfigFieldnames
	extends WP_UnitTestCase
{
	/** @var InputVariableReaderConfigFieldnames */
	private $_pSubject = null;

	/** @var Fieldnames */
	private $_pFieldnames = null;


	/**
	 *
	 */

	public function setUp()
	{
		parent::setUp();
		$_POST = [];
		$_GET = ['phptest' => 123, 'phptest1' => ['c']];
		$_REQUEST = array_merge($_GET, $_POST);

		$GLOBALS['wp_tests_options'] = [
			'timezone_string' => 'Europe/Berlin',
		];
		update_option('timezone_string', 'Europe/Berlin');
	}

	/**
	 *
	 */

	public function testConstruct()
	{
		$pConfig = new InputVariableReaderConfigFieldnames();
		$pClosureGetFieldnames = Closure::bind(function() {
			return $this->_pFieldnames;
		}, $pConfig, InputVariableReaderConfigFieldnames::class);
		$this->assertInstanceOf(Fieldnames::class, $pClosureGetFieldnames());
	}


	/**
	 *
	 */

	public function testLazyLoad()
	{
		$this->_pFieldnames->expects($this->once())->method('loadLanguage');

		$pClosureGetLazyLoad = Closure::bind(function() {
			return $this->_loadComplete;
		}, $this->_pSubject, InputVariableReaderConfigFieldnames::class);

		$this->assertFalse($pClosureGetLazyLoad());

		$this->_pSubject->getFieldType('test1', 'estate');
		$this->assertTrue($pClosureGetLazyLoad());
	}


	/**
	 *
	 */

	public function testGetFieldType()
	{
		$this->assertEquals('string', $this->_pSubject->getFieldType('test1', 'estate'));
		$this->assertEquals('int', $this->_pSubject->getFieldType('test2', 'estate'));
	}


	/**
	 *
	 */

	public function testGetIsRequestVarArray()
	{
		$this->assertFalse($this->_pSubject->getIsRequestVarArray('phptest'));
		$this->assertTrue($this->_pSubject->getIsRequestVarArray('phptest1'));
		$this->assertFalse($this->_pSubject->getIsRequestVarArray('unknown'));
	}


	/**
	 *
	 */

	public function testGetTimezoneString()
	{
		$result = $this->_pSubject->getTimezoneString();
		$this->assertEquals('Europe/Berlin', $result);
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pFieldnames = $this->getMockBuilder(Fieldnames::class)
			->setMethods(['__construct', 'getFieldInformation', 'loadLanguage'])
			->setConstructorArgs([new FieldsCollection()])
			->getMock();
		$this->_pFieldnames->method('getFieldInformation')->will($this->returnValueMap([
			['test1', 'estate', ['type' => 'string']],
			['test2', 'estate', ['type' => 'int']],
		]));
		$this->_pSubject = new InputVariableReaderConfigFieldnames($this->_pFieldnames);
	}
}
