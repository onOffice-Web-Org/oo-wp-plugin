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

use Generator;
use onOffice\WPlugin\ScriptLoader\ScriptLoader;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderBuilder;
use onOffice\WPlugin\ScriptLoader\ScriptLoaderRegistrator;
use WP_UnitTestCase;

/**
 *
 */

class TestClassScriptLoaderRegistrator
	extends WP_UnitTestCase
{
	/** @var ScriptLoaderRegistrator */
	private $_pSubject = null;

	/** @var ScriptLoader */
	private $_pScriptLoader = null;



	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pBuilder = $this->getMockBuilder(ScriptLoaderBuilder::class)
			->disableOriginalConstructor()
			->setMethods(['build'])
			->getMock();
		$pBuilder->expects($this->once())->method('build')->will($this->returnCallback(function(): Generator {
			$this->_pScriptLoader = $this->getMockBuilder(ScriptLoader::class)->getMock();
			yield $this->_pScriptLoader;
		}));
		$this->_pSubject = new ScriptLoaderRegistrator($pBuilder);
	}


	/**
	 *
	 */

	public function testRegister()
	{
		$this->assertSame($this->_pSubject, $this->_pSubject->generate());
		$this->_pScriptLoader->expects($this->once())->method('register');
		$this->_pSubject->register();
	}


	/**
	 *
	 */

	public function testEnqueue()
	{
		$this->assertSame($this->_pSubject, $this->_pSubject->generate());
		$this->_pScriptLoader->expects($this->once())->method('enqueue');
		$this->_pSubject->enqueue();
	}
}
