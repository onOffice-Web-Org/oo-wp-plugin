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
use DI\ContainerBuilder;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostConfigurationTest;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\FormPostApplicantSearch;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\Logger;
use WP_UnitTestCase;
use function DI\autowire;
use function json_decode;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFormPostApplicantSearch
	extends WP_UnitTestCase
{
	/** @var FormPostApplicantSearch */
	private $_pFormPostApplicantSearch = null;

	/** @var DataFormConfigurationApplicantSearch */
	private $_pDataFormConfiguration = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;

	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->onlyMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria', 'addFieldsFormFrontend'])
			->setConstructorArgs([new Container])
			->getMock();

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

				$FieldObjektart = new Field('objektart', onOfficeSDK::MODULE_ESTATE);
				$FieldObjektart->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
				$pFieldsCollection->addField($FieldObjektart);

				$FieldVermarktungsart = new Field('vermarktungsart', onOfficeSDK::MODULE_ESTATE);
				$FieldVermarktungsart->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
				$pFieldsCollection->addField($FieldVermarktungsart);

				$pFieldWohnfl = new Field('wohnflaeche', onOfficeSDK::MODULE_ESTATE);
				$pFieldWohnfl->setType(FieldTypes::FIELD_TYPE_FLOAT);
				$pFieldsCollection->addField($pFieldWohnfl);

				$pFieldKabelSatTv = new Field('kaufpreis', onOfficeSDK::MODULE_ESTATE);
				$pFieldKabelSatTv->setType(FieldTypes::FIELD_TYPE_INTEGER);
				$pFieldsCollection->addField($pFieldKabelSatTv);

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

		$pSDKWrapperMocker = $this->setupSDKWrapperMocker();
		$_POST = [
			'objektart' => 'haus',
			'vermarktungsart' => 'kauf',
			'kaufpreis' => '200000',
			'wohnflaeche' => '800',
		];

		$this->setupDataFormConfiguration();

		$pSearchcriteriaFields = $this->getMockBuilder(SearchcriteriaFields::class)
			->setConstructorArgs([new FieldsCollectionBuilderShort(new Container)])
			->onlyMethods(['getFormFields'])
			->getMock();
		$pSearchcriteriaFields->method('getFormFields')->with($this->anything())->will($this->returnArgument(0));

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pContainer->set(FormPostConfiguration::class, autowire(FormPostConfigurationTest::class));
		$pContainer->set(SDKWrapper::class, $pSDKWrapperMocker);
		$pContainer->set(SearchcriteriaFields::class, $pSearchcriteriaFields);
		$pContainer->set(Logger::class, $this->getMockBuilder(Logger::class)->getMock());
		$pContainer->set(FieldsCollectionBuilderShort::class, $this->_pFieldsCollectionBuilderShort);

		$this->_pFormPostApplicantSearch = $pContainer->get(FormPostApplicantSearch::class);
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
				'kaufpreis' => 200000,
				'wohnflaeche' => 800,
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
		$_POST = [
			'objektart' => 'haus',
			'vermarktungsart' => 'kauf',
			'kaufpreis' => '200000',
			'wohnflaeche' => '800',
		];

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
		$_POST = [
			'objektart' => 'haus',
			'vermarktungsart' => 'kauf',
			'kaufpreis' => '200000',
			'wohnflaeche' => '800',
		];

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
		$_POST = [
			'vermarktungsart' => 'kauf',
		];

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
