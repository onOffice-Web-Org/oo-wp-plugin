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
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverterMultiSelect;
use onOffice\WPlugin\Types\Field;
use WP_UnitTestCase;

class TestClassDefaultValueModelToOutputConverterMultiSelect
	extends WP_UnitTestCase
{
	/**
	 *
	 */
	public function testConvertToRow()
	{
		$pField = new Field('testFieldMultiSelect', 'testModule');
		$pDataModel = new DefaultValueModelMultiselect(23, $pField);
		$pDataModel->setValues(['value1', 'value2', 'value3']);
		$pSubject = new DefaultValueModelToOutputConverterMultiSelect;
		$result = $pSubject->convertToRow($pDataModel);
		$this->assertSame(['value1', 'value2', 'value3'], $result);
	}
}