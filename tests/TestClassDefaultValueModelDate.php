<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelDate;
use onOffice\WPlugin\Types\Field;

class TestClassDefaultValueModelDate
	extends \WP_UnitTestCase
{
	/** @var DefaultValueModelDate */
	private $_pSubject = null;

	/** @var Field */
	private $_pField = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pField = new Field('testField', 'testModule');
		$this->_pSubject = new DefaultValueModelDate(18, $this->_pField);
	}

	/**
	 *
	 */
	public function testDefaults()
	{
		$this->assertSame(0, $this->_pSubject->getDefaultsId());
		$this->assertSame($this->_pField, $this->_pSubject->getField());
		$this->assertSame(18, $this->_pSubject->getFormId());
		$this->assertSame('', $this->_pSubject->getValueFrom());
		$this->assertSame('', $this->_pSubject->getValueTo());
	}

	/**
	 *
	 */
	public function testValueFrom()
	{
		$this->assertSame('', $this->_pSubject->getValueFrom());
		$this->_pSubject->setValueFrom('2023/04/18');
		$this->assertSame('2023/04/18', $this->_pSubject->getValueFrom());
	}

	/**
	 *
	 */
	public function testValueTo()
	{
		$this->assertSame('', $this->_pSubject->getValueTo());
		$this->_pSubject->setValueTo('2023/04/19');
		$this->assertSame('2023/04/19', $this->_pSubject->getValueTo());
	}
}