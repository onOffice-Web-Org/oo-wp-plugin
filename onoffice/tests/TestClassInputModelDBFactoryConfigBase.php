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

use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigBase;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryFilterableFields;
use ReflectionClass;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

abstract class TestClassInputModelDBFactoryConfigBase
	extends WP_UnitTestCase
{
	/** @var array */
	private $_tableNames = [];

	/** @var InputModelDBFactoryConfigBase */
	private $_pConcreteFactory = null;


	/**
	 *
	 */

	public function testGetConfig()
	{
		$config = $this->_pConcreteFactory->getConfig();
		$this->assertGreaterThan(10, $config);

		$pReflectionInputModelDBFactory = new ReflectionClass(InputModelDBFactory::class);
		$constantsInputModelDBFactory = $pReflectionInputModelDBFactory->getConstants();

		$pReflectionInputModelDBFactoryFilterableFields = new ReflectionClass
			(InputModelDBFactoryFilterableFields::class);
		$constantsInputModelDBFactoryFilterable =
			$pReflectionInputModelDBFactoryFilterableFields->getConstants();

		foreach ($config as $key => $configEntry) {
			$this->assertTrue(in_array($key, $constantsInputModelDBFactory) ||
				in_array($key, $constantsInputModelDBFactoryFilterable));
			$this->assertTrue(in_array
				($configEntry[InputModelDBFactoryConfigBase::KEY_TABLE], $this->_tableNames));
			$this->assertContainsOnly
				('string', [$configEntry[InputModelDBFactoryConfigBase::KEY_FIELD]]);
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getTableNames(): array
	{
		return $this->_tableNames;
	}


	/**
	 *
	 * @return InputModelDBFactoryConfigBase
	 *
	 */

	public function getConcreteFactory(): InputModelDBFactoryConfigBase
	{
		return $this->_pConcreteFactory;
	}


	/**
	 *
	 * @param array $tableNames
	 *
	 */

	public function setTableNames(array $tableNames)
	{
		$this->_tableNames = $tableNames;
	}


	/**
	 *
	 * @param InputModelDBFactoryConfigBase $pConcreteFactory
	 *
	 */

	public function setConcreteFactory(InputModelDBFactoryConfigBase $pConcreteFactory)
	{
		$this->_pConcreteFactory = $pConcreteFactory;
	}
}
