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

use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelMultiselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelSingleselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueUpdate;
use onOffice\WPlugin\Types\Field;
use WP_UnitTestCase;
use wpdb;


/**
 *
 */

class TestClassDefaultValueUpdate
	extends WP_UnitTestCase
{
	/** @var \wpdb */
	private $_pWPDB = null;

	/** @var DefaultValueUpdate */
	private $_pDefaultValueUpdate;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pWPDB = $this->getMockBuilder(wpdb::class)
			->disableOriginalConstructor()
			->getMock();
		$this->_pWPDB->prefix = 'wp_test_';
		$this->_pDefaultValueUpdate = new DefaultValueUpdate($this->_pWPDB);
	}


	/**
	 *
	 */

	public function testUpdateSingleselect()
	{
		$this->_pWPDB->expects($this->once())->method('insert')
			->with('wp_test_oo_plugin_fieldconfig_form_defaults_values', ['defaults_id' => 444, 'value' => 'testValue'])
			->will($this->returnValue(true));
		$this->_pWPDB->expects($this->once())->method('delete')
			->with('wp_test_oo_plugin_fieldconfig_form_defaults_values', ['defaults_id' => 444])
			->will($this->returnValue(true));

		$pField = new Field('testFieldSingleselect1', 'testModule');
		$pModel = new DefaultValueModelSingleselect(13, $pField);
		$pModel->setValue('testValue');
		$pModel->setDefaultsId(444);
		$this->_pDefaultValueUpdate->updateSingleselect($pModel);
	}


	/**
	 *
	 */

	public function testUpdateMultiselect()
	{
		$this->_pWPDB->expects($this->once())->method('delete')
			->with('wp_test_oo_plugin_fieldconfig_form_defaults_values', ['defaults_id' => 445])
			->will($this->returnValue(true));
		$this->_pWPDB->expects($this->exactly(2))->method('insert')
			->with('wp_test_oo_plugin_fieldconfig_form_defaults_values', $this->logicalOr(
				$this->equalTo(['defaults_id' => 445, 'value' => 'testValue1']),
				$this->equalTo(['defaults_id' => 445, 'value' => 'testValue2'])
			))
			->will($this->returnValue(true));

		$pField = new Field('testFieldMultiselect1', 'testModule');
		$pModel = new DefaultValueModelMultiselect(14, $pField);
		$pModel->setValues(['testValue1', 'testValue2']);
		$pModel->setDefaultsId(445);
		$this->_pDefaultValueUpdate->updateMultiselect($pModel);
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\Field\DefaultValue\DefaultValueSaveException
	 * @expectedExceptionMessage defaultsId cannot be 0
	 *
	 */

	public function testFailedPreCheck()
	{
		$pField = new Field('testFieldSingleselect1', 'testModule');
		$pModel = new DefaultValueModelSingleselect(13, $pField);
		$pModel->setValue('testValue');
		$this->_pDefaultValueUpdate->updateSingleselect($pModel);
	}


	/**
	 *
	 * @expectedException \onOffice\WPlugin\Field\DefaultValue\DefaultValueSaveException
	 * @expectedExceptionMessage Insert/Delete/Update failed
	 *
	 */

	public function testFailedCheckWPDB()
	{
		$this->_pWPDB->expects($this->once())->method('delete')
			->with('wp_test_oo_plugin_fieldconfig_form_defaults_values', ['defaults_id' => 445])
			->will($this->returnValue(false));
		$pField = new Field('testFieldSingleselect1', 'testModule');
		$pModel = new DefaultValueModelSingleselect(13, $pField);
		$pModel->setValue('testValue');
		$pModel->setDefaultsId(445);
		$this->_pDefaultValueUpdate->updateSingleselect($pModel);
	}
}