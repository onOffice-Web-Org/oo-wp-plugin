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

use Generator;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelNumericRange;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverterNumericRange;
use onOffice\WPlugin\Types\Field;

class TestClassDefaultValueModelToOutputConverterNumericRange
	extends \WP_UnitTestCase
{
	/**
	 * @dataProvider dataProviderModel
	 * @param array $expectedResult
	 * @param DefaultValueModelNumericRange $pModel
	 */
	public function testConvertToRowEmptyModel(array $expectedResult, DefaultValueModelNumericRange $pModel)
	{
		$pSubject = new DefaultValueModelToOutputConverterNumericRange;
		$actualResult = $pSubject->convertToRow($pModel);
		$this->assertSame($expectedResult, $actualResult);
	}

	/**
	 * @return Generator
	 */
	public function dataProviderModel(): Generator
	{
		$pField = new Field('testField', 'testModule');
		$pField->setIsRangeField(true);
		$pModel = new DefaultValueModelNumericRange(12, $pField);
		yield [['min' => .0, 'max' => .0], $pModel];
		$pModel1 = clone $pModel;
		$pModel1->setValueFrom(13.1);
		yield [['min' => 13.1, 'max' => .0], $pModel1];
		$pModel2 = clone $pModel1;
		$pModel2->setValueTo(15.0);
		yield [['min' => 13.1, 'max' => 15.0], $pModel2];
	}
}
