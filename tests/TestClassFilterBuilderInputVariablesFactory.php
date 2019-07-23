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

use DI\Container;
use onOffice\WPlugin\Controller\InputVariableReaderConfig;
use onOffice\WPlugin\Controller\InputVariableReaderConfigTest;
use onOffice\WPlugin\Filter\FilterBuilderInputVariables;
use onOffice\WPlugin\Filter\FilterBuilderInputVariablesFactory;
use WP_UnitTestCase;

/**
 *
 */

class TestClassFilterBuilderInputVariablesFactory
	extends WP_UnitTestCase
{
	/** @var FilterBuilderInputVariablesFactory */
	private $_pInstance = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pContainer = new Container();
		$pContainer->set(InputVariableReaderConfig::class, new InputVariableReaderConfigTest());

		$this->_pInstance = $pContainer->get(FilterBuilderInputVariablesFactory::class);
	}


	/**
	 *
	 */

	public function testConstruct()
	{
		$this->assertInstanceOf(FilterBuilderInputVariablesFactory::class, $this->_pInstance);
	}


	/**

	 * @covers onOffice\WPlugin\Filter\FilterBuilderInputVariablesFactory::createForAddress
	 *
	 */

	public function testCreate()
	{
		$pResult = $this->_pInstance->createForAddress();
		$this->assertInstanceOf(FilterBuilderInputVariables::class, $pResult);
	}
}
