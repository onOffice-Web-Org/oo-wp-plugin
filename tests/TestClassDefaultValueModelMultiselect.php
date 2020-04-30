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

use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelMultiselect;
use onOffice\WPlugin\Types\Field;
use WP_UnitTestCase;


/**
 *
 */

class TestClassDefaultValueModelMultiselect
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testConstruct()
	{
		$pDefaultValueModelMultiselect = new DefaultValueModelMultiselect
			(14, new Field('testfieldA', 'testModuleA'));
		$this->assertEmpty($pDefaultValueModelMultiselect->getValues());
	}


	/**
	 *
	 */

	public function testSetValues()
	{
		$pDefaultValueModelMultiselect = new DefaultValueModelMultiselect
			(14, new Field('testfieldA', 'testModuleA'));
		$pDefaultValueModelMultiselect->setValues(['123', 'abc']);
		$this->assertSame(['123', 'abc'], $pDefaultValueModelMultiselect->getValues());
	}
}
