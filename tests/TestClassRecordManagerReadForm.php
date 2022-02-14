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
use DI\ContainerBuilder;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Record\RecordManager;
use WP_UnitTestCase;
use onOffice\WPlugin\Form;
use onOffice\SDK\onOfficeSDK;
use wpdb;
/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassRecordManagerReadForm
	extends WP_UnitTestCase
{
	private $_pRecordManagerReadForm = null;
	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pRecordManagerReadForm = $this->getMockBuilder(RecordManagerReadForm::class)
			->getMock();
	}
	/**
	 *
	 */

	public function testConstruct()
	{
		$pRecordManager = new RecordManagerReadForm();

		$pMainTable = $pRecordManager->getMainTable();
		$pIdColumnMain = $pRecordManager->getIdColumnMain();

		$this->assertEquals('oo_plugin_forms', $pMainTable);
		$this->assertEquals('form_id', $pIdColumnMain);
	}

	private function getBasicFieldsArray(int $formId, string $formType): array
	{
		$fields = [
			[
				'form_fieldconfig_id' => '1',
				'form_id' => $formId,
				'order' => '1',
				'fieldname' => 'Test 1',
				'fieldlabel' => 'Field label 1',
				'module' => onOfficeSDK::MODULE_ADDRESS,
				'individual_fieldname' => '0',
				'availableOptions' => '0',
				'required' => '1',
			],
			[
				'form_fieldconfig_id' => '1',
				'form_id' => $formId,
				'order' => '1',
				'fieldname' => 'Test 3',
				'fieldlabel' => 'Field label 3',
				'module' => onOfficeSDK::MODULE_SEARCHCRITERIA,
				'individual_fieldname' => '0',
				'availableOptions' => '0',
				'required' => '1',
			],
			[
				'form_fieldconfig_id' => '1',
				'form_id' => $formId,
				'order' => '1',
				'fieldname' => 'Test 3',
				'fieldlabel' => 'Field label 3',
				'module' => onOfficeSDK::MODULE_ESTATE,
				'individual_fieldname' => '0',
				'availableOptions' => '0',
				'required' => '1',
			],
			[
				'form_fieldconfig_id' => '1',
				'form_id' => $formId,
				'order' => '1',
				'fieldname' => 'Test 4',
				'fieldlabel' => 'Field label 4',
				'module' => '',
				'individual_fieldname' => '0',
				'availableOptions' => '0',
				'required' => '1',
			],
		];

		return $fields;
	}

	private function getBaseRow(int $formId, string $formType): array
	{
		return [
			'form_id' => $formId,
			'name' => 'testForm' . $formId,
			'form_type' => $formType,
			'template' => 'testtemplate.php',
			'recipient' => 'test@my-onoffice.com',
			'subject' => 'A Subject',
			'createaddress' => '1',
			'limitresults' => '30',
			'checkduplicates' => '1',
			'pages' => '3',
			'captcha' => '1',
			'newsletter' => '1',
			'availableOptions' => '1',
			'show_estate_context' => '0',
		];
	}

	public function testGetRecords()
	{
		$pFieldsForm = $this->_pRecordManagerReadForm->getRecords();
		$this->assertEquals(null,$pFieldsForm);
	}

	public function testGetRecordsSortedAlphabetically()
	{
		$pFieldsFormSortAlphabe = $this->_pRecordManagerReadForm->getRecordsSortedAlphabetically();
		$this->assertEquals([],$pFieldsFormSortAlphabe);
	}

	public function testGetAllRecords()
	{
		$this->_pRecordManagerReadForm->method('getAllRecords')->will($this->returnValueMap([
			['testForm1', $this->getBaseRow(1, Form::TYPE_CONTACT)]
		]));
		$pFieldsForm = $this->_pRecordManagerReadForm->getAllRecords();

		$this->assertEquals(null, $pFieldsForm);
	}

	public function testGetRowByName()
	{
		$this->_pRecordManagerReadForm->method('getRowByName')->will($this->returnValueMap([
			['testForm1', $this->getBaseRow(1, Form::TYPE_CONTACT)]
		]));
		$pFieldsForm = $this->_pRecordManagerReadForm->getRowByName('testForm1');
		$this->assertEquals(14, count($pFieldsForm));
	}

	public function testReadFieldconfigByFormId()
	{
		$this->_pRecordManagerReadForm->method('readFieldconfigByFormId')->will($this->returnValueMap([
			[1, $this->getBasicFieldsArray(1, Form::TYPE_CONTACT)]
		]));
		$pFieldsForm = $this->_pRecordManagerReadForm->readFieldconfigByFormId(1);
		$this->assertEquals(4, count($pFieldsForm));
	}

	public function testGetNameByFormId()
	{
		$this->_pRecordManagerReadForm->method('getNameByFormId')->will($this->returnValueMap([
			[1, $this->getBasicFieldsArray(1, Form::TYPE_CONTACT)]
		]));
		$pFieldsForm = $this->_pRecordManagerReadForm->getNameByFormId(1);
		$this->assertEquals(4, count($pFieldsForm));
	}

	public function testReadFieldsByFormId()
	{
		$this->_pRecordManagerReadForm->method('readFieldsByFormId')->will($this->returnValueMap([
			[1, $this->getBasicFieldsArray(1, Form::TYPE_CONTACT)]
		]));
		$pFieldsForm = $this->_pRecordManagerReadForm->readFieldsByFormId(1);
		$this->assertEquals(4, count($pFieldsForm));
	}
}
