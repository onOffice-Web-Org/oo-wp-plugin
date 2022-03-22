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
use onOffice\WPlugin\Model\InputModelOption;
use WP_UnitTestCase;

/**
 *
 */
class TestClassInputModelOption
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testDefaultValues(): InputModelOption
	{
		$pInputModelOption = new InputModelOption('_optionGroup', 'name', 'label', 'boolean');
		$this->assertEquals('_optionGroup', $pInputModelOption->getOptionGroup());
		$this->assertEquals('name', $pInputModelOption->getName());
		$this->assertEquals('label', $pInputModelOption->getLabel());
		$this->assertEquals('boolean', $pInputModelOption->getType());
		$this->assertFalse($pInputModelOption->getDefault());
		return $pInputModelOption;
	}


	/**
	 *
	 * @depends testDefaultValues
	 *
	 */

	public function testSetter(InputModelOption $pInputModelOption): InputModelOption
	{
		$pInputModelOption->setDescription('testTable');
		$this->assertEquals('testTable', $pInputModelOption->getDescription());
		$pInputModelOption->setDefault(true);
		$this->assertTrue($pInputModelOption->getDefault());
		$pInputModelOption->setShowInRest(true);
		$this->assertTrue($pInputModelOption->getShowInRest());
		$pInputModelOption->setSanitizeCallback(function () {
			return true;
		});
		$this->assertInstanceOf(Closure::class, $pInputModelOption->getSanitizeCallback());
		$pInputModelOption->setType('int');
		$this->assertEquals('int', $pInputModelOption->getType());
		return $pInputModelOption;
	}


	/**
	 *
	 * @depends testSetter
	 *
	 */

	public function testGetIdentifier(InputModelOption $pInputModelOption)
	{
		$this->assertEquals('_optionGroup-name', $pInputModelOption->getIdentifier());
	}
}