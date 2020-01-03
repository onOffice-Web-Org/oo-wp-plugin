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

use Generator;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueDelete;
use onOffice\WPlugin\Field\DefaultValue\Exception\DefaultValueDeleteException;
use WP_UnitTestCase;
use wpdb;

/**
 *
 */

class TestClassDefaultValueDelete
	extends WP_UnitTestCase
{
	/** @var DefaultValueDelete */
	private $_pSubject = null;

	/** @var wpdb */
	private $_pWPDB = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pWPDB = $this->getMockBuilder(wpdb::class)
			->setMethods(['query', 'prepare'])
			->disableOriginalConstructor()
			->getMock();
		$this->_pWPDB->prefix = 'wp_test_';
		$this->_pSubject = new DefaultValueDelete($this->_pWPDB);
	}

	/**
	 * @throws DefaultValueDeleteException
	 */
	public function testDeleteSingleDefaultValueByFieldname()
	{
		$expectedQuery = "DELETE wp_test_oo_plugin_fieldconfig_form_defaults, wp_test_oo_plugin_fieldconfig_form_defaults_values "
			."FROM wp_test_oo_plugin_fieldconfig_form_defaults "
			."INNER JOIN wp_test_oo_plugin_fieldconfig_form_defaults_values "
			."ON wp_test_oo_plugin_fieldconfig_form_defaults.defaults_id = wp_test_oo_plugin_fieldconfig_form_defaults_values.defaults_id WHERE "
			."wp_test_oo_plugin_fieldconfig_form_defaults.form_id = %d AND "
			."wp_test_oo_plugin_fieldconfig_form_defaults.fieldname = %s AND "
			."wp_test_oo_plugin_fieldconfig_form_defaults.locale = %s";
		$this->_pWPDB->expects($this->once())->method('prepare')
			->with($expectedQuery, 13, 'objektart', 'de_DE')
			->will($this->returnValue('testQuery'));
		$this->_pWPDB->expects($this->once())->method('query')->will($this->returnValue(true));
		$this->_pSubject->deleteSingleDefaultValueByFieldname(13, 'objektart', 'de_DE');
	}

	/**
	 * @throws DefaultValueDeleteException
	 */
	public function testDeleteByFormIdAndFieldNamesEmptyFieldlist()
	{
		$this->_pWPDB->expects($this->never())->method('query');
		$this->_pSubject->deleteByFormIdAndFieldNames(13, []);
	}

	/**
	 * @throws DefaultValueDeleteException
	 */
	public function testDeleteByFormIdAndFieldNames()
	{
		$expectedQuery = "DELETE wp_test_oo_plugin_fieldconfig_form_defaults, wp_test_oo_plugin_fieldconfig_form_defaults_values "
			."FROM wp_test_oo_plugin_fieldconfig_form_defaults "
			."INNER JOIN wp_test_oo_plugin_fieldconfig_form_defaults_values "
			."ON wp_test_oo_plugin_fieldconfig_form_defaults.defaults_id = wp_test_oo_plugin_fieldconfig_form_defaults_values.defaults_id WHERE "
			."wp_test_oo_plugin_fieldconfig_form_defaults.form_id = '13' AND "
			."wp_test_oo_plugin_fieldconfig_form_defaults.fieldname IN('testField1', 'testField2')";
		$this->_pWPDB->expects($this->once())->method('query')->with($expectedQuery)->will($this->returnValue(true));

		$this->_pSubject->deleteByFormIdAndFieldNames(13, ['testField1', 'testField2']);
	}

	/**
	 * @dataProvider dataProviderDeleteByFormIdAndFieldNamesFailure
	 * @param mixed $returnValue
	 * @throws DefaultValueDeleteException
	 */
	public function testDeleteByFormIdAndFieldNamesFailureFalse($returnValue)
	{
		$this->expectException(DefaultValueDeleteException::class);
		$this->_pWPDB->expects($this->once())->method('query')->will($this->returnValue($returnValue));
		$this->_pSubject->deleteByFormIdAndFieldNames(13, ['testField2']);
	}

	/**
	 * @return Generator
	 */
	public function dataProviderDeleteByFormIdAndFieldNamesFailure(): Generator
	{
		yield [false];
		yield [0];
	}

	/**
	 * @throws DefaultValueDeleteException
	 */
	public function testDeleteSingleDefaultValueByFieldnameFailure()
	{
		$this->expectException(DefaultValueDeleteException::class);
		$this->_pWPDB->expects($this->once())->method('prepare')
			->will($this->returnValue('testQuery'));
		$this->_pWPDB->expects($this->once())->method('query')->will($this->returnValue(false));
		$this->_pSubject->deleteSingleDefaultValueByFieldname(13, 'objektart', 'de_DE');

	}

	/**
	 * @throws DefaultValueDeleteException
	 */
	public function testDeleteSingleDefaultValueById()
	{
		$expectedQuery = "DELETE wp_test_oo_plugin_fieldconfig_form_defaults, wp_test_oo_plugin_fieldconfig_form_defaults_values "
			."FROM wp_test_oo_plugin_fieldconfig_form_defaults "
			."INNER JOIN wp_test_oo_plugin_fieldconfig_form_defaults_values "
			."ON wp_test_oo_plugin_fieldconfig_form_defaults.defaults_id = wp_test_oo_plugin_fieldconfig_form_defaults_values.defaults_id WHERE "
			."wp_test_oo_plugin_fieldconfig_form_defaults.defaults_id = %d";
		$this->_pWPDB->expects($this->once())->method('prepare')->with($expectedQuery, 1337)->will($this->returnValue('testQuery2'));
		$this->_pWPDB->expects($this->once())->method('query')->with('testQuery2')->will($this->returnValue(true));
		$this->_pSubject->deleteSingleDefaultValueById(1337);
	}

	/**
	 * @throws DefaultValueDeleteException
	 */
	public function testDeleteSingleDefaultValueByIdFailure()
	{
		$this->expectException(DefaultValueDeleteException::class);
		$this->_pWPDB->expects($this->once())->method('prepare')->will($this->returnValue('testQuery2'));
		$this->_pWPDB->expects($this->once())->method('query')->with('testQuery2')->will($this->returnValue(false));
		$this->_pSubject->deleteSingleDefaultValueById(1337);
	}
}
