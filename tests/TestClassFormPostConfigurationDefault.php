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
use onOffice\WPlugin\Form\FormPostConfigurationDefault;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFields;
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
		$pFieldsCollectionBuildershort = new FieldsCollectionBuilderShort(new Container());
		$pCompoundFields = new CompoundFields();
		$this->_pSubject = new FormPostConfigurationDefault($pFieldsCollectionBuildershort, $pCompoundFields);
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


	/**
	 *
	 * @covers onOffice\WPlugin\Form\FormPostConfigurationDefault::getFieldsCollectionBuilderShort
	 *
	 */

	public function testGetFieldsCollectionBuilderShort()
	{
		$this->assertInstanceOf(FieldsCollectionBuilderShort::class, $this->_pSubject->getFieldsCollectionBuilderShort());
	}



	/**
	 *
	 * @covers onOffice\WPlugin\Form\FormPostConfigurationDefault::getCompoundFields
	 *
	 */

	public function testGetCompoundFields()
	{
		$this->assertInstanceOf(CompoundFields::class, $this->_pSubject->getCompoundFields());
	}
}