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
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFieldsFilter;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListViewAddress;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListViewAddressFactory;
use onOffice\WPlugin\Filter\FilterBuilderInputVariablesFactory;
use WP_UnitTestCase;

/**
 *
 */

class TestClassDefaultFilterBuilderListViewAddressFactory
	extends WP_UnitTestCase
{

	/** @var DefaultFilterBuilderListViewAddressFactory */
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

		$pFieldsCollectionBuilderShort = $pContainer->get(FieldsCollectionBuilderShort::class);
		$pFilterBuilderFactory = $pContainer->get(FilterBuilderInputVariablesFactory::class);
		$pCompoundFieldsFilter = $pContainer->get(CompoundFieldsFilter::class);

		$this->_pInstance = new DefaultFilterBuilderListViewAddressFactory(
				$pFieldsCollectionBuilderShort, $pCompoundFieldsFilter, $pFilterBuilderFactory);
	}


	/**
	 *
	 */

	public function testConstruct()
	{
		$this->assertInstanceOf(DefaultFilterBuilderListViewAddressFactory::class, $this->_pInstance);
	}




	/**
	 *
	 * @covers onOffice\WPlugin\Filter\DefaultFilterBuilderListViewAddressFactory::create
	 *
	 */

	public function testCreate()
	{
		$pDataListViewAddress = new DataListViewAddress(11, 'Name');

		$pResult = $this->_pInstance->create($pDataListViewAddress);
		$this->assertInstanceOf(DefaultFilterBuilderListViewAddress::class, $pResult);
	}
}
