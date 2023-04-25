<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

use onOffice\WPlugin\Field\DefaultValue\DefaultValueEstateDelete;
use onOffice\WPlugin\Field\DefaultValue\Exception\DefaultValueDeleteException;
use WP_UnitTestCase;
use wpdb;

/**
 *
 */

class TestClassDefaultValueEstateDelete
	extends WP_UnitTestCase
{
	/** @var DefaultValueEstateDelete */
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
		$this->_pSubject = new DefaultValueEstateDelete($this->_pWPDB);
	}


	/**
	 * @throws DefaultValueDeleteException
	 */
	public function testDeleteByEstateIdAndFieldNamesEmptyListField()
	{
		$this->_pWPDB->expects($this->never())->method('query');
		$this->_pSubject->deleteByEstateIdAndFieldNames(13, []);
	}

	/**
	 * @throws DefaultValueDeleteException
	 */
	public function testDeleteByEstateIdAndFieldNames()
	{
		$expectedQuery = "DELETE wp_test_oo_plugin_fieldconfig_estate_defaults, wp_test_oo_plugin_fieldconfig_estate_defaults_values "
			."FROM wp_test_oo_plugin_fieldconfig_estate_defaults "
			."INNER JOIN wp_test_oo_plugin_fieldconfig_estate_defaults_values "
			."ON wp_test_oo_plugin_fieldconfig_estate_defaults.defaults_id = wp_test_oo_plugin_fieldconfig_estate_defaults_values.defaults_id WHERE "
			."wp_test_oo_plugin_fieldconfig_estate_defaults.estate_id = '13' AND "
			."wp_test_oo_plugin_fieldconfig_estate_defaults.fieldname IN('testField1', 'testField2')";
		$this->_pWPDB->expects($this->once())->method('query')->with($expectedQuery)->will($this->returnValue(true));

		$this->_pSubject->deleteByEstateIdAndFieldNames(13, ['testField1', 'testField2']);
	}

	/**
	 * @throws DefaultValueDeleteException
	 */
	public function testDeleteByEstateIdAndFieldNamesFailureFalse()
	{
		$this->expectException(DefaultValueDeleteException::class);
		$this->_pWPDB->expects($this->once())->method('query')->will($this->returnValue(false));
		$this->_pSubject->deleteByEstateIdAndFieldNames(13, ['testField2']);
	}
}
