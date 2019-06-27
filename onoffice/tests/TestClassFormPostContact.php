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

use DI\Container;
use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationContact;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormAddressCreator;
use onOffice\WPlugin\Form\FormPostConfigurationTest;
use onOffice\WPlugin\Form\FormPostContactConfigurationTest;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\FormPostContact;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPQueryWrapper;
use WP_UnitTestCase;
use function json_decode;

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
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSDKWrapperMocker = new SDKWrapperMocker();
		$pLogger = $this->getMockBuilder(Logger::class)->getMock();

		$this->_pFormPostConfiguration = new FormPostConfigurationTest($pLogger);
		$pWPQueryWrapper = $this->getMockBuilder(WPQueryWrapper::class)
			->getMock();
		$pContainer = new Container;
		$pContainer->set(SDKWrapper::class, $this->_pSDKWrapperMocker);
		$pFormAddressCreator = new FormAddressCreator($this->_pSDKWrapperMocker,
			new FieldsCollectionBuilderShort($pContainer));
		$this->_pFormPostContactConfiguration = new FormPostContactConfigurationTest
			($this->_pSDKWrapperMocker, $pWPQueryWrapper, $pFormAddressCreator);
		$this->_pFormPostContactConfiguration->setReferrer('/test/page');
		$this->_pFormPostContact = new FormPostContact($this->_pFormPostConfiguration,
			$this->_pFormPostContactConfiguration);

		$this->configureSDKWrapperForContactAddress();
		$this->configureSDKWrapperForCreateAddress();
		$this->configureSDKWrapperForCreateAddressWithDuplicateCheck();
		$this->configureSDKWrapperForFieldsAddressEstate();
	}


	/**
	 *
	 */

	private function configureSDKWrapperForContactAddress()
	{
		$parameters = [
			'addressdata' => [
				'Vorname' => 'John',
				'Name' => 'Doe',
				'Email' => 'john.doe@my-onoffice.com',
				'Plz' => '52068',
				'Ort' => 'Aachen',
				'Telefon1' => '0815/2345677',
				'AGB_akzeptiert' => '1',
			],
			'estateid' => '1337',
			'message' => null,
			'subject' => '¡A new Contact!',
			'referrer' => '/test/page',
			'formtype' => 'contact',
			'recipient' => 'test@my-onoffice.com',
		];

		$responseJson = file_get_contents(__DIR__.'/resources/ApiResponseDoContactaddress.json');
		$response = json_decode($responseJson, true);

		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_DO, 'contactaddress', '', $parameters, null, $response);
	}


	/**
	 *
	 */

	private function configureSDKWrapperForFieldsAddressEstate()
	{
		$fieldParameters = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'language' => 'ENG',
			'modules' => ['address', 'estate'],
		];

		$responseGetFields = json_decode
			(file_get_contents(__DIR__.'/resources/ApiResponseGetFields.json'), true);
		/* @var $pSDKWrapperMocker SDKWrapperMocker */
		$this->_pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
			$fieldParameters, null, $responseGetFields);
	}


	/**
	 *
	 */

	private function configureSDKWrapperForCreateAddress()
	{
		$parameters = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'email' => 'john.doe@my-onoffice.com',
			'Plz' => '52068',
			'Ort' => 'Aachen',
			'phone' => '0815/2345677',
			'AGB_akzeptiert' => '1',
			'checkDuplicate' => true,
		];

		$responseJson = file_get_contents
			(__DIR__.'/resources/FormPostContact/ApiResponseCreateAddress.json');
		$response = json_decode($responseJson, true);

		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_CREATE, 'address', '', $parameters, null, $response);
	}


	/**
	 *
	 */

	private function configureSDKWrapperForCreateAddressWithDuplicateCheck()
	{
		$parameters = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'email' => 'john.doe@my-onoffice.com',
			'Plz' => '52068',
			'Ort' => 'Aachen',
			'phone' => '0815/2345677',
			'AGB_akzeptiert' => '1',
			'checkDuplicate' => false,
		];

		$responseJson = file_get_contents
			(__DIR__.'/resources/FormPostContact/ApiResponseCreateAddress.json');
		$response = json_decode($responseJson, true);

		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_CREATE, 'address', '', $parameters, null, $response);
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
	 */

	public function testSendWithNewAddressAndNewsletter()
	{
		$postVariables = $this->getPostVariables();

		$this->_pFormPostConfiguration->setPostVariables($postVariables);
		$this->_pFormPostContactConfiguration->setNewsletterAccepted(true);
		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$pDataFormConfiguration->setCreateAddress(true);
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
		$pDataFormConfiguration->addInput('AGB_akzeptiert', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->setRecipient('test@my-onoffice.com');
		$pDataFormConfiguration->setCreateAddress(false);
		$pDataFormConfiguration->setFormType(Form::TYPE_CONTACT);
		$pDataFormConfiguration->setFormName('contactForm');
		$pDataFormConfiguration->setSubject('¡A new Contact!');
		$pDataFormConfiguration->setRequiredFields(['Vorname', 'Name', 'Email']);
		$this->mockNewsletterCall();

		return $pDataFormConfiguration;
	}


	/**
	 *
	 */

	private function mockNewsletterCall()
	{
		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:do',
			'resourceid' => '320',
			'resourcetype' => 'registerNewsletter',
			'cacheable' => false,
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => null,
				],
				'records' => []
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		$this->_pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_DO,
			'registerNewsletter', '320', ['register' => true], null, $response);
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
			'AGB_akzeptiert' => '1',
			'Id' => '1337',
		];
	}
}
