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
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationOwner;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Form\FormAddressCreator;
use onOffice\WPlugin\Form\FormPostConfigurationTest;
use onOffice\WPlugin\Form\FormPostOwnerConfiguration;
use onOffice\WPlugin\Form\FormPostOwnerConfigurationTest;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\FormPostOwner;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\Field;
use WP_UnitTestCase;
use function json_decode;


/**
 *
 */

class TestClassFormPostOwner
	extends WP_UnitTestCase
{
	/** @var FormPostOwner */
	private $_pFormPostOwner = null;

	/** @var SDKWrapperMocker */
	private $_pSDKWrapperMocker = null;

	/** @var FormPostConfigurationTest */
	private $_pFormPostConfiguration = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;

	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSDKWrapperMocker = new SDKWrapperMocker();
		$this->_pFormPostConfiguration = $this->createNewFormPostConfigurationTest();
		$this->prepareSDKWrapperForFieldsAddressEstate();

		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->setMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria', 'addFieldsFormFrontend'])
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
				$pFieldVorname = new Field('Vorname', onOfficeSDK::MODULE_ADDRESS);
				$pFieldVorname->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldVorname);

				$pFieldName = new Field('Name', onOfficeSDK::MODULE_ADDRESS);
				$pFieldName->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldName);

				$pFieldArtDaten = new Field('ArtDaten', onOfficeSDK::MODULE_ADDRESS);
				$pFieldArtDaten->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
				$pFieldsCollection->addField($pFieldArtDaten);

				$pFieldTel1 = new Field('Telefon1', onOfficeSDK::MODULE_ADDRESS);
				$pFieldTel1->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pFieldTel1);

				$FieldObjektart = new Field('objektart', onOfficeSDK::MODULE_ESTATE);
				$FieldObjektart->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
				$pFieldsCollection->addField($FieldObjektart);

				$FieldObjekttyp = new Field('objekttyp', onOfficeSDK::MODULE_ESTATE);
				$FieldObjekttyp->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
				$pFieldsCollection->addField($FieldObjekttyp);

				$pFieldEnergieausweistyp = new Field('energieausweistyp', onOfficeSDK::MODULE_ESTATE);
				$pFieldEnergieausweistyp->setType(FieldTypes::FIELD_TYPE_SINGLESELECT);
				$pFieldsCollection->addField($pFieldEnergieausweistyp);

				$pFieldWohnfl = new Field('wohnflaeche', onOfficeSDK::MODULE_ESTATE);
				$pFieldWohnfl->setType(FieldTypes::FIELD_TYPE_FLOAT);
				$pFieldsCollection->addField($pFieldWohnfl);

				$pFieldKabelSatTv = new Field('kabel_sat_tv', onOfficeSDK::MODULE_ESTATE);
				$pFieldKabelSatTv->setType(FieldTypes::FIELD_TYPE_BOOLEAN);
				$pFieldsCollection->addField($pFieldKabelSatTv);

				return $this->_pFieldsCollectionBuilderShort;
			}));

		$this->_pFieldsCollectionBuilderShort->method('addFieldsFormFrontend')
				->with($this->anything())
				->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
			$pField1 = new Field('message', '');
			$pField1->setType(FieldTypes::FIELD_TYPE_VARCHAR);
			$pFieldsCollection->addField($pField1);

			return $this->_pFieldsCollectionBuilderShort;
		}));

		$pContainer = new Container;
		$pContainer->set(SDKWrapper::class, $this->_pSDKWrapperMocker);
		$pFormAddressCreator = new FormAddressCreator($this->_pSDKWrapperMocker,
			new FieldsCollectionBuilderShort($pContainer));
		$pFormPostOwnerConfiguration = new FormPostOwnerConfigurationTest
			($this->_pSDKWrapperMocker, $pFormAddressCreator);
		$pFormPostOwnerConfiguration->setReferrer('/test/page/1');
		$this->configureEstateListInputVariableReaderConfig($pFormPostOwnerConfiguration);

		$this->_pFormPostOwner = new FormPostOwner($this->_pFormPostConfiguration,
			$pFormPostOwnerConfiguration, $this->_pFieldsCollectionBuilderShort);
	}


	/**
	 *
	 * @param FormPostOwnerConfiguration $pFormPostOwnerConfiguration
	 *
	 */

	private function configureEstateListInputVariableReaderConfig
		(FormPostOwnerConfiguration $pFormPostOwnerConfiguration)
	{
		$moduleEstate = onOfficeSDK::MODULE_ESTATE;

		$pEstateListInputVariableReaderConfig =
			$pFormPostOwnerConfiguration->getEstateListInputVariableReaderConfigTest();
		$pEstateListInputVariableReaderConfig->setValue('objektart', 'haus');
		$pEstateListInputVariableReaderConfig->setValue('objekttyp', 'stadthaus');
		$pEstateListInputVariableReaderConfig->setValue('energieausweistyp', 'Bedarfsausweis');
		$pEstateListInputVariableReaderConfig->setValue('wohnflaeche', '800');
		$pEstateListInputVariableReaderConfig->setValue('kabel_sat_tv', 'y');
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('objektart', $moduleEstate, FieldTypes::FIELD_TYPE_VARCHAR);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('objekttyp', $moduleEstate, FieldTypes::FIELD_TYPE_VARCHAR);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('energieausweistyp', $moduleEstate, FieldTypes::FIELD_TYPE_SINGLESELECT);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('wohnflaeche', $moduleEstate, FieldTypes::FIELD_TYPE_INTEGER);
		$pEstateListInputVariableReaderConfig->setFieldTypeByModule
			('kabel_sat_tv', $moduleEstate, FieldTypes::FIELD_TYPE_BOOLEAN);
	}


	/**
	 *
	 * @return FormPostConfigurationTest
	 *
	 */

	private function createNewFormPostConfigurationTest(): FormPostConfigurationTest
	{
		$pLogger = $this->getMockBuilder(Logger::class)->getMock();

		$pFormPostConfiguration = new FormPostConfigurationTest($pLogger);
		return $pFormPostConfiguration;
	}


	/**
	 *
	 */

	public function testInitialCheckSuccess()
	{
		$_POST = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'ArtDaten' => 'Eigentümer',
			'Telefon1' => '0815 234567890',
			'objektart' => 'haus',
			'objekttyp' => 'stadthaus',
			'energieausweistyp' => 'Bedarfsausweis',
			'wohnflaeche' => 800,
			'kabel_sat_tv' => 'y',
			'message' => 'Hello! I am interested in selling my property!',
		];

		$this->prepareMockerForAddressCreationSuccess();
		$this->prepareMockerForEstateCreationSuccess();
		$this->prepareMockerForRelationSuccess();
		$this->prepareMockerForContactSuccess();
		$pDataFormConfiguration = $this->getDataFormConfiguration();

		$this->_pFormPostOwner->initialCheck($pDataFormConfiguration, 5);
		$pFormData = $this->_pFormPostOwner->getFormDataInstance('test', 5);
		$this->assertInstanceOf(FormData::class, $pFormData);
		$this->assertTrue($pFormData->getFormSent());

		$estateData = [
			'objektart' => 'haus',
			'objekttyp' => 'stadthaus',
			'energieausweistyp' => 'Bedarfsausweis',
			'wohnflaeche' => 800,
			'kabel_sat_tv' => true,
		];

		$this->assertEquals(FormPost::MESSAGE_SUCCESS, $pFormData->getStatus());
		$this->assertEquals($estateData, $this->_pFormPostOwner->getEstateData());
	}


	/**
	 *
	 */

	public function testInitialCheckMissingFields()
	{
		$this->_pFormPostConfiguration->setPostVariables([
			'Vorname' => 'John',
			// missing Name
			'ArtDaten' => 'Eigentümer',
			'objektart' => 'haus',
			'kabel_sat_tv' => 'y',
		]);

		$pDataFormConfiguration = $this->getDataFormConfiguration();
		$this->_pFormPostOwner->initialCheck($pDataFormConfiguration, 3);

		$pFormData = $this->_pFormPostOwner->getFormDataInstance('test', 3);
		$this->assertInstanceOf(FormData::class, $pFormData);
		$this->assertTrue($pFormData->getFormSent());
		$this->assertEquals(FormPost::MESSAGE_REQUIRED_FIELDS_MISSING, $pFormData->getStatus());
	}


	/**
	 *
	 * No relation should be created if the address wasn't created but the estate was
	 *
	 */

	public function testInitialCheckUnsuccessfulAddressCreation()
	{
		$_POST = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'ArtDaten' => 'Eigentümer',
			'Telefon1' => '0815 234567890',
			'objektart' => 'haus',
			'objekttyp' => 'stadthaus',
			'energieausweistyp' => 'Bedarfsausweis',
			'wohnflaeche' => 800,
			'kabel_sat_tv' => 'y',
			'message' => 'Hello! I am interested in selling my property!',
		];

		$this->prepareMockerForAddressCreationNoSuccess();
		$this->prepareMockerForEstateCreationSuccess();
		$this->_pFormPostConfiguration->getLogger()->expects($this->once())->method('logError');

		$pDataFormConfiguration = $this->getDataFormConfiguration();
		$this->_pFormPostOwner->initialCheck($pDataFormConfiguration, 3);

		$pFormData = $this->_pFormPostOwner->getFormDataInstance('test', 3);
		$this->assertInstanceOf(FormData::class, $pFormData);
		$this->assertTrue($pFormData->getFormSent());
		$this->assertEquals(FormPost::MESSAGE_ERROR, $pFormData->getStatus());
	}


	/**
	 *
	 * No relation should be created if the address wasn't created but the estate was
	 *
	 */

	public function testInitialCheckUnsuccessfulEstateCreation()
	{
		$_POST = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'ArtDaten' => 'Eigentümer',
			'Telefon1' => '0815 234567890',
			'objektart' => 'haus',
			'objekttyp' => 'stadthaus',
			'energieausweistyp' => 'Bedarfsausweis',
			'wohnflaeche' => 800,
			'kabel_sat_tv' => 'y',
			'message' => 'Hello! I am interested in selling my property!',
		];

		$this->prepareMockerForAddressCreationSuccess();
		$this->prepareMockerForEstateCreationNoSuccess();
		$pDataFormConfiguration = $this->getDataFormConfiguration();
		$this->_pFormPostOwner->initialCheck($pDataFormConfiguration, 3);

		$pFormData = $this->_pFormPostOwner->getFormDataInstance('test', 3);
		$this->assertInstanceOf(FormData::class, $pFormData);
		$this->assertTrue($pFormData->getFormSent());
		$this->assertEquals(FormPost::MESSAGE_ERROR, $pFormData->getStatus());
	}



	/**
	 *
	 */

	private function prepareSDKWrapperForFieldsAddressEstate()
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
		$this->_pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
			$fieldParameters, null, $responseGetFields);
	}


	/**
	 *
	 */

	private function prepareMockerForAddressCreationSuccess()
	{
		$parameters = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'ArtDaten' => ['Eigentümer'],
			'phone' => '0815 234567890',
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
						'id' => 281,
						'type' => 'address',
						'elements' => [],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		$this->_pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_CREATE, 'address',
			'', $parameters, null, $response);
	}


	/**
	 *
	 */

	private function prepareMockerForAddressCreationNoSuccess()
	{
		$parameters = [
			'Vorname' => 'John',
			'Name' => 'Doe',
			'ArtDaten' => ['Eigentümer'],
			'phone' => '0815 234567890',
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
				'records' => [],
			],
			'status' => [
				'errorcode' => 500,
				'message' => 'Error',
			],
		];

		$this->_pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_CREATE, 'address',
			'', $parameters, null, $response);
	}


	/**
	 *
	 */

	private function prepareMockerForEstateCreationSuccess()
	{
		$parameters = [
			'data' => [
				'objektart' => 'haus',
				'objekttyp' => 'stadthaus',
				'energieausweistyp' => 'Bedarfsausweis',
				'wohnflaeche' => 800.0,
				'kabel_sat_tv' => true,
			],
		];

		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:create',
			'resourceid' => '',
			'resourcetype' => 'estate',
			'cacheable' => false,
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => null,
				],
				'records' => [
					0 => [
						'id' => 5590,
						'type' => 'estate',
						'elements' => [],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		$this->_pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_CREATE, 'estate',
			'', $parameters, null, $response);
	}


	/**
	 *
	 */

	private function prepareMockerForEstateCreationNoSuccess()
	{
		$parameters = [
			'data' => [
				'objektart' => 'haus',
				'objekttyp' => 'stadthaus',
				'energieausweistyp' => 'Bedarfsausweis',
				'wohnflaeche' => 800.0,
				'kabel_sat_tv' => true,
			],
		];

		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:create',
			'resourceid' => '',
			'resourcetype' => 'estate',
			'cacheable' => false,
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => null,
				],
				'records' => [],
			],
			'status' => [
				'errorcode' => 500,
				'message' => 'Error',
			],
		];

		$this->_pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_CREATE, 'estate',
			'', $parameters, null, $response);
	}


	/**
	 *
	 */

	private function prepareMockerForRelationSuccess()
	{
		$parameters = [
			'relationtype' => 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:owner',
			'parentid' => 5590,
			'childid' => 281,
		];

		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:create',
			'resourceid' => '',
			'resourcetype' => 'relation',
			'cacheable' => false,
			'identifier' => '',
			'data' => [
				'meta' => [
					'cntabsolute' => null,
				],
				'records' => [],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		$this->_pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_CREATE, 'relation',
			'', $parameters, null, $response);
	}


	/**
	 *
	 */

	private function prepareMockerForContactSuccess()
	{
		$parameters = [
			'addressdata' => [
				'Vorname' => 'John',
				'Name' => 'Doe',
				'ArtDaten' => ['Eigentümer'],
				'Telefon1' => '0815 234567890',
			],
			'estateid' => 5590,
			'message' => 'Hello! I am interested in selling my property!',
			'subject' => null,
			'referrer' => '/test/page/1',
			'formtype' => 'owner',
			'recipient' => 'test@my-onoffice.com'
		];

		$response = [
			'actionid' => 'urn:onoffice-de-ns:smart:2.5:smartml:action:do',
			'resourceid' => '',
			'resourcetype' => 'contactaddress',
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
						],
					],
				],
			],
			'status' => [
				'errorcode' => 0,
				'message' => 'OK',
			],
		];

		$this->_pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_DO,
			'contactaddress', '', $parameters, null, $response);
	}


	/**
	 *
	 * @return DataFormConfigurationOwner
	 *
	 */

	private function getDataFormConfiguration()
	{
		$pDataFormConfiguration = new DataFormConfigurationOwner();

		$pDataFormConfiguration->addInput('Vorname', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Name', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('ArtDaten', onOfficeSDK::MODULE_ADDRESS);
		$pDataFormConfiguration->addInput('Telefon1', onOfficeSDK::MODULE_ADDRESS);

		$pDataFormConfiguration->addInput('objektart', onOfficeSDK::MODULE_ESTATE);
		$pDataFormConfiguration->addInput('objekttyp', onOfficeSDK::MODULE_ESTATE);
		$pDataFormConfiguration->addInput('energieausweistyp', onOfficeSDK::MODULE_ESTATE);
		$pDataFormConfiguration->addInput('wohnflaeche', onOfficeSDK::MODULE_ESTATE);
		$pDataFormConfiguration->addInput('kabel_sat_tv', onOfficeSDK::MODULE_ESTATE);
		$pDataFormConfiguration->addInput('kabel_sat_tv', onOfficeSDK::MODULE_ESTATE);
		$pDataFormConfiguration->addInput('message', '');

		$pDataFormConfiguration->setRequiredFields(['Vorname', 'Name', 'objektart']);

		$pDataFormConfiguration->setFormType(Form::TYPE_OWNER);
		$pDataFormConfiguration->setRecipient('test@my-onoffice.com');
		$pDataFormConfiguration->setPages(2);
		$pDataFormConfiguration->setFormName('test');

		return $pDataFormConfiguration;
	}
}
