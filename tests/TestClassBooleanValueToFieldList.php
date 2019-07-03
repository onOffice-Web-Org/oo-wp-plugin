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

use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigAddress;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Record\BooleanValueToFieldList;
use stdClass;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassBooleanValueToFieldList
	extends WP_UnitTestCase
{
	/** @var array */
	private $_fieldsArray = [
		'testField1',
		'testField2',
		'testField3',
		'testField4',
	];


	/**
	 *
	 */

	public function testFillCheckboxValues()
	{
		$pValues = new stdClass();
		$pBooleanValueToFieldList = new BooleanValueToFieldList
			(new InputModelDBFactoryConfigAddress(), $pValues);

		$hiddenFields = ['testField2', 'testField3'];
		$pValues->{'oopluginaddressfieldconfig-hidden'} = $hiddenFields;
		$pValues->{'oopluginaddressfieldconfig-fieldname'} = $this->_fieldsArray;
		$pBooleanValueToFieldList->fillCheckboxValues
			(InputModelDBFactoryConfigAddress::INPUT_FIELD_HIDDEN);
		$valuesArray = (array)$pValues->{'oopluginaddressfieldconfig-hidden'};
		foreach ($valuesArray as $key => $value) {
			$this->assertArrayHasKey($key, $this->_fieldsArray);
			if ($value === '0') {
				$this->assertFalse(in_array($this->_fieldsArray[$key], $hiddenFields));
			} elseif ($value === '1') {
				$this->assertContains($this->_fieldsArray[$key], $hiddenFields);
			} else {
				$this->assertTrue(false, 'Unknown Value');
			}
		}

	}


	/**
	 *
	 */

	public function testFillCheckboxUnset()
	{
		$pValues = new stdClass();
		$pBooleanValueToFieldList = new BooleanValueToFieldList
			(new InputModelDBFactoryConfigEstate(), $pValues);

		// test unset
		$pBooleanValueToFieldList->fillCheckboxValues
			(InputModelDBFactoryConfigEstate::INPUT_FIELD_FILTERABLE);
		$this->assertObjectHasAttribute('oopluginfieldconfig-filterable', $pValues);
		$this->assertEquals([], $pValues->{'oopluginfieldconfig-filterable'});
	}


	/**
	 *
	 */

	public function testGetValues()
	{
		$pValues = new stdClass();
		$pValues->asdf = 'test';
		$pBooleanValueToFieldList = new BooleanValueToFieldList
			(new InputModelDBFactoryConfigAddress(), $pValues);
		$this->assertEquals($pValues, $pBooleanValueToFieldList->getValues());
	}


	/**
	 *
	 */

	public function testGetInputModelFactory()
	{
		$pValues = new stdClass();
		$pValues->asdf = 'test';
		$pFactoryConfig = new InputModelDBFactoryConfigAddress();
		$pBooleanValueToFieldList = new BooleanValueToFieldList($pFactoryConfig, $pValues);
		$pInputModelFactory = $pBooleanValueToFieldList->getInputModelFactory();
		$this->assertInstanceOf(InputModelDBFactory::class, $pInputModelFactory);
		$this->assertEquals($pFactoryConfig, $pInputModelFactory->getInputModelDBFactoryConfig());
	}
}
