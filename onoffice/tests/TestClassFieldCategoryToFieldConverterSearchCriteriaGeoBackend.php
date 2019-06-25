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

use onOffice\WPlugin\Field\Collection\FieldCategoryToFieldConverterSearchCriteriaGeoBackend;
use onOffice\WPlugin\Field\Collection\FieldRowConverterSearchCriteria;
use WP_UnitTestCase;

/**
 *
 */

class TestClassFieldCategoryToFieldConverterSearchCriteriaGeoBackend
	extends WP_UnitTestCase
{
	/** @var FieldCategoryToFieldConverterSearchCriteriaGeoBackend */
	private $_pSubject = null;

	/** @var array */
	private $_categoryData = [
		'input' => [
			'name' => 'Umkreis',
			'fields' => [
				'range_land' => [
					'label' => 'Land',
				],
			],
		],
		'expectedResult' => [
			'geoPosition' => [
				'id' => 'geoPosition',
				'type' => 'float',
				'label' => 'Geo Position',
				'tablename' => 'ObjSuchkriterien',
				'module' => 'searchcriteria',
				'content' => 'Search Criteria',
				'permittedvalues' => [],
				'rangefield' => false,
			],
		],
	];


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pFieldRowConverter = new FieldRowConverterSearchCriteria();
		$this->_pSubject = new FieldCategoryToFieldConverterSearchCriteriaGeoBackend($pFieldRowConverter);
	}


	/**
	 *
	 */

	public function testConvertCategoryInactive()
	{
		$category = [
			'name' => 'test',
			'fields' => [
				'testField1' => [
					'label' => 'Test 1',
				],
			],
		];
		$result = iterator_to_array($this->_pSubject->convertCategory($category));
		$this->assertEquals([], $result);
	}


	/**
	 *
	 */

	public function testConvertCategoryActive()
	{
		$actualResult = iterator_to_array
			($this->_pSubject->convertCategory($this->_categoryData['input']));
		$this->assertEquals($this->_categoryData['expectedResult'], $actualResult);
	}
}
