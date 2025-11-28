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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationContact;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormPostConfigurationTest;
use onOffice\WPlugin\Form\FormPostConfigurationDefault;
use onOffice\WPlugin\Form\FormPostContactConfigurationTest;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\FormPostContact;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPQueryWrapper;
use onOffice\WPlugin\WP\WPWrapper;
use WP_UnitTestCase;
use function DI\autowire;
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

	/** @var SDKWrapperMocker */
	private $_pSDKWrapperMocker = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;

	/** @var Container */
	private $_pContainer = null;

	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSDKWrapperMocker = new SDKWrapperMocker();
		$pLogger = $this->getMockBuilder(Logger::class)->getMock();
		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->onlyMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria',  'addFieldsFormFrontend'])
			->setConstructorArgs([new Container])
			->getMock();
		add_option('onoffice-settings-honeypot', true);

		$pWPQueryWrapper = $this->getMockBuilder(WPQueryWrapper::class)
			->getMock();
		$this->_pContainer = new Container;
		$this->_pContainer->set(SDKWrapper::class, $this->_pSDKWrapperMocker);
		$this->_pContainer->set(Form\FormPostConfiguration::class, autowire(FormPostConfigurationTest::class));
		$this->_pContainer->set(Form\FormPostConfiguration::class, autowire(FormPostConfigurationDefault::class));
		$this->_pContainer->set(Form\FormPostContactConfiguration::class, autowire(FormPostContactConfigurationTest::class));
		$this->_pContainer->set(WPQueryWrapper::class, $pWPQueryWrapper);
		$this->_pContainer->set(Logger::class, $pLogger);
		$this->_pContainer->set(FieldsCollectionBuilderShort::class, $this->_pFieldsCollectionBuilderShort);
		$this->_pContainer->get(Form\FormPostContactConfiguration::class)->setNewsletterAccepted(true);


		$this->_pFieldsCollectionBuilderShort->method('addFieldsSearchCriteria')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {

				$pField1 = new Field('asd', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));

		$this->_pFieldsCollectionBuilderShort->method('addFieldsAddressEstate')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pFieldVorname = new Field('Vorname', onOfficeSDK::MODULE_ADDRESS);
				$pFieldVorname->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldVorname);

				$pFieldName = new Field('Name', onOfficeSDK::MODULE_ADDRESS);
				$pFieldName->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldName);

				$pFieldEmail = new Field('Email', onOfficeSDK::MODULE_ADDRESS);
				$pFieldEmail->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldEmail);

				$pFieldPlz = new Field('Plz', onOfficeSDK::MODULE_ADDRESS);
				$pFieldPlz->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldPlz);

				$pFieldOrt = new Field('Ort', onOfficeSDK::MODULE_ADDRESS);
				$pFieldOrt->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldOrt);

				$pFieldStrasse = new Field('Strasse', onOfficeSDK::MODULE_ADDRESS);
				$pFieldStrasse->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldStrasse);

				$FieldTelefon1 = new Field('Telefon1', onOfficeSDK::MODULE_ADDRESS);
				$FieldTelefon1->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($FieldTelefon1);

				$pFieldArtDaten = new Field('ArtDaten', onOfficeSDK::MODULE_ADDRESS);
				$pFieldArtDaten->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldArtDaten);

				$pFieldAgbAkzeptiert = new Field('AGB_akzeptiert', onOfficeSDK::MODULE_ADDRESS);
				$pFieldAgbAkzeptiert->setType(FieldTypes::FIELD_TYPE_BOOLEAN);
				$pFieldsCollection->addField($pFieldAgbAkzeptiert);

				$pFieldId = new Field('ID', onOfficeSDK::MODULE_ADDRESS);
				$pFieldId->setType(FieldTypes::FIELD_TYPE_INTEGER);
				$pFieldsCollection->addField($pFieldId);

				$pFieldIdEstate = new Field('Id', onOfficeSDK::MODULE_ESTATE);
				$pFieldIdEstate->setType(FieldTypes::FIELD_TYPE_INTEGER);
				$pFieldsCollection->addField($pFieldIdEstate);

				$pFieldAnrede = new Field('Anrede', onOfficeSDK::MODULE_ADDRESS);
				$pFieldAnrede->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
				$pFieldsCollection->addField($pFieldAnrede);

				$pFieldNewsletter = new Field('newsletter', onOfficeSDK::MODULE_ADDRESS);
				$pFieldNewsletter->setType(FieldTypes::FIELD_TYPE_BOOLEAN);
				$pFieldsCollection->addField($pFieldNewsletter);

				$pFieldMessage = new Field('message', '');
				$pFieldMessage->setType(FieldTypes::FIELD_TYPE_TEXT);
				$pFieldsCollection->addField($pFieldMessage);

				return $this->_pFieldsCollectionBuilderShort;
			}));

			$this->_pFieldsCollectionBuilderShort->method('addFieldsFormFrontend')
					->with($this->anything())
					->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('region_plz', onOfficeSDK::MODULE_SEARCHCRITERIA);
				$pField1->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));

		$this->_pFormPostContact = $this->_pContainer->get(FormPostContact::class);

		$this->configureSDKWrapperForContactAddress();
		$this->configureSDKWrapperForContactAddressWithNewsLetter();
		$this->configureSDKWrapperForCreateAddress();
		$this->configureSDKWrapperForCreateAddressWithDuplicateCheck();
		$this->configureSDKWrapperForFieldsAddressEstate();
		$this->configureSDKWrapperForCreateAgentsLog();
		$this->configureSDKWrapperForCreateTask();
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
				'AGB_akzeptiert' => true,
				'newsletter_aktiv' => false
			],
			'estateid' => 1337,
			'message' => null,
			'subject' => '¡A new Contact!'.' '.FormPostContact::PORTALFILTER_IDENTIFIER,
			'referrer' => '/test/page',
			'formtype' => 'contact',
			'estatedata' => ["objekttitel", "ort", "plz", "land"],
			'estateurl' => 'http://example.org',
			'recipient' => 'test@my-onoffice.com',
		];

		$responseJson = file_get_contents(__DIR__.'/resources/ApiResponseDoContactaddress.json');
		$response = json_decode($responseJson, true);

		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_DO, 'contactaddress', '', $parameters, null, $response);
	}

	private function configureSDKWrapperForContactAddressWithNewsLetter()
	{
		$parameters = [
			'addressdata' => [
				'Vorname' => 'John',
				'Name' => 'Doe',
				'Email' => 'john.doe@my-onoffice.com',
				'Plz' => '52068',
				'Ort' => 'Aachen',
				'Telefon1' => '0815/2345677',
				'AGB_akzeptiert' => true,
				'newsletter_aktiv' => true
			],
			'estateid' => 1337,
			'message' => '',
			'subject' => '¡A new Contact!'.' '.FormPostContact::PORTALFILTER_IDENTIFIER,
			'referrer' => '/test/page',
			'formtype' => 'contact',
			'estatedata' => ["objekttitel", "ort", "plz", "land"],
			'estateurl' => 'http://example.org',
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
			'AGB_akzeptiert' => true,
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
			'AGB_akzeptiert' => true,
			'checkDuplicate' => false,
			'ArtDaten' => ['Owner', 'Customer'],
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
	public function configureSDKWrapperForCreateTask()
	{
		$parameters = [
			'data' => [
				'Prio' => 3,
				'Verantwortung' => 'Tobias',
				'Art' => 1,
				'Status' => 1,
				'Bearbeiter' => 'Tobias',
				'Betreff' => 'Task subject',
				'Aufgabe' => 'Task description'
			],
			'relatedAddressId' => 320,
		];

		$responseJson = file_get_contents(__DIR__.'/resources/ApiResponseCreateTask.json');
		$response = json_decode($responseJson, true);

		$this->_pSDKWrapperMocker->addResponseByParameters
		(onOfficeSDK::ACTION_ID_CREATE, 'task', '', $parameters, null, $response);
	}

	/**
	 *
	 */
	public function testCreateTask()
	{
		$_POST = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'Email' => 'john.doe@my-onoffice.com',
			'Plz' => '52068',
			'Ort' => 'Aachen',
			'Telefon1' => '0815/2345677',
			'AGB_akzeptiert' => 'y',
			'newsletter' => 'y',
			'Id' => '1337',
			'Anrede' => '',
			'tmpField' => 'content',
			'onoffice_nonce' => wp_create_nonce('onoffice_form_contactForm'),
		];

		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$pDataFormConfiguration->setEnableCreateTask(true);
		$pDataFormConfiguration->setTaskResponsibility('Tobias');
		$pDataFormConfiguration->setTaskProcessor('Tobias');
		$pDataFormConfiguration->setTaskPriority(3);
		$pDataFormConfiguration->setTaskType(1);
		$pDataFormConfiguration->setTaskSubject('Task subject');
		$pDataFormConfiguration->setTaskDescription('Task description');
		$pDataFormConfiguration->setTaskStatus(1);
		$this->_pFormPostContact->initialCheck($pDataFormConfiguration, 2);

		$pFormData = $this->_pFormPostContact->getFormDataInstance('contactForm', 2);
		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
	}


	/**
	 *
	 */

	private function configureSDKWrapperForCreateAgentsLog()
	{
		$parameters = [
			'addressids' => [320],
			'actionkind' => 'ActionKind',
			'actiontype' => 'ActionType',
			'origincontact' => 'originContact',
			'features' => ['Characteristic1', 'Characteristic2'],
			'note' => 'comment'
		];

		$responseJson = file_get_contents
			(__DIR__.'/resources/FormPostContact/ApiResponseCreateAgentLog.json');
		$response = json_decode($responseJson, true);

		$this->_pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_CREATE, 'agentslog', '', $parameters, null, $response);
	}

	/**
	 *
	 */

	public function testMissingFields()
	{
		$_POST = [
        	'onoffice_nonce' => wp_create_nonce('onoffice_form_contactForm'),
		];
    

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
		$_POST = $this->getPostVariables();

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
		$_POST = $this->getPostVariables();

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
		$_POST = $this->getPostVariables();

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
		$_POST = $this->getPostVariables();

		$this->_pContainer->get(Form\FormPostContactConfiguration::class)->setNewsletterAccepted(true);
		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$pDataFormConfiguration->setCreateAddress(true);
		$this->_pFormPostContact->initialCheck($pDataFormConfiguration, 2);

		$pFormData = $this->_pFormPostContact->getFormDataInstance('contactForm', 2);
		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
	}


	/**
	 *
	 */

	public function testPostHoneypotMessageEmpty()
	{
		
		$_POST = $this->getPostVariables();

		$_POST = [
			'message' => 'content2',
			'onoffice_nonce' => wp_create_nonce('onoffice_form_contactForm'),
		];

		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$pDataFormConfiguration->setCreateAddress(true);
		$this->_pFormPostContact->initialCheck($pDataFormConfiguration, 2);

		$pFormData = $this->_pFormPostContact->getFormDataInstance('contactForm', 2);
		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
	}

	/**
	 *
	 */

	public function testFormHoneypot()
	{
		$_POST = $this->getPostVariables();

		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$pDataFormConfiguration->setCreateAddress(true);
		$this->_pFormPostContact->initialCheck($pDataFormConfiguration, 2);

		$pFormData = $this->_pFormPostContact->getFormDataInstance('contactForm', 2);
		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
	}


	/**
	 *
	 */

	public function testFormHoneypotEmpty()
	{
		$_POST = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'Email' => 'john.doe@my-onoffice.com',
			'Plz' => '52068',
			'Ort' => 'Aachen',
			'Telefon1' => '0815/2345677',
			'AGB_akzeptiert' => 'y',
			'newsletter' => 'y',
			'Id' => '1337',
			'Anrede' => '',
			'tmpField' => 'content',
			'onoffice_nonce' => wp_create_nonce('onoffice_form_contactForm'),
		];

		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$pDataFormConfiguration->setCreateAddress(true);
		$this->_pFormPostContact->initialCheck($pDataFormConfiguration, 2);

		$pFormData = $this->_pFormPostContact->getFormDataInstance('contactForm', 2);
		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
	}

	/**
	 *
	 */

	public function testFormWhenHoneypotInactive()
	{
		update_option('onoffice-settings-honeypot', false);
		$_POST = $this->getPostVariables();

		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$pDataFormConfiguration->setCreateAddress(true);
		$this->_pFormPostContact->initialCheck($pDataFormConfiguration, 2);

		$pFormData = $this->_pFormPostContact->getFormDataInstance('contactForm', 2);
		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
	}


	/**
	 *
	 */

	public function testCreateAgentsLog()
	{
		$_POST = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'Email' => 'john.doe@my-onoffice.com',
			'Plz' => '52068',
			'Ort' => 'Aachen',
			'Telefon1' => '0815/2345677',
			'AGB_akzeptiert' => 'y',
			'newsletter' => 'y',
			'Id' => '1337',
			'Anrede' => '',
			'tmpField' => 'content',
			'onoffice_nonce' => wp_create_nonce('onoffice_form_contactForm'),
		];

		$pDataFormConfiguration = $this->getNewDataFormConfiguration();
		$pDataFormConfiguration->setWriteActivity(true);
		$pDataFormConfiguration->setActionKind('ActionKind');
		$pDataFormConfiguration->setActionType('ActionType');
		$pDataFormConfiguration->setCharacteristic('Characteristic1,Characteristic2');
		$pDataFormConfiguration->setOriginContact('originContact');
		$pDataFormConfiguration->setRemark('comment');
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
		$pDataFormConfiguration->addInput('Anrede', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Vorname', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Name', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Email', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Strasse', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Plz', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Ort', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Telefon1', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('AGB_akzeptiert', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('newsletter', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('message', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->setRecipient('test@my-onoffice.com');
		$pDataFormConfiguration->setCreateAddress(true);
		$pDataFormConfiguration->setFormType(Form::TYPE_CONTACT);
		$pDataFormConfiguration->setFormName('contactForm');
		$pDataFormConfiguration->setSubject('¡A new Contact!');
		$pDataFormConfiguration->setRequiredFields(['Vorname', 'Name', 'Email']);
		$pDataFormConfiguration->setContactTypeField(['Owner', 'Customer']);
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
			'AGB_akzeptiert' => 'y',
			'newsletter' => 'y',
			'Id' => '1337',
			'Anrede' => '',
			'message' => 'content1',
			'tmpField' => 'content2',
			'onoffice_nonce' => wp_create_nonce('onoffice_form_contactForm'),
		];
	}
}
