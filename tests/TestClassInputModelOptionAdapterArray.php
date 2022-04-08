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

use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Model\InputModelOptionAdapterArray;
use WP_UnitTestCase;

/**
 *
 */
class TestClassInputModelOptionAdapterArray
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGenerateValuesArray()
	{
		$pInputModelOption = new InputModelOption('_optionGroup', 'name', 'label', 'boolean');
		$pInputModelOption->setValue('value');
		$pInputModelOptionAdapterArray = new InputModelOptionAdapterArray();
		$pInputModelOptionAdapterArray->addInputModelOption($pInputModelOption);
		$value = $pInputModelOptionAdapterArray->generateValuesArray();
		$this->assertEquals(['name' => 'value'], $value);
	}
}