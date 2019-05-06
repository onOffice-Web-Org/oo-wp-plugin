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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormPostApplicantSearchConfigurationTest;
use onOffice\WPlugin\Form\FormPostConfigurationTest;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\FormPostApplicantSearch;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Utility\Logger;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFormPostApplicantSearch
	extends WP_UnitTestCase
{
	/** @var FormPostApplicantSearchConfigurationTest */
	private $_pTestConfigurationApplicantSearch = null;

	/** @var FormPostConfigurationTest */
	private $_pFormPostConfigurationTest = null;

	/** @var FormPostApplicantSearch */
	private $_pFormPostApplicantSearch = null;

	/** @var DataFormConfigurationApplicantSearch */
	private $_pDataFormConfiguration = null;


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

		$this->_pFormPostConfigurationTest = new FormPostConfigurationTest($pFieldnames, $pLogger);
		$pSDKWrapperMocker = $this->setupSDKWrapperMocker();
		$this->_pFormPostConfigurationTest->setSDKWrapper($pSDKWrapperMocker);
		$this->_pFormPostConfigurationTest->setPostVariables([
			'objektart' => 'haus',
			'vermarktungsart' => 'kauf',
			'kaufpreis' => '200000',
			'wohnflaeche' => '800',
		]);

		$this->setupDataFormConfiguration();

		$this->_pTestConfigurationApplicantSearch = new FormPostApplicantSearchConfigurationTest();
		$this->_pTestConfigurationApplicantSearch->setSDKWrapper($pSDKWrapperMocker);

		$this->_pFormPostApplicantSearch = new FormPostApplicantSearch($this->_pFormPostConfigurationTest,
			$this->_pTestConfigurationApplicantSearch);
	}


	/**
	 *
	 * @return SDKWrapperMocker
	 *
	 */

	private function setupSDKWrapperMocker(): SDKWrapperMocker
	{
		$searchCriteriaFieldsResponseEng = file_get_contents
			(__DIR__.'/resources/ApiResponseGetSearchcriteriaFieldsENG.json');
		$responseArraySearchCriteriaFieldsENG = json_decode($searchCriteriaFieldsResponseEng, true);
		$searchSearchCriteriaResponse = file_get_contents
			(__DIR__.'/resources/FormPostApplicantSearch/ApiResponseGetSearchSearchCriteria.json');
		$responseSearchSearchCriteria = json_decode($searchSearchCriteriaResponse, true);
		$readAddressResponse = file_get_contents
			(__DIR__.'/resources/FormPostApplicantSearch/ApiResponseReadAddress.json');
		$responseReadAddress = json_decode($readAddressResponse, true);

		$pSDKWrapperMocker = new SDKWrapperMocker();
		$searchSearchcriteriaParameter = [
			'searchdata' => [
				'objektart' => 'haus',
				'vermarktungsart' => 'kauf',
				'kaufpreis' => '200000',
				'wohnflaeche' => '800',
			],
			'outputall' => true,
			'groupbyaddress' => true,
			'limit' => 100,
			'offset' => 0,
		];

		$pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_GET, 'search', 'searchcriteria', $searchSearchcriteriaParameter,
				null, $responseSearchSearchCriteria);

		$readAddressParameters = [
			'recordids' => [122, 169],
			'data' => ['KdNr'],
		];

		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_READ, 'address', '',
			$readAddressParameters, null, $responseReadAddress);

		$pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_GET, 'searchCriteriaFields', '', [
				'language' => 'ENG',
				'additionalTranslations' => true,
			], null, $responseArraySearchCriteriaFieldsENG);

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

		return $pSDKWrapperMocker;
	}


	/**
	 *
	 */

	private function setupDataFormConfiguration()
	{
		$this->_pDataFormConfiguration = new DataFormConfigurationApplicantSearch();
		$this->_pDataFormConfiguration->setCaptcha(false);
		$this->_pDataFormConfiguration->setFormName('applicantsearch');
		$this->_pDataFormConfiguration->setInputs([
			'objektart' => onOfficeSDK::MODULE_ESTATE,
			'vermarktungsart' => onOfficeSDK::MODULE_ESTATE,
			'kaufpreis' => onOfficeSDK::MODULE_ESTATE,
			'wohnflaeche' => onOfficeSDK::MODULE_ESTATE,
		]);

		$this->_pDataFormConfiguration->setRequiredFields([
			'objektart',
			'vermarktungsart',
			'kaufpreis',
			'wohnflaeche',
		]);
	}


	/**
	 *
	 */

	public function testGeneral()
	{
		$this->_pFormPostApplicantSearch->initialCheck($this->_pDataFormConfiguration, 2);
		$pFormData = $this->_pFormPostApplicantSearch->getFormDataInstance(Form::TYPE_APPLICANT_SEARCH, 2);

		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
		$this->assertTrue($pFormData->getFormSent());
	}


	/**
	 *
	 */

	public function testApplicantResult()
	{
		$this->_pFormPostApplicantSearch->initialCheck($this->_pDataFormConfiguration, 2);
		$pFormData = $this->_pFormPostApplicantSearch->getFormDataInstance(Form::TYPE_APPLICANT_SEARCH, 2);

		$result = $pFormData->getResponseFieldsValues();
		$expectation = [
			51 => [
				'objektart' => 'haus',
				'vermarktungsart' => 'kauf',
				'kaufpreis' => [0, 200000.00],
				'wohnflaeche' => [30.00, 200.00],
			],
			62 => [
				'objektart' => 'haus',
				'vermarktungsart' => 'kauf',
				'kaufpreis' => [160000.00, 250000.00],
				'wohnflaeche' => [67.00, 100.00],
			],
		];

		$this->assertEquals($expectation, $result);
	}


	/**
	 *
	 */

	public function testRequiredFields()
	{
		$this->_pFormPostConfigurationTest->setPostVariables([
			'vermarktungsart' => 'kauf',
		]);

		$this->_pFormPostApplicantSearch->initialCheck($this->_pDataFormConfiguration, 2);
		$pFormData = $this->_pFormPostApplicantSearch->getFormDataInstance(Form::TYPE_APPLICANT_SEARCH, 2);
		$this->assertEquals(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING, $pFormData->getStatus());

		$missingFieldsResult = $pFormData->getMissingFields();
		$missingFieldsExpectation = [
			'objektart',
			'kaufpreis',
			'wohnflaeche',
		];

		$this->assertEqualSets($missingFieldsExpectation, $missingFieldsResult);
	}
}
