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

namespace onOffice\tests;

use Closure;
use DI\Container;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListViewAddress;
use onOffice\WPlugin\Filter\FilterBuilderInputVariables;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\CompoundFieldsFilter;
use PHPUnit\Framework\MockObject\MockObject;
use WP_UnitTestCase;

class TestClassDefaultFilterBuilderListViewAddress
	extends WP_UnitTestCase
{
	/** */
	const POST_FIELDS_FITLER = [
		'Plz' => [
			[
				'op' => 'in',
				'val' => [52068, 52070, 52072],
			],
		],
		'ArtDaten' => [
			[
				'op' => 'in',
				'val' => ['Interessent_Kauf'],
			],
			[
				'op' => 'not in',
				'val' => ['Makler'],
			]
		],
	];

	/** */
	const EXPECTED_RESULT = [
		'homepage_veroeffentlichen' => [
			[
				'op' => '=',
				'val' => 1
			],
		],
		'Plz' => [
			[
				'op' => 'in',
				'val' => [52068, 52070, 52072],
			],
		],
		'ArtDaten' => [
			[
				'op' => 'in',
				'val' => ['Interessent_Kauf'],
			],
			[
				'op' => 'not in',
				'val' => ['Makler'],
			]
		],
	];


	/** @var FilterBuilderInputVariables */
	private $_pFilterBuilderInputVariables = null;

	/**
	 * @var MockObject
	 */
	private $_pFieldsCollectionBuilderShort;

	/**
	 * @var MockObject
	 */
	private $_pCompoundFields;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
				->setConstructorArgs([new Container()])
				->getMock();

		$this->_pCompoundFields = $this->getMockBuilder(CompoundFieldsFilter::class)
				->getMock();

		$this->_pFilterBuilderInputVariables = $this->getMockBuilder(FilterBuilderInputVariables::class)
			->setConstructorArgs([onOfficeSDK::MODULE_ADDRESS])
			->setMethods(['getModule', 'getPostFieldsFilter'])
			->getMock();
	}


	public function testWrongModule()
	{
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Module must be address');
		$pDataListView = new DataListViewAddress(1, 'test');
		$this->_pFilterBuilderInputVariables->method('getModule')->willReturn(onOfficeSDK::MODULE_ESTATE);
		new DefaultFilterBuilderListViewAddress(
				$pDataListView,
				$this->_pFieldsCollectionBuilderShort,
				$this->_pCompoundFields,
				$this->_pFilterBuilderInputVariables);
	}


	/**
	 *
	 */

	public function testBuildFilter()
	{
		$pDataListView = new DataListViewAddress(1, 'test');
		$this->_pFilterBuilderInputVariables->method('getModule')->willReturn(onOfficeSDK::MODULE_ADDRESS);
		$pInstance = new DefaultFilterBuilderListViewAddress
			($pDataListView,
				$this->_pFieldsCollectionBuilderShort,
				$this->_pCompoundFields,
				$this->_pFilterBuilderInputVariables);

		$this->_pFilterBuilderInputVariables
			->method('getPostFieldsFilter')
			->willReturn(self::POST_FIELDS_FITLER);

		$this->assertEquals(self::EXPECTED_RESULT, $pInstance->buildFilter());
	}
}
