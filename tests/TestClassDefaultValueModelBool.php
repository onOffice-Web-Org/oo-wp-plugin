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

use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelBool;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 */

class TestClassDefaultValueModelBool
	extends WP_UnitTestCase
{
	/**
	 *
	 */
	public function testDefault()
	{
		$pDefaultValueModelBool = $this->createInstance();
		$this->assertFalse($pDefaultValueModelBool->getValue());
	}

	/**
	 *
	 */
	public function testSetValue()
	{
		$pDefaultValueModelBool = $this->createInstance();
		$pDefaultValueModelBool->setValue(true);
		$this->assertTrue($pDefaultValueModelBool->getValue());
	}

	/**
	 * @return DefaultValueModelBool
	 */
	private function createInstance(): DefaultValueModelBool
	{
		$pField = new Field('testField', 'testModule');
		$pField->setType(FieldTypes::FIELD_TYPE_BOOLEAN);
		return new DefaultValueModelBool(13, $pField);
	}
}