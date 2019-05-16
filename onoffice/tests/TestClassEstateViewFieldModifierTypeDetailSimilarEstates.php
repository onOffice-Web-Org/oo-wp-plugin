<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypeDetailSimilarEstates;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassEstateViewFieldModifierTypeDetailSimilarEstates
	extends WP_UnitTestCase
{
	/** @var array */
	private $_testViewFields = [
		'viewField1',
		'viewField2',
		'viewField3',
		'viewField4',
		'viewField5',
	];

	/** @var array */
	private $_expectedFieldResult = [
		'Id',
		'viewField1',
		'viewField2',
		'viewField3',
		'viewField4',
		'viewField5',
		'laengengrad',
		'breitengrad',
		'plz',
		'vermarktungsart',
		'objektart',
		'strasse',
		'land',
	];


	/**
	 *
	 */

	public function testApiFields()
	{
		$pInstance = new EstateViewFieldModifierTypeDetailSimilarEstates($this->_testViewFields);
		$this->assertEqualSets($this->_expectedFieldResult, $pInstance->getAPIFields());
	}


	/**
	 *
	 */

	public function testVisibleFields()
	{
		$pInstance = new EstateViewFieldModifierTypeDetailSimilarEstates($this->_testViewFields);
		$this->assertEqualSets($this->_expectedFieldResult, $pInstance->getVisibleFields());
	}


	/**
	 *
	 */

	public function testReduceRecord()
	{
		$record = [
			'testField1' => 'hello',
			'plz' => '85333',
			'testField2' => ['a', 'b', 3],
		];

		$pInstance = new EstateViewFieldModifierTypeDetailSimilarEstates($this->_testViewFields);
		$this->assertEquals($record, $pInstance->reduceRecord($record));
	}
}
