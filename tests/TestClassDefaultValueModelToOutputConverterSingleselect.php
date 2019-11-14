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
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelSingleselect;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverterSingleselect;
use onOffice\WPlugin\Types\Field;
use WP_UnitTestCase;

/**
 *
 */

class TestClassDefaultValueModelToOutputConverterSingleselect
	extends WP_UnitTestCase
{
	/**
	 *
	 * @dataProvider getDataModel
	 *
	 * @param DefaultValueModelSingleselect $pDataModel
	 * @param array $expectation
	 *
	 */

	public function testConvert(DefaultValueModelSingleselect $pDataModel, array $expectation)
	{
		$pConverter = new DefaultValueModelToOutputConverterSingleselect();
		$result = $pConverter->convertToRow($pDataModel);
		$this->assertEquals($expectation, $result);
	}


	/**
	 *
	 * @return Generator
	 *
	 */

	public function getDataModel(): Generator
	{
		$pField = new Field('testField', 'testModule');
		$pDataModelOne = new DefaultValueModelSingleselect(13, $pField);
		yield [$pDataModelOne, ['']];

		$pDataModelTwo = clone $pDataModelOne;
		$pDataModelTwo->setValue('testValue');
		yield [$pDataModelTwo, ['testValue']];
	}
}
