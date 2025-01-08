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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 * @covers onOffice\WPlugin\FormData
 *
 */

class TestClassFormData
	extends WP_UnitTestCase
{
	/** @var FormData */
	private $_pFormData = null;

	/** @var array */
	private $_values = [
		'testInput1' => 'Test',
		'testInput3__von' => 3.1415,
		'testInput3__bis' => 15,
		'Telefon1' => '0815 8374374',
		'Email' => 'test-wp@my-onoffice.com',
		'Telefax1' => '0815 83748937',
		'testInput4' => 'Another Test',
		'testInput5' => 8237383.99,
		'testInput6' => 47,
		'testInput7' => ['test1', 'test2'],
		'testInput8__von' => 12,
		'testInput8__bis' => 55,
		'testInput9' => 'wood',
		'regionaler_zusatz' => 'AachenStadt',
		'testBoolInput' => 'y',
		'message' => 'abc'
	];


	/**
	 *
	 */

	public function set_up()
	{
		parent::set_up();

		$pDataFormConfiguration = new DataFormConfiguration();
		$pDataFormConfiguration->setFormName('Testform');
		$pDataFormConfiguration->setInputs([
			'testInput1' => onOfficeSDK::MODULE_ADDRESS,
			'testInput2' => onOfficeSDK::MODULE_ADDRESS,
			'testInput3' => onOfficeSDK::MODULE_ADDRESS,
			'Telefon1' => onOfficeSDK::MODULE_ADDRESS,
			'Email' => onOfficeSDK::MODULE_ADDRESS,
			'Telefax1' => onOfficeSDK::MODULE_ADDRESS,
			'testInput4' => onOfficeSDK::MODULE_ESTATE,
			'testInput5' => onOfficeSDK::MODULE_ESTATE,
			'testInput6' => onOfficeSDK::MODULE_ESTATE,
			'testInput7' => onOfficeSDK::MODULE_ESTATE,
			'testInput8' => onOfficeSDK::MODULE_SEARCHCRITERIA,
			'testInput9' => onOfficeSDK::MODULE_SEARCHCRITERIA,
			'regionaler_zusatz' => onOfficeSDK::MODULE_SEARCHCRITERIA,
			'testBoolInput' => onOfficeSDK::MODULE_ADDRESS,
			'message' => ''
		]);

		$pDataFormConfiguration->setRequiredFields([
			'testInput1',
			'testInput2',
		]);

		$this->_pFormData = new FormData($pDataFormConfiguration, 1);
		$this->_pFormData->setValues($this->_values);
		$this->_pFormData->setRequiredFields($pDataFormConfiguration->getRequiredFields());
	}


	/**
	 *
	 */

	public function testGetMissingFields()
	{
		$this->_pFormData->setFormSent(true);

		$missingFieldsActual = $this->_pFormData->getMissingFields();
		$missingFieldsExpectation = [
			'testInput2',
		];

		$this->assertEquals($missingFieldsExpectation, $missingFieldsActual);
	}


	/**
	 *
	 */

	public function testGetRequiredFields()
	{
		$requiredFieldsActual = $this->_pFormData->getRequiredFields();
		$requiredFieldsExpect = [
			'testInput1',
			'testInput2',
		];
		$this->assertEquals($requiredFieldsExpect, $requiredFieldsActual);
	}


	/**
	 *
	 */

	public function testGetDataFormConfiguration()
	{
		$pDataFormConfiguration = $this->_pFormData->getDataFormConfiguration();
		$this->assertInstanceOf(DataFormConfiguration::class, $pDataFormConfiguration);
	}


	/**
	 *
	 */

	public function testGetSetStatus()
	{
		$this->assertNull($this->_pFormData->getStatus());
		$this->_pFormData->setStatus('test');
		$this->assertEquals('test', $this->_pFormData->getStatus());
	}


	/**
	 *
	 */

	public function testGetFormNo()
	{
		$this->assertEquals(1, $this->_pFormData->getFormNo());
		$pFormData = new FormData(new DataFormConfiguration, 5);
		$this->assertEquals(5, $pFormData->getFormNo());
	}


	/**
	 *
	 */

	public function testGetAddressData()
	{
		$pFieldTelefon1 = new Field('Telefon1', onOfficeSDK::MODULE_ADDRESS);
		$pFieldTelefon1->setType(FieldTypes::FIELD_TYPE_VARCHAR);

		$pFieldEmail = new Field('Email', onOfficeSDK::MODULE_ADDRESS);
		$pFieldEmail->setType(FieldTypes::FIELD_TYPE_VARCHAR);

		$pFieldTelefax1 = new Field('Telefax1', onOfficeSDK::MODULE_ADDRESS);
		$pFieldTelefax1->setType(FieldTypes::FIELD_TYPE_VARCHAR);

		$pFieldTestInput1 = new Field('testInput1', onOfficeSDK::MODULE_ADDRESS);
		$pFieldTestInput1->setType(FieldTypes::FIELD_TYPE_VARCHAR);

		$pFieldTestInput3Von = new Field('testInput3__von', onOfficeSDK::MODULE_ADDRESS);
		$pFieldTestInput3Von->setType(FieldTypes::FIELD_TYPE_INTEGER);

		$pFieldTestInput3Bis = new Field('testInput3__bis', onOfficeSDK::MODULE_ADDRESS);
		$pFieldTestInput3Bis->setType(FieldTypes::FIELD_TYPE_INTEGER);

		$pFieldTestBool = new Field('testBoolInput', onOfficeSDK::MODULE_ADDRESS);
		$pFieldTestBool->setType(FieldTypes::FIELD_TYPE_BOOLEAN);

		$pFieldsCollection = new FieldsCollection;
		$pFieldsCollection->addField($pFieldTelefon1);
		$pFieldsCollection->addField($pFieldEmail);
		$pFieldsCollection->addField($pFieldTelefax1);
		$pFieldsCollection->addField($pFieldTestInput1);
		$pFieldsCollection->addField($pFieldTestInput3Von);
		$pFieldsCollection->addField($pFieldTestInput3Bis);
		$pFieldsCollection->addField($pFieldTestBool);

		$addressData = $this->_pFormData->getAddressData($pFieldsCollection);
		$expectation = [
			'testInput1' => 'Test',
			'testInput3__von' => 3.1415,
			'testInput3__bis' => 15,
			'Telefon1' => '0815 8374374',
			'Email' => 'test-wp@my-onoffice.com',
			'Telefax1' => '0815 83748937',
			'testBoolInput' => 1
		];

		$this->assertEquals($expectation, $addressData);
	}


	/**
	 *
	 */

	public function testGetSetFormSent()
	{
		$this->assertFalse($this->_pFormData->getFormSent());
		$this->_pFormData->setFormSent(true);
		$this->assertTrue($this->_pFormData->getFormSent());
	}


	/**
	 *
	 */

	public function testGetSetFormType()
	{
		$this->_pFormData->setFormtype(Form::TYPE_CONTACT);
		$this->assertEquals(Form::TYPE_CONTACT, $this->_pFormData->getFormtype());

		$this->_pFormData->setFormtype(Form::TYPE_INTEREST);
		$this->assertEquals(Form::TYPE_INTEREST, $this->_pFormData->getFormtype());
	}


	/**
	 *
	 */

	public function testGetSearchcriteriaData()
	{
		$searchcriteriaData = $this->_pFormData->getSearchcriteriaData();

		$expectation = [
			'testInput8__von' => 12,
			'testInput8__bis' => 55,
			'testInput9' => 'wood',
			'regionaler_zusatz' => 'AachenStadt',
		];

		$this->assertEquals($expectation, $searchcriteriaData);
	}


	/**
	 *
	 */

	public function testGetValues()
	{
		$this->assertEquals($this->_values, $this->_pFormData->getValues());
	}


	/**
	 *
	 */

	public function testGetSetResponseFieldsValues()
	{
		$values = [
			'test1',
			'test2',
		];

		$this->assertEquals([], $this->_pFormData->getResponseFieldsValues());
		$this->_pFormData->setResponseFieldsValues($values);
		$this->assertEquals($values, $this->_pFormData->getResponseFieldsValues());
	}
}
