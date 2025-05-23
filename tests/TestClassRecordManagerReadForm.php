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

use onOffice\WPlugin\Record\RecordManagerReadForm;
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

	/**
	 * @param int $formId
	 *
	 * @return array
	 */
	private function getContactTypesArray(int $formId): array
	{
		return [
			'form_id' => $formId,
			'contact_type' => 'Owner'
		];
	}

	/**
	 * @param int $formId
	 *
	 * @return array
	 */
	private function getActivityConfigRow(int $formId): array
	{
		return [
			'form_activityconfig_id' => 1,
			'form_id' => $formId,
			'write_activity' => '1',
			'action_kind' => 'action_kind',
			'action_type' => 'action_type',
			'characteristic' => 'characteristic1,characteristic2',
			'remark' => 'comment'
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

	/**
	 * 
	 */
	public function testCheckSameName()
	{
		$configOutput = ['count' => 0];

		$pWPDB = $this->getMockBuilder(wpdb::class)
			->disableOriginalConstructor(['testUser', 'testPassword', 'testDB', 'testHost'])
			->setMethods(['get_row'])
			->getMock();
		$pWPDB->prefix = 'testPrefix';
		$pWPDB->expects($this->once())
			->method('get_row')
			->willReturnOnConsecutiveCalls($configOutput);
		$pRecordManagerReadForm = $this->getMockBuilder(RecordManagerReadForm::class)
			->setMethods(['getWpdb'])
			->getMock();

		$pRecordManagerReadForm->method('getWpdb')->will($this->returnValue($pWPDB));
		$pData = $pRecordManagerReadForm->checkSameName('testForm1');

		$this->assertTrue($pData);
	}

	/**
	 *
	 */
	public function testReadContactTypesByFormId()
	{
		$this->_pRecordManagerReadForm->method('readContactTypesByFormId')->will($this->returnValueMap([
			[1, $this->getContactTypesArray(1)]
		]));
		$pFieldsForm = $this->_pRecordManagerReadForm->readContactTypesByFormId(1);
		$this->assertEquals(2, count($pFieldsForm));
	}

	/**
	 * @param int $formId
	 *
	 * @return array
	 */
	private function getFormTaskConfigByFormId(int $formId): array
	{
		return [
			'form_taskconfig_id ' => 1,
			'form_id' => $formId,
			'enable_create_task' => true,
			'responsibility' => 'Tobias',
			'processor' => 'Tobias',
			'type' => 1,
			'priority' => 1,
			'subject' => 'Test subject',
			'description' => 'Test description',
			'status' => 1,
		];
	}

	/**
	 *
	 */
	public function testReadActivityConfigByFormId()
	{
		$this->_pRecordManagerReadForm->method('readActivityConfigByFormId')->will($this->returnValueMap([
			[1, $this->getActivityConfigRow(1)]
		]));
		$pActivityConfig = $this->_pRecordManagerReadForm->readActivityConfigByFormId(1);
		$this->assertEquals(7, count($pActivityConfig));
	}

	/**
	 * @return void
	 */
	public function testReadFormTaskConfigByFormId()
	{
		$this->_pRecordManagerReadForm->method('readFormTaskConfigByFormId')->will($this->returnValueMap([
			[1, $this->getFormTaskConfigByFormId(1)]
		]));
		$pActivityConfig = $this->_pRecordManagerReadForm->readFormTaskConfigByFormId(1);
		$this->assertEquals(10, count($pActivityConfig));
	}
}
