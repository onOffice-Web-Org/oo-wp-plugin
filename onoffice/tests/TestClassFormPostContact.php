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

use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationContact;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormPostConfigurationTest;
use onOffice\WPlugin\Form\FormPostContactConfigurationTest;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\FormPostContact;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFormPostContact
	extends WP_UnitTestCase
{
	/** @var FormPostContact */
	private $_pFormPostContact = null;

	/** @var FormPostConfigurationTest */
	private $_pFormPostConfiguration = null;

	/** @var FormPostContactConfigurationTest */
	private $_pFormPostContactConfiguration = null;

	/** @var SDKWrapperMocker */
	private $_pSDKWrapperMocker = null;


	/**
	 *
	 */

	public function setUp()
	{
		$this->_pSDKWrapperMocker = new SDKWrapperMocker();

		$this->_pFormPostConfiguration = new FormPostConfigurationTest();
		$this->_pFormPostContactConfiguration = new FormPostContactConfigurationTest
			($this->_pSDKWrapperMocker);
		$this->_pFormPostContactConfiguration->setReferrer('/test/page');
		$this->_pFormPostConfiguration->setSDKWrapper($this->_pSDKWrapperMocker);
		$this->_pFormPostContact = new FormPostContact($this->_pFormPostConfiguration,
			$this->_pFormPostContactConfiguration);

		$module = onOfficeSDK::MODULE_ADDRESS;
		$this->_pFormPostConfiguration->addInputType($module, 'Vorname', FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pFormPostConfiguration->addInputType($module, 'Name', FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pFormPostConfiguration->addInputType($module, 'Email', FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pFormPostConfiguration->addInputType($module, 'Plz', FieldTypes::FIELD_TYPE_INTEGER);
		$this->_pFormPostConfiguration->addInputType($module, 'Ort', FieldTypes::FIELD_TYPE_VARCHAR);
		$this->_pFormPostConfiguration->addInputType($module, 'Telefon1', FieldTypes::FIELD_TYPE_VARCHAR);

		$this->configureSDKWrapperForContactAddress();
		$this->configureSDKWrapperForCreateAddress();
		$this->configureSDKWrapperForCreateAddressWithDuplicateCheck();
	}


	/**
	 *
	 */

	private function configureSDKWrapperForContactAddress()
	{
		$actionId = onOfficeSDK::ACTION_ID_DO;
		$resourceType = 'contactaddress';
		$parameters = [
			'addressdata' => [
				'Vorname' => 'John',
				'Name' => 'Doe',
				'Email' => 'john.doe@my-onoffice.com',
				'Plz' => '52068',
				'Ort' => 'Aachen',
				'Telefon1' => '0815/2345677',
			],

			'estateid' => null,
			'message' => null,
			'subject' => '¡A new Contact!',
			'referrer' => '/test/page',
			'formtype' => 'contact',
			'recipient' => 'test@my-onoffice.com',
		];

		$responseJson = file_get_contents(__DIR__.'/resources/ApiResponseDoContactaddress.json');
		$response = json_decode($responseJson, true);

		$this->_pSDKWrapperMocker->addResponseByParameters
			($actionId, $resourceType, '', $parameters, null, $response);
	}


	/**
	 *
	 */

	private function configureSDKWrapperForCreateAddress()
	{
		$actionId = onOfficeSDK::ACTION_ID_CREATE;
		$resourceType = 'address';
		$parameters = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'email' => 'john.doe@my-onoffice.com',
			'Plz' => '52068',
			'Ort' => 'Aachen',
			'phone' => '0815/2345677',
			'checkDuplicate' => true,
		];

		$responseJson = file_get_contents(__DIR__
			.'/resources/FormPostContact/ApiResponseCreateAddress.json');
		$response = json_decode($responseJson, true);

		$this->_pSDKWrapperMocker->addResponseByParameters
			($actionId, $resourceType, '', $parameters, null, $response);
	}


	/**
	 *
	 */

	private function configureSDKWrapperForCreateAddressWithDuplicateCheck()
	{
		$actionId = onOfficeSDK::ACTION_ID_CREATE;
		$resourceType = 'address';
		$parameters = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'email' => 'john.doe@my-onoffice.com',
			'Plz' => '52068',
			'Ort' => 'Aachen',
			'phone' => '0815/2345677',
			'checkDuplicate' => false,
		];

		$responseJson = file_get_contents(__DIR__
			.'/resources/FormPostContact/ApiResponseCreateAddress.json');
		$response = json_decode($responseJson, true);

		$this->_pSDKWrapperMocker->addResponseByParameters
			($actionId, $resourceType, '', $parameters, null, $response);
	}


	/**
	 *
	 */

	public function testMissingFields()
	{
		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$this->_pFormPostContact->initialCheck($pDataFormConfiguration, 2);

		$pFormData = $this->_pFormPostContact->getFormDataInstance('contactForm', 2);
		$this->assertEquals(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING, $pFormData->getStatus());
		$missingFieldsResult = $pFormData->getMissingFields();
		$missingFieldsExpectation = ['Vorname', 'Name', 'Email'];
		$this->assertEquals($missingFieldsExpectation, $missingFieldsResult);
	}


	/**
	 *
	 */

	public function testSendWithoutNewAddress()
	{
		$postVariables = $this->getPostVariables();

		$this->_pFormPostConfiguration->setPostVariables($postVariables);
		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$this->_pFormPostContact->initialCheck($pDataFormConfiguration, 2);

		$pFormData = $this->_pFormPostContact->getFormDataInstance('contactForm', 2);
		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
	}


	/**
	 *
	 */

	public function testSendWithNewAddress()
	{
		$postVariables = $this->getPostVariables();

		$this->_pFormPostConfiguration->setPostVariables($postVariables);
		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$pDataFormConfiguration->setCreateAddress(true);
		$this->_pFormPostContact->initialCheck($pDataFormConfiguration, 2);

		$pFormData = $this->_pFormPostContact->getFormDataInstance('contactForm', 2);
		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
	}


	/**
	 *
	 */

	public function testSendWithNewAddressAndCheckDuplicate()
	{
		$postVariables = $this->getPostVariables();

		$this->_pFormPostConfiguration->setPostVariables($postVariables);
		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$pDataFormConfiguration->setCreateAddress(true);
		$pDataFormConfiguration->setCheckDuplicateOnCreateAddress(true);
		$this->_pFormPostContact->initialCheck($pDataFormConfiguration, 2);

		$pFormData = $this->_pFormPostContact->getFormDataInstance('contactForm', 2);
		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
	}


	/**
	 *
	 * @return DataFormConfigurationContact
	 *
	 */

	private function getNewDataFormConfiguration(): DataFormConfigurationContact
	{
		$pDataFormConfiguration = new DataFormConfigurationContact();
		$pDataFormConfiguration->addInput('Vorname', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Name', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Email', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Strasse', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Plz', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Ort', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Telefon1', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->setRecipient('test@my-onoffice.com');
		$pDataFormConfiguration->setCreateAddress(false);
		$pDataFormConfiguration->setFormType(Form::TYPE_CONTACT);
		$pDataFormConfiguration->setFormName('contactForm');
		$pDataFormConfiguration->setSubject('¡A new Contact!');
		$pDataFormConfiguration->setRequiredFields(['Vorname', 'Name', 'Email']);

		return $pDataFormConfiguration;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getPostVariables(): array
	{
		return [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'Email' => 'john.doe@my-onoffice.com',
			'Plz' => '52068',
			'Ort' => 'Aachen',
			'Telefon1' => '0815/2345677',
		];
	}
}
