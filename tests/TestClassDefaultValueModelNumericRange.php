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

use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelNumericRange;
use onOffice\WPlugin\Types\Field;

class TestClassDefaultValueModelNumericRange
	extends \WP_UnitTestCase
{
	/** @var DefaultValueModelNumericRange */
	private $_pSubject = null;

	/** @var Field */
	private $_pField = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pField = new Field('testField', 'testModule');
		$this->_pSubject = new DefaultValueModelNumericRange(13, $this->_pField);
	}

	/**
	 *
	 */
	public function testDefaults()
	{
		$this->assertSame(0, $this->_pSubject->getDefaultsId());
		$this->assertSame($this->_pField, $this->_pSubject->getField());
		$this->assertSame(13, $this->_pSubject->getFormId());
		$this->assertSame(.0, $this->_pSubject->getValueFrom());
		$this->assertSame(.0, $this->_pSubject->getValueTo());
	}

	/**
	 *
	 */
	public function testValueFrom()
	{
		$this->assertSame(.0, $this->_pSubject->getValueFrom());
		$this->_pSubject->setValueFrom(133.3);
		$this->assertSame(133.3, $this->_pSubject->getValueFrom());
	}

	/**
	 *
	 */
	public function testValueTo()
	{
		$this->assertSame(.0, $this->_pSubject->getValueTo());
		$this->_pSubject->setValueTo(1337.6);
		$this->assertSame(1337.6, $this->_pSubject->getValueTo());
	}
}