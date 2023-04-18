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

use Generator;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelDate;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverterDate;
use onOffice\WPlugin\Types\Field;

class TestClassDefaultValueModelToOutputConverterDate
	extends \WP_UnitTestCase
{
	/**
	 * @dataProvider dataProviderModel
	 * @param array $expectedResult
	 * @param DefaultValueModelDate $pModel
	 */
	public function testConvertToRowEmptyModel(array $expectedResult, DefaultValueModelDate $pModel)
	{
		$pSubject = new DefaultValueModelToOutputConverterDate;
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
		$pModel = new DefaultValueModelDate(12, $pField);
		yield [['min' => '', 'max' => ''], $pModel];
		$pModel1 = clone $pModel;
		$pModel1->setValueFrom('2023/04/08');
		yield [['min' => '2023/04/08', 'max' => ''], $pModel1];
		$pModel2 = clone $pModel1;
		$pModel2->setValueTo('2023/04/09');
		yield [['min' => '2023/04/08', 'max' => '2023/04/09'], $pModel2];
	}
}
