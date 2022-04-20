<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
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

use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateUnitListSettings;
use onOffice\WPlugin\Model\InputModelDB;
use WP_UnitTestCase;

class TestClassFormModelBuilderDBEstateUnitListSettings
	extends WP_UnitTestCase
{
	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateUnitListSettings::createInputModelRandomOrder
	 */
	public function testCreateInputModelRandomOrder()
	{
		$pFormModelBuilderDBEstateUnitListSettings = new FormModelBuilderDBEstateUnitListSettings();
		$pInputModelDB = $pFormModelBuilderDBEstateUnitListSettings->createInputModelRandomOrder();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}
}