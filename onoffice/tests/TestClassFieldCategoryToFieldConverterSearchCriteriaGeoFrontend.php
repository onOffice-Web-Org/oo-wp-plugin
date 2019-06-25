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

use onOffice\WPlugin\Field\Collection\FieldCategoryToFieldConverterSearchCriteriaGeoFrontend;
use onOffice\WPlugin\Field\Collection\FieldRowConverterSearchCriteria;
use WP_UnitTestCase;


/**
 *
 */

class TestClassFieldCategoryToFieldConverterSearchCriteriaGeoFrontend
	extends WP_UnitTestCase
{
	/** @var FieldCategoryToFieldConverterSearchCriteriaGeoFrontend */
	private $_pSubject = null;

	/** @var array */
	private $_data = [
		'input' => [
			'name' => 'Umkreis',
			'fields' => [
				'range' => [
					'id' => 'range',
					'type' => 'float',
					'name' => 'Umkreis',
					'tablename' => 'ObjSuchkriterien',
					'module' => 'searchcriteria',
					'content' => 'Search Criteria',
					'permittedvalues' => [],
					'rangefield' => false,
				],
				'range_land' => [
					'id' => 'range_land',
					'type' => 'float',
					'name' => 'Land',
					'tablename' => 'ObjSuchkriterien',
					'module' => 'searchcriteria',
					'content' => 'Search Criteria',
					'permittedvalues' => [],
					'rangefield' => false,
				],
			],
		],
		'expectedResult' => [
			'range' => [
				'id' => 'range',
				'type' => 'float',
				'label' => 'Umkreis',
				'tablename' => 'ObjSuchkriterien',
				'module' => 'searchcriteria',
				'content' => 'Search Criteria',
				'permittedvalues' => [],
				'rangefield' => false,
			],
			'range_land' => [
				'id' => 'range_land',
				'type' => 'float',
				'label' => 'Land',
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
		$this->_pSubject = new FieldCategoryToFieldConverterSearchCriteriaGeoFrontend($pFieldRowConverter);
	}


	/**
	 *
	 */

	public function testConvertCategoryNonGeo()
	{
		$category = [
			'name' => 'testCategory1',
		];
		$this->assertEquals([], iterator_to_array($this->_pSubject->convertCategory($category)));
	}


	/**
	 *
	 */

	public function testConvertCategoryWithGeo()
	{
		$actualResult = iterator_to_array($this->_pSubject->convertCategory($this->_data['input']));
		$this->assertEquals($this->_data['expectedResult'], $actualResult);
	}
}