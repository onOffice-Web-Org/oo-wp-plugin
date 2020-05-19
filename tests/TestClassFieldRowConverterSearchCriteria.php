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

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Field\Collection\FieldRowConverterSearchCriteria;
use onOffice\WPlugin\Region\RegionController;
use WP_UnitTestCase;


/**
 *
 */

class TestClassFieldRowConverterSearchCriteria
	extends WP_UnitTestCase
{
	/** @var array */
	private $_testData = [
		'basic values' => [
			[
				'name' => 'testField',
				'type' => 'multiselect',
			],
			[
				'label' => 'testField',
				'tablename' => 'ObjSuchkriterien',
				'module' => 'searchcriteria',
				'content' => 'Search Criteria',
				'permittedvalues' => [],
				'rangefield' => false,
				'type' => 'multiselect',
			],
		],
		'variable values' => [
			[
				'name' => 'testField1',
				'values' => ['abc', 'def'],
				'rangefield' => true,
				'type' => 'multiselect',
				'additionalTranslations' => [
					'testField1__von' => 'testField1 von',
					'testField1__bis' => 'testField1 bis',
				],
			],
			[
				'label' => 'testField1',
				'tablename' => 'ObjSuchkriterien',
				'module' => 'searchcriteria',
				'content' => 'Search Criteria',
				'permittedvalues' => ['abc', 'def'],
				'type' => 'multiselect',
				'rangefield' => true,
				'additionalTranslations' => [
					'testField1__von' => 'testField1 von',
					'testField1__bis' => 'testField1 bis',
				],
			],
		],
	];

	/**
	 *
	 * @dataProvider rowProvider
	 *
	 * @param array $input
	 * @param array $expected
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function testConvertRow(array $input, array $expected)
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pRegionController = $this->getMockBuilder(RegionController::class)
			->disableOriginalConstructor()
			->getMock();
		$pContainer->set(RegionController::class, $pRegionController);
		$pFieldRowConverterSearchCriteria = $pContainer->get(FieldRowConverterSearchCriteria::class);
		$result = $pFieldRowConverterSearchCriteria->convertRow($input);
		$this->assertEquals($expected, $result);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function rowProvider(): array
	{
		return $this->_testData;
	}
}

