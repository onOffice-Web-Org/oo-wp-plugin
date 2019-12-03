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

use onOffice\WPlugin\Field\DefaultValue\DefaultValueCreate;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelMultiselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelNumericRange;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelSingleselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelText;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerInsertGeneric;
use onOffice\WPlugin\Types\Field;
use WP_UnitTestCase;

/**
 *
 */

class TestClassDefaultValueCreate
	extends WP_UnitTestCase
{
	/** @var DefaultValueCreate */
	private $_pSubject = null;

	/** @var RecordManagerFactory */
	private $_pRecordManagerFactory = null;

	/** @var RecordManagerInsertGeneric */
	private $_pRecordManagerInsertGeneric = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pRecordManagerFactory = $this->getMockBuilder(RecordManagerFactory::class)
			->setMethods(['createRecordManagerInsertGeneric'])
			->getMock();
		$this->_pRecordManagerInsertGeneric = $this->getMockBuilder(RecordManagerInsertGeneric::class)
			->setConstructorArgs(['testMainTable'])
			->getMock();
		$this->_pRecordManagerFactory->method('createRecordManagerInsertGeneric')
			->will($this->returnValue($this->_pRecordManagerInsertGeneric));

		$this->_pSubject = new DefaultValueCreate($this->_pRecordManagerFactory);
	}


	/**
	 *
	 */

	public function testCreateForSingleselect()
	{
		$this->_pRecordManagerFactory->expects($this->exactly(2))->method('createRecordManagerInsertGeneric');
		$this->_pRecordManagerInsertGeneric->expects($this->exactly(2))->method('insertByRow')
			->will($this->returnCallback(function(array $values) {
				if ($values === ['oo_plugin_fieldconfig_form_defaults' => ['form_id' => 3, 'fieldname' => 'testField1']]) {
					return 13;
				}
				if ($values === ['oo_plugin_fieldconfig_form_defaults_values' => [
						'defaults_id' => 13, 'locale' => '', 'value' => 'testDefaultValue']]) {
					return 37;
				}
				return 0;
			}));
		$pField = new Field('testField1', 'testModule1');
		$pDefaultValueModelSingleselect = new DefaultValueModelSingleselect(3, $pField);
		$pDefaultValueModelSingleselect->setValue('testDefaultValue');
		$result = $this->_pSubject->createForSingleselect($pDefaultValueModelSingleselect);
		$this->assertEquals(13, $result);
		$this->assertEquals(13, $pDefaultValueModelSingleselect->getDefaultsId());
	}


	/**
	 *
	 */

	public function testCreateForMultiselect()
	{
		$this->_pRecordManagerInsertGeneric->expects($this->exactly(3))->method('insertByRow')
				->will($this->returnCallback(function(array $values) {
				$valuesConf = [
					12 => ['oo_plugin_fieldconfig_form_defaults' => ['form_id' => 14, 'fieldname' => 'testField2']],
					55 => ['oo_plugin_fieldconfig_form_defaults_values' => ['defaults_id' => 12, 'locale' => '', 'value' => '123']],
					56 => ['oo_plugin_fieldconfig_form_defaults_values' => ['defaults_id' => 12, 'locale' => '', 'value' => 'abc']],
				];
				$returnValue = array_search($values, $valuesConf);
				return $returnValue ?: 0;
			}));
		$pField = new Field('testField2', 'testModule2');
		$pDefaultValue = new DefaultValueModelMultiselect(14, $pField);
		$pDefaultValue->setValues(['123', 'abc']);
		$this->assertEquals(12, $this->_pSubject->createForMultiselect($pDefaultValue));
		$this->assertEquals(12, $pDefaultValue->getDefaultsId());
	}


	/**
	 *
	 */

	public function testCreateForText()
	{
		$this->_pRecordManagerInsertGeneric->expects($this->exactly(3))->method('insertByRow')
			->will($this->returnCallback(function(array $values) {
				$valuesConf = [
					121 => ['oo_plugin_fieldconfig_form_defaults' => ['form_id' => 14, 'fieldname' => 'testField2']],
					130 => ['oo_plugin_fieldconfig_form_defaults_values' => [
						'defaults_id' => 121, 'locale' => 'de_DE', 'value' => 'Aachen']
					],
					56 => ['oo_plugin_fieldconfig_form_defaults_values' => [
						'defaults_id' => 121, 'locale' => 'fr_BE', 'value' => 'Aix-la-Chapelle']
					],
				];
				$returnValue = array_search($values, $valuesConf);
				return $returnValue ?: 0;
			}));
		$pField = new Field('testField2', 'testModule2');
		$pDefaultValues = new DefaultValueModelText(14, $pField);
		$pDefaultValues->addValueByLocale('de_DE', 'Aachen');
		$pDefaultValues->addValueByLocale('fr_BE', 'Aix-la-Chapelle');
		$resultId = $this->_pSubject->createForText($pDefaultValues);
		$this->assertEquals(121, $resultId);
		$this->assertEquals(121, $pDefaultValues->getDefaultsId());
	}


	public function testCreateForNumericRange()
	{
		$this->_pRecordManagerInsertGeneric->expects($this->exactly(3))->method('insertByRow')
			->will($this->returnCallback(function(array $values) {
				$valuesConf = [
					122 => ['oo_plugin_fieldconfig_form_defaults' => ['form_id' => 14, 'fieldname' => 'testField3']],
					130 => ['oo_plugin_fieldconfig_form_defaults_values' => [
						'defaults_id' => 121, 'locale' => '', 'value' => '1332.2']
					],
					56 => ['oo_plugin_fieldconfig_form_defaults_values' => [
						'defaults_id' => 121, 'locale' => '', 'value' => '1333.3']
					],
				];
				$returnValue = array_search($values, $valuesConf);
				return $returnValue ?: 0;
			}));
		$pField = new Field('testField3', 'testModule2');
		$pDefaultValues = new DefaultValueModelNumericRange(14, $pField);
		$pDefaultValues->setValueFrom(1332.2);
		$pDefaultValues->setValueTo(1333.3);
		$resultId = $this->_pSubject->createForNumericRange($pDefaultValues);
		$this->assertEquals(122, $resultId);
		$this->assertEquals(122, $pDefaultValues->getDefaultsId());
	}
}
