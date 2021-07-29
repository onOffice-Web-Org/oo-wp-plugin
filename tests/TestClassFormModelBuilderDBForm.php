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

use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use onOffice\WPlugin\Model\InputModelDB;
use WP_UnitTestCase;

class TestClassFormModelBuilderDBForm
	extends WP_UnitTestCase
{
	/** @var InputModelDBFactory */
	private $_pInputModelFactoryDBEntry;
	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pInputModelFactoryDBEntry = new InputModelDBFactory(new InputModelDBFactoryConfigForm());

	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilderDBForm::createInputModelContactType
	 */
	public function testCreateInputModelContactType()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBForm::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getDataContactType'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');
		$pInstance->method('getDataContactType')->willReturn([]);

		$pInputModelDB = $pInstance->createInputModelContactType();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}
}