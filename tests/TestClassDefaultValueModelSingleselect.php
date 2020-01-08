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

use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelSingleselect;
use onOffice\WPlugin\Types\Field;
use WP_UnitTestCase;


/**
 *
 */

class TestClassDefaultValueModelSingleselect
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testDefault()
	{
		$pField = new Field('testField', 'testModule');
		$pDefaultValueModel = new DefaultValueModelSingleselect(3, $pField);
		$this->assertSame('', $pDefaultValueModel->getValue());
		$this->assertSame($pField, $pDefaultValueModel->getField());
		$this->assertSame(3, $pDefaultValueModel->getFormId());
	}


	/**
	 *
	 */

	public function testSetter()
	{
		$pField = new Field('testField', 'testModule');
		$pDefaultValueModel = new DefaultValueModelSingleselect(4, $pField);
		$pDefaultValueModel->setValue('bonjour');
		$this->assertSame('bonjour', $pDefaultValueModel->getValue());
	}
}
