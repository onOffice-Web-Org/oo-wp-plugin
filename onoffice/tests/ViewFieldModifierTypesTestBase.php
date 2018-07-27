<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierTypes;
use ReflectionClass;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers ViewFieldModifierTypes
 *
 */

abstract class ViewFieldModifierTypesTestBase
	extends WP_UnitTestCase
{
	/** @var ViewFieldModifierTypes */
	private $_pViewFieldModifierTypes = null;


	/**
	 *
	 */

	public function testConstants()
	{
		$pReflection = new ReflectionClass($this->_pViewFieldModifierTypes);
		$constants = $pReflection->getConstants();

		$this->assertGreaterThanOrEqual(1, count($constants));
	}


	/**
	 *
	 */

	public function testGetMapping()
	{
		$mapping = $this->_pViewFieldModifierTypes->getMapping();
		$this->assertNotEmpty($mapping);
	}


	/**
	 *
	 * @depends testGetMapping
	 *
	 */

	public function testClass()
	{
		$mapping = $this->_pViewFieldModifierTypes->getMapping();

		foreach ($mapping as $type => $class) {
			$this->assertInternalType('string', $type);
			$this->assertStringStartsWith('onOffice\\WPlugin\\', $class);
			$this->assertTrue(class_exists($class), 'Class does not exist');
		}
	}


	/** @return ViewFieldModifierTypes */
	protected function getViewFieldModifierTypes(): ViewFieldModifierTypes
		{ return $this->_pViewFieldModifierTypes; }

	/** @param ViewFieldModifierTypes $pViewFieldModifierTypes */
	protected function setViewFieldModifierTypes(ViewFieldModifierTypes $pViewFieldModifierTypes)
		{ $this->_pViewFieldModifierTypes = $pViewFieldModifierTypes; }
}
