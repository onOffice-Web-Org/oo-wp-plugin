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

use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateUnitListSettings;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModelDB;
use WP_UnitTestCase;

class TestClassFormModelBuilderDBEstateUnitListSettings
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;
	
	/** @var FormModelBuilderDBAddress */
	private $_pInstance;
	
	/**
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$this->_pInstance = $this->_pContainer->get(FormModelBuilderDBEstateUnitListSettings::class);
	}
	
	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateUnitListSettings::createInputModelRandomOrder
	 */
	public function testCreateInputModelRandomOrder()
	{
		$pInputModelDB = $this->_pInstance->createInputModelRandomOrder();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}
}