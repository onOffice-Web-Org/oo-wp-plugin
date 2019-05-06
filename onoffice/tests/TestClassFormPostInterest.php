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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationInterest;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormPostConfigurationTest;
use onOffice\WPlugin\Form\FormPostInterestConfigurationTest;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\FormPostInterest;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\Logger;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFormPostInterest
	extends WP_UnitTestCase
{
	/** @var FormPostConfigurationTest */
	private $_pFormPostConfiguration = null;

	/** @var FormPostInterestConfigurationTest */
	private $_pFormPostInterestConfiguration = null;

	/** @var FormPostInterest */
	private $_pFormPostInterest = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pFieldnames = $this->getMockBuilder(Fieldnames::class)
			->setConstructorArgs([new FieldsCollection()])
			->getMock();
		$pLogger = $this->getMock(Logger::class);

		$jsonFile = ONOFFICE_PLUGIN_DIR.'/tests/resources/FormPostSearchCriteriaFields.json';
		$jsonString = file_get_contents($jsonFile);
		$searchCriteriaFields = json_decode($jsonString, true);

		$pFieldnames->method('getFieldList')->with(onOfficeSDK::MODULE_SEARCHCRITERIA)
			->will($this->returnValue($searchCriteriaFields));

		$searchCriteriaFieldsResponseENG = file_get_contents
			(__DIR__.'/resources/ApiResponseGetSearchcriteriaFieldsENG.json');
		$responseArrayENG = json_decode($searchCriteriaFieldsResponseENG, true);
		$pSDKWrapperMocker = new SDKWrapperMocker();
		$pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields', '', [
				'language' => 'ENG',
				'additionalTranslations' => true,
			], null, $responseArrayENG);
		$fieldsResponse = file_get_contents
			(__DIR__.'/resources/ApiResponseGetFields.json');
		$responseArrayFields = json_decode($fieldsResponse, true);
		$pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_GET, 'fields', '', [
			'labels' => true,
					'showContent' => true,
					'showTable' => true,
					'language' => 'ENG',
					'modules' => ['address', 'estate'],
			], null, $responseArrayFields);

		$this->_pFormPostConfiguration = new FormPostConfigurationTest($pFieldnames, $pLogger);
		$this->_pFormPostConfiguration->setSDKWrapper($pSDKWrapperMocker);

		$this->_pFormPostInterestConfiguration = new FormPostInterestConfigurationTest();
		$this->_pFormPostInterestConfiguration->setSDKWrapper($pSDKWrapperMocker);

		$this->_pFormPostInterest = new FormPostInterest($this->_pFormPostConfiguration,
			$this->_pFormPostInterestConfiguration);
	}


	/**
	 *
	 */

	public function testInitialCheck()
	{
		$pConfig = $this->getNewDataFormConfigurationInterest();
		$postVariables = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'Email' => 'john@doemail.com',
			'vermarktungsart' => 'kauf',
			'kaufpreis__von' => '200000.00',
			'kaufpreis__bis' => '800000.00',
		];

		$this->_pFormPostConfiguration->setPostVariables($postVariables);
		$this->_pFormPostInterestConfiguration->setPostValues($postVariables);
		$this->addApiResponseCreateAddress(true);
		$this->addApiResponseCreateSearchCriteria(true);
		$this->addApiResponseSendMail(true);
		$this->_pFormPostInterest->initialCheck($pConfig, 2);
		$pFormData = $this->_pFormPostInterest->getFormDataInstance('interestform', 2);

		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
	}


	/**
	 *
	 */

	public function testUnsuccessful()
	{
		$pConfig = $this->getNewDataFormConfigurationInterest();
		$postVariables = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'Email' => 'john@doemail.com',
			'vermarktungsart' => 'kauf',
			'kaufpreis__von' => '200000.00',
			'kaufpreis__bis' => '800000.00',
		];

		$unsuccessfulCombinations = [
			[false, false, false],
			[false, false, true],
			[false, true, false],
			[false, true, true],
			[true, false, false],
			[true, false, true],
			[true, true, false],
		];

		$this->_pFormPostConfiguration->setPostVariables($postVariables);
		$this->_pFormPostInterestConfiguration->setPostValues($postVariables);
		$this->_pFormPostConfiguration->getLogger()
			->expects($this->exactly(count($unsuccessfulCombinations)))->method('logError');

		foreach ($unsuccessfulCombinations as $values) {
			$this->addApiResponseCreateAddress($values[0]);
			$this->addApiResponseCreateSearchCriteria($values[1]);
			$this->addApiResponseSendMail($values[2]);
			$this->_pFormPostInterest->initialCheck($pConfig, 2);
			$pFormData = $this->_pFormPostInterest->getFormDataInstance('interestform', 2);

			$this->assertEquals(FormPost::MESSAGE_ERROR, $pFormData->getStatus());
		}
	}


	/**
	 *
	 */

	public function testMissingFields()
	{
		$pConfig = $this->getNewDataFormConfigurationInterest();
		$postValues = [
			'Vorname' => 'John',
		];
		$this->_pFormPostConfiguration->setPostVariables($postValues);
		$this->_pFormPostInterestConfiguration->setPostValues($postValues);

		$this->_pFormPostInterest->initialCheck($pConfig, 3);
		$pFormData = $this->_pFormPostInterest->getFormDataInstance('interestform', 3);

		$this->assertEquals(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING, $pFormData->getStatus());
		$this->assertEquals(['Name', 'vermarktungsart'], $pFormData->getMissingFields());
	}


	/**
	 *
	 * @return DataFormConfigurationInterest
	 *
	 */

	private function getNewDataFormConfigurationInterest(): DataFormConfigurationInterest
	{
		$pConfig = new DataFormConfigurationInterest();
		$pConfig->addInput('Vorname', onOfficeSDK::MODULE_ADDRESS);
		$pConfig->addInput('Name', onOfficeSDK::MODULE_ADDRESS);
		$pConfig->addInput('Email', onOfficeSDK::MODULE_ADDRESS);
		$pConfig->addInput('vermarktungsart', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pConfig->addInput('kaufpreis', onOfficeSDK::MODULE_SEARCHCRITERIA);
		$pConfig->setFormName('interestform');
		$pConfig->setRecipient('test@my-onoffice.com');
		$pConfig->setRequiredFields(['Vorname', 'Name', 'vermarktungsart']);
		$pConfig->setSubject('Interest');
		$pConfig->setTemplate('testtemplate.php');
		$pConfig->setFormType(Form::TYPE_INTEREST);

		$valueMap = [
			['Vorname', onOfficeSDK::MODULE_ADDRESS, FieldTypes::FIELD_TYPE_VARCHAR],
			['Name', onOfficeSDK::MODULE_ADDRESS, FieldTypes::FIELD_TYPE_VARCHAR],
			['Email', onOfficeSDK::MODULE_ADDRESS, FieldTypes::FIELD_TYPE_VARCHAR],
			['vermarktungsart', onOfficeSDK::MODULE_SEARCHCRITERIA, FieldTypes::FIELD_TYPE_SINGLESELECT],
			['kaufpreis', onOfficeSDK::MODULE_SEARCHCRITERIA, FieldTypes::FIELD_TYPE_FLOAT],
		];

		$this->_pFormPostConfiguration->getFieldnames()->method('getType')->will($this->returnValueMap($valueMap));

		return $pConfig;
	}


	/**
	 *
	 * @param bool $success
	 *
	 */

	private function addApiResponseCreateAddress(bool $success)
	{
		/* @var $pSDKWrapper SDKWrapperMocker */
		$pSDKWrapper = $this->_pFormPostConfiguration->getSDKWrapper();
		$parameters = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'email' => 'john@doemail.com',
			'checkDuplicate' => false,
		];

		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:create',
			'resourceid' => '',
			'resourcetype' => 'address',
			'cacheable' => false,
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => null,
				],
				'records' => [
					0 => [
						'id' => 294,
						'type' => 'address',
						'elements' => []
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		if (!$success) {
			$response = $this->getUnsuccessfulResponse(onOfficeSDK::ACTION_ID_CREATE, 'address');
		}

		$pSDKWrapper->addResponseByParameters
			(onOfficeSDK::ACTION_ID_CREATE, 'address', '', $parameters, null, $response);
	}


	/**
	 *
	 * @param bool $success
	 *
	 */

	private function addApiResponseCreateSearchCriteria(bool $success)
	{
		/* @var $pSDKWrapper SDKWrapperMocker */
		$pSDKWrapper = $this->_pFormPostConfiguration->getSDKWrapper();
		$parameters = [
			'data' => [
				'vermarktungsart' => 'kauf',
				'kaufpreis__von' => '200000.00',
				'kaufpreis__bis' => '800000.00',
			],
			'addressid' => 294,
		];

		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:create',
			'resourceid' => '',
			'resourcetype' => 'searchcriteria',
			'cacheable' => false,
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => null,
				],
				'records' => [
					0 => [
						'id' => 274,
						'type' => 'searchCriteria',
						'elements' => [],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		if (!$success) {
			$response = $this->getUnsuccessfulResponse
				(onOfficeSDK::ACTION_ID_CREATE, 'searchcriteria');
		}

		$pSDKWrapper->addResponseByParameters
			(onOfficeSDK::ACTION_ID_CREATE, 'searchcriteria', '', $parameters, null, $response);
	}


	/**
	 *
	 * @param bool $success
	 *
	 */

	private function addApiResponseSendMail(bool $success)
	{
		/* @var $pSDKWrapper SDKWrapperMocker */
		$pSDKWrapper = $this->_pFormPostConfiguration->getSDKWrapper();
		$parameters = [
			'anonymousEmailidentity' => true,
			'body' => 'Sehr geehrte Damen und Herren,'."\n\n"
				.'ein neuer Interessent hat sich über das Kontaktformular auf Ihrer Webseite '
				.'eingetragen. Die Adresse (John Doe) wurde bereits in Ihrem System eingetragen.'
				."\n\n"
				.'Herzliche Grüße'."\n"
				.'Ihr onOffice Team',
			'subject' => 'Interest',
			'replyto' => 'john@doemail.com',
			'receiver' => [
				'test@my-onoffice.com',
			],
			'X-Original-From' => 'john@doemail.com',
			'saveToAgentsLog' => false,
		];

		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:do',
			'resourceid' => '',
			'resourcetype' => 'sendmail',
			'cacheable' => false,
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => null,
				],
				'records' => [
					0 => [
						'id' => 0,
						'type' => '',
						'elements' => [
							'success' => 'success',
							'readablereport' => 'Es wurde eine E-Mail versendet'."\n\n",
						],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		if (!$success) {
			$response = $this->getUnsuccessfulResponse(onOfficeSDK::ACTION_ID_DO, 'sendmail');
		}

		$pSDKWrapper->addResponseByParameters
			(onOfficeSDK::ACTION_ID_DO, 'sendmail', '', $parameters, null, $response);
	}


	/**
	 *
	 * @param string $actionId
	 * @param string $resourceType
	 * @return array
	 *
	 */

	private function getUnsuccessfulResponse(string $actionId, string $resourceType)
	{
		return [
			'actionid' => $actionId,
			'resourceid' => '',
			'resourcetype' => $resourceType,
			'cacheable' => false,
			'identifier' => '',
			'data' => [],
			'status' => [
				'errorcode' => 2093,
				'message' => 'Unknown error',
			],
		];
	}
}
