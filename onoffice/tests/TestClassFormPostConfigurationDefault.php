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

use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Form\FormPostConfigurationDefault;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;
use WP_UnitTestCase;

/**
 *
 */

class TestClassFormPostConfigurationDefault
	extends WP_UnitTestCase
{
	/** @var FormPostConfigurationDefault */
	private $_pSubject = null;

	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSubject = new FormPostConfigurationDefault();
	}


	/**
	 *
	 */

	public function testGetPostVars()
	{
		$_POST = ['asdf' => 'test'];
		$this->assertEquals(['asdf' => 'test'], $this->_pSubject->getPostVars());
	}


	/**
	 *
	 */

	public function testGetPostvarCaptchaToken()
	{
		$_POST = [];
		$this->assertEquals('', $this->_pSubject->getPostvarCaptchaToken());
	}


	/**
	 *
	 */

	public function testGetFieldnames()
	{
		$this->assertInstanceOf(Fieldnames::class, $this->_pSubject->getFieldnames());
	}


	/**
	 *
	 */

	public function testGetLogger()
	{
		$this->assertInstanceOf(Logger::class, $this->_pSubject->getLogger());
	}


	/**
	 *
	 */

	public function testGetWPOptionsWrapper()
	{
		$this->assertInstanceOf(WPOptionWrapperDefault::class, $this->_pSubject->getWPOptionsWrapper());
	}
}
