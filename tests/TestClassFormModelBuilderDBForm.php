<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
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
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilder;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelLabel;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\SDKWrapper;

class TestClassFormModelBuilderDBForm
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/** @var InputModelDBFactory */
	private $_pInputModelFactoryDBEntry;

	/** @var FormModelBuilderDBForm */
	private $_pInstance;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pInputModelFactoryDBEntry = new InputModelDBFactory(new InputModelDBFactoryConfigForm);
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();

		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
				->setMethods(['addFieldsAddressEstate', 'addFieldsSearchCriteria',  'addFieldsFormBackend'])
				->setConstructorArgs([$this->_pContainer])
				->getMock();
		$this->_pContainer->set(FieldsCollectionBuilderShort::class, $this->_pFieldsCollectionBuilderShort);
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
				$pField1 = new Field('Vorname', onOfficeSDK::MODULE_ADDRESS);
				$pField1->setType(FieldTypes::FIELD_TYPE_VARCHAR);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));
			
		$this->_pFieldsCollectionBuilderShort->method('addFieldsFormBackend')
				->with($this->anything())
				->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
			$pField1 = new Field('region_plz', onOfficeSDK::MODULE_SEARCHCRITERIA);
			$pField1->setType(FieldTypes::FIELD_TYPE_VARCHAR);
			$pFieldsCollection->addField($pField1);

			return $this->_pFieldsCollectionBuilderShort;
		}));

		$pSDKWrapperMocker = new SDKWrapperMocker();
		$response = json_decode
		(file_get_contents(__DIR__ . '/resources/ApiResponseActionKindTypes.json'), true);
		/* @var $pSDKWrapperMocker SDKWrapperMocker */

		$pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_GET, 'actionkindtypes', '', ['lang'=> "ENG"], null, $response);

		$parameters = [
			'labels' => true,
			'language' => "ENG",
			'fieldList' => ['merkmal', 'HerkunftKontakt'],
			'modules' => ['agentsLog', 'address']
		];
		$pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_GET, 'fields', '', $parameters, null, $this->getResponseFieldCharacteristic());

		$parametersGetFieldList = [
			'labels' => true,
			'showContent' => true,
			'showTable' => true,
			'fieldList' => ['benutzer'],
			'language' => 'ENG',
			'modules' => [onOfficeSDK::MODULE_ESTATE],
			'realDataTypes' => true
		];
		$responseGetSupervisorFields = json_decode
		(file_get_contents(__DIR__.'/resources/ApiResponseSupervisorFields.json'), true);
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '',
			$parametersGetFieldList, null, $responseGetSupervisorFields);

		$responseGetListSupervisors = json_decode(file_get_contents(__DIR__ . '/resources/ApiResponseListSupervisors.json'), true);
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'users', '', [], null,
			$responseGetListSupervisors);
		$this->_pContainer->set(SDKWrapper::class, $pSDKWrapperMocker);
		$this->_pInstance = new FormModelBuilderDBForm($this->_pContainer);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelRecipientContactForm
	 */
	public function testCreateInputModelRecipientContactForm()
	{
		$pInputModelDB = $this->_pInstance->createInputModelRecipientContactForm();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'email');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelDefaultRecipient
	 */
	public function testCreateInputModelDefaultRecipient()
	{
		$pInstance = $this->getMockBuilder( FormModelBuilderDBForm::class )
		                  ->disableOriginalConstructor()
		                  ->setMethods( [ 'getInputModelDBFactory', 'getValue' ] )
		                  ->getMock();
		add_option( 'onoffice-settings-default-email', 'a@a' );
		$pInstance->method( 'getInputModelDBFactory' )->willReturn( $this->_pInputModelFactoryDBEntry );
		$pInstance->method( 'getValue' )->willReturn( '1' );

		$pInputModelDB = $pInstance->createInputModelDefaultRecipient();
		$this->assertInstanceOf( InputModelDB::class, $pInputModelDB );
		$this->assertEquals( 'checkbox', $pInputModelDB->getHtmlType() );
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelDefaultRecipient
	 */
	public function testCreateInputModelDefaultRecipientWithoutEmail()
	{
		$pInstance = $this->getMockBuilder( FormModelBuilderDBForm::class )
		                  ->disableOriginalConstructor()
		                  ->setMethods( [ 'getInputModelDBFactory', 'getValue' ] )
		                  ->getMock();

		$pInstance->method( 'getInputModelDBFactory' )->willReturn( $this->_pInputModelFactoryDBEntry );
		$pInstance->method( 'getValue' )->willReturn( '1' );

		$pInputModelDB = $pInstance->createInputModelDefaultRecipient();
		$this->assertInstanceOf( InputModelDB::class, $pInputModelDB );
		$this->assertEquals( 'checkbox', $pInputModelDB->getHtmlType() );
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelFormType
	 */
	public function testCreateInputModelFormType()
	{
		$pInstance = $this->getMockBuilder( FormModelBuilderDBForm::class )
		                  ->disableOriginalConstructor()
		                  ->setMethods( [ 'getInputModelDBFactory', 'getValue', 'getFormType' ] )
		                  ->getMock();

		$pInstance->method( 'getInputModelDBFactory' )->willReturn( $this->_pInputModelFactoryDBEntry );
		$pInstance->method( 'getValue' )->willReturn( '1' );
		$pInstance->method( 'getFormType' )->willReturn( 'contact' );

		$pInputModelDB = $pInstance->createInputModelFormType();
		$this->assertInstanceOf( InputModelLabel::class, $pInputModelDB );
		$this->assertEquals( 'label', $pInputModelDB->getHtmlType() );
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelCaptchaRequired
	 */
	public function testCreateInputModelCaptchaRequired()
	{
		$pInstance = $this->getMockBuilder( FormModelBuilderDBForm::class )
		                  ->disableOriginalConstructor()
		                  ->setMethods( [ 'getInputModelDBFactory', 'getValue' ] )
		                  ->getMock();

		$pInstance->method( 'getInputModelDBFactory' )->willReturn( $this->_pInputModelFactoryDBEntry );
		$pInstance->method( 'getValue' )->willReturn( '1' );

		$pInputModelDB = $pInstance->createInputModelCaptchaRequired();
		$this->assertInstanceOf( InputModelDB::class, $pInputModelDB );
		$this->assertEquals( 'checkbox', $pInputModelDB->getHtmlType() );
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelContactType
	 */
	public function testCreateInputModelContactType()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBForm::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue', 'getDataContactType'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');
		$pInstance->method('getDataContactType')->willReturn([]);

		$pInputModelDB = $pInstance->createInputModelContactType();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select2');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getDataContactType
	 */
	public function testGetDataContactType()
	{
		$fieldCollectionBuilder = $this->getMockBuilder(FieldsCollectionBuilder::class)
			->disableOriginalConstructor()
			->setMethods([ 'buildFieldsCollection'])
			->getMock();

		$pFieldsCollection = $this->getMockBuilder(FieldsCollection::class)
			->disableOriginalConstructor()
			->setMethods(['getFieldsByModule'])
			->getMock();

		$mockData = [
			'test' => 'abc'
		];
		$mockField = new Field('ArtDaten', onOfficeSDK::MODULE_ADDRESS, 'Type of contact');
		$mockField->setPermittedvalues($mockData);

		$pFieldsCollection->method('getFieldsByModule')->willReturn(['ArtDaten' => $mockField]);

		$fieldCollectionBuilder->method('buildFieldsCollection')->willReturn($pFieldsCollection);



		$this->_pContainer->set(FieldsCollectionBuilder::class, $fieldCollectionBuilder);

		$pInstance = $this->_pContainer->get(FormModelBuilderDBForm::class);

		$result = $pInstance->getDataContactType('address');

		$this->assertEquals($mockData, $result);
	}

		/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getDataContactType
	 */
	public function testGetDataContactTypeThrowException()
	{
		$fieldCollectionBuilder = $this->getMockBuilder(FieldsCollectionBuilder::class)
			->disableOriginalConstructor()
			->setMethods(['buildFieldsCollection'])
			->getMock();

		$ex = $this->_pContainer->get(APIClientCredentialsException::class);


		$fieldCollectionBuilder->method('buildFieldsCollection')->will($this->throwException($ex));

		$this->_pContainer->set(FieldsCollectionBuilder::class, $fieldCollectionBuilder);

		$pInstance = $this->_pContainer->get(FormModelBuilderDBForm::class);

		$result = $pInstance->getDataContactType('address');

		$this->assertEquals([], $result);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getInputModelIsRequired
	 */
	public function testGetInputModelIsRequired()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBForm::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->getInputModelIsRequired();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelName
	 */
	public function testCreateInputModelName()
	{
		$pInputModelName = $this->_pInstance->createInputModelName();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelName);
		$this->assertEquals($pInputModelName->getHtmlType(), 'text');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelEmbedCode
	 */
	public function testCreateInputModelEmbedCode()
	{
		$pInputModelFormEmbedCode = $this->_pInstance->createInputModelEmbedCode();
		$this->assertInstanceOf(InputModelLabel::class, $pInputModelFormEmbedCode);
		$this->assertEquals($pInputModelFormEmbedCode->getHtmlType(), 'label');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelButton
	 */
	public function testCreateInputModelButton()
	{
		$pInputModelButton = $this->_pInstance->createInputModelButton();
		$this->assertInstanceOf(InputModelLabel::class, $pInputModelButton);
		$this->assertEquals($pInputModelButton->getHtmlType(), 'button');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelResultLimit
	 */
	public function testCreateInputModelResultLimit()
	{
		$pInputModelResultLimit = $this->_pInstance->createInputModelResultLimit();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelResultLimit);
		$this->assertEquals($pInputModelResultLimit->getHtmlType(), 'text');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelRecipient
	 */
	public function testCreateInputModelRecipient()
	{
		$pInputModelRecipient = $this->_pInstance->createInputModelRecipient();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelRecipient);
		$this->assertEquals($pInputModelRecipient->getHtmlType(), 'email');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getInputModelIsAvailableOptions
	 */
	public function testGetInputModelIsAvailableOptions()
	{
		$pInputModelIsAvailableOptions = $this->_pInstance->getInputModelIsAvailableOptions();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelIsAvailableOptions);
		$this->assertEquals($pInputModelIsAvailableOptions->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelFieldsConfigByCategory
	 */
	public function testCreateInputModelFieldsConfigByCategory()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBForm::class)
		                  ->disableOriginalConstructor()
		                  ->setMethods(['getInputModelDBFactory', 'getValue'])
		                  ->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelFieldsConfigByCategory('category','name','label');

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals( 'checkboxWithSubmitButton', $pInputModelDB->getHtmlType() );
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createButtonModelFieldsConfigByCategory
	 */
	public function testCreateButtonModelFieldsConfigByCategory()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBForm::class)
		                  ->disableOriginalConstructor()
		                  ->setMethods(['getInputModelDBFactory', 'getValue'])
		                  ->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createButtonModelFieldsConfigByCategory('category','name','label');

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals( 'buttonHandleField', $pInputModelDB->getHtmlType() );
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getInputModelIsMarkDown
	 */
	public function testGetInputModelIsMarkDown()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBForm::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->getInputModelIsMarkDown();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
		$this->assertEquals([$pInstance, 'callbackValueInputModelIsMarkDown'], $pInputModelDB->getValueCallback());
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getInputModelIsHiddenField
	 */
	public function testGetInputModelIsHiddenField()
	{
		$pInputModelIsHiddenField = $this->_pInstance->getInputModelIsHiddenField();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelIsHiddenField);
		$this->assertEquals($pInputModelIsHiddenField->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createSearchFieldForFieldLists
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getFieldsCollection
	 */
	public function testCreateSearchFieldForFieldLists()
	{
		$this->_pInstance->setFormType('address');
		$pInputModelDB = $this->_pInstance->createSearchFieldForFieldLists('address', 'searchFieldForFieldLists');

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'searchFieldForFieldLists');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelCharacteristic
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::fetchDataTypesOfActionAndCharacteristics
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::generate
	 */
	public function testCreateInputModelCharacteristic()
	{
		$this->_pInstance->setFormType('contact');

		$this->_pInstance->generate('test');
		$pInputModelDB = $this->_pInstance->createInputModelCharacteristic();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals(['invoiceOpen', 'invoiceOpen2'], $pInputModelDB->getValuesAvailable());
		$this->assertTrue($pInputModelDB->getIsMulti());
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select2');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelActionKind
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::fetchDataTypesOfActionAndCharacteristics
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::generate
	 */
	public function testCreateInputModelActionKind()
	{
		$data = [
			'' => "Please choose",
			'Immofeedback / Terminnachbereitung' => "Immofeedback / Terminnachbereitung",
			'Termin' => "Termin",
			'AGB bestätigt' => "AGB bestätigt",
			'Kaufpreisangebot' => "Kaufpreisangebot",
			'Widerruf bestätigt' => "Widerruf bestätigt",
			'Aufgabe' => 'Aufgabe'
		];
		$this->_pInstance->setFormType('contact');

		$this->_pInstance->generate('test');
		$pInputModelDB = $this->_pInstance->createInputModelActionKind();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($data, $pInputModelDB->getValuesAvailable());
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelActionType
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::fetchDataTypesOfActionAndCharacteristics
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::generate
	 */
	public function testCreateInputModelActionType()
	{
		$this->_pInstance->setFormType('contact');

		$this->_pInstance->generate('test');
		$pInputModelDB = $this->_pInstance->createInputModelActionType();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelWriteActivity
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::fetchDataTypesOfActionAndCharacteristics
	 */
	public function testCreateInputModelWriteActivity()
	{
		$this->_pInstance->setFormType('contact');

		$pInputModelDB = $this->_pInstance->createInputModelWriteActivity();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelRemark
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::fetchDataTypesOfActionAndCharacteristics
	 */
	public function testCreateInputModelRemark()
	{
		$this->_pInstance->setFormType('contact');

		$pInputModelDB = $this->_pInstance->createInputModelRemark();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'textarea');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelOriginContact
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::fetchDataTypesOfActionAndCharacteristics
	 */
	public function testCreateInputModelOriginContact()
	{
		$this->_pInstance->setFormType('contact');

		$this->_pInstance->generate('test');
		$pInputModelDB = $this->_pInstance->createInputModelOriginContact();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelSubject
	 */
	public function testCreateInputModelSubject()
	{
		$pInputModelDB = $this->_pInstance->createInputModelSubject();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'emailSubject');
	}

	/**
	 *
	 */
	private function getResponseFieldCharacteristic()
	{
		$responseStr = '
		{
			"actionid": "urn:onoffice-de-ns:smart:2.5:smartml:action:get",
			"resourceid": "",
			"resourcetype": "fields",
			"cacheable": true,
			"identifier": "",
			"data": {
				"meta": {
				  "cntabsolute": null
				},
				"records": [
				  {
					"id": "agentsLog",
					"type": "",
					"elements": {
					  "merkmal": {
						"type": "multiselect",
						"length": null,
						"permittedvalues": [
						  "invoiceOpen",
						  "invoiceOpen2"
						],
						"default": null,
						"filters": [],
						"dependencies": [],
						"compoundFields": []
					  },
					  "HerkunftKontakt": {
						"type": "multiselect",
						"length": null,
						"permittedvalues": [
						  "invoiceOpen",
						  "invoiceOpen2"
						],
						"default": null,
						"filters": [],
						"dependencies": [],
						"compoundFields": []
					  }
					}
				  }
				]
			  },
			"status": {
				"errorcode": 0,
				"message": "OK"
			}
		}';

		return json_decode($responseStr, true);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelEnableCreateTask
	 */
	public function testCreateInputModelEnableCreateTask()
	{
		$this->_pInstance->setFormType('contact');

		$pInputModelDB = $this->_pInstance->createInputModelEnableCreateTask();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelTaskStatus
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getTasksStatus
	 */
	public function testCreateInputModelTaskStatus()
	{
		$data = [
			1 => 'Not started',
			2 => 'In process',
			3 => 'Completed',
			4 => 'Deferred',
			5 => 'Cancelled',
			6 => 'Miscellaneous',
			7 => 'Checked',
			8 => 'Need clarification'
		];
		$this->_pInstance->setFormType('contact');
		$pInputModelDB = $this->_pInstance->createInputModelTaskStatus();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($data, $pInputModelDB->getValuesAvailable());
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelTaskPriority
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getTasksPriority
	 */
	public function testCreateInputModelTaskPriority()
	{
		$data = [
			1 => 'highest',
			2 => 'high',
			3 => 'standard',
			4 => 'low',
			5 => 'lowest'
		];
		$this->_pInstance->setFormType('contact');
		$pInputModelDB = $this->_pInstance->createInputModelTaskPriority();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($data, $pInputModelDB->getValuesAvailable());
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelTaskSubject
	 */
	public function testCreateInputModelTaskSubject()
	{
		$this->_pInstance->setFormType('contact');
		$pInputModelDB = $this->_pInstance->createInputModelTaskSubject();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'text');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelTaskDescription
	 */
	public function testCreateInputModelTaskDescription()
	{
		$this->_pInstance->setFormType('contact');
		$pInputModelDB = $this->_pInstance->createInputModelTaskDescription();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'textarea');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelTaskType
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::fetchDataTypesOfActionAndCharacteristics
	 */
	public function testCreateInputModelTaskType()
	{
		$data = [
			'' => 'Please choose',
			217 => 'Objektaufnahme',
			215 => 'Angebot erstellen'
		];
		$this->_pInstance->setFormType('contact');
		$this->_pInstance->generate('test');
		$pInputModelDB = $this->_pInstance->createInputModelTaskType();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($data, $pInputModelDB->getValuesAvailable());
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelTaskResponsibility
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getSupervisorData
	 */
	public function	testCreateInputModelTaskResponsibility()
	{
		$data = [
			'' => 'Please choose',
			'duong' => 'aa, aa',
			'tester1' => 'Test first name',
			'tester2' => 'Test last name',
			'Test Username' => '(Test Username)'
		];
		$this->_pInstance->setFormType('contact');
		$this->_pInstance->generate('test');
		$pInputModelDB = $this->_pInstance->createInputModelTaskResponsibility();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($data, $pInputModelDB->getValuesAvailable());
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
		$this->assertEquals($pInputModelDB->getLabel(), 'Responsibility');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelTaskProcessor
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::getSupervisorData
	 */
	public function testCreateInputModelTaskProcessor()
	{
		$data = [
			'' => 'Please choose',
			'duong' => 'aa, aa',
			'tester1' => 'Test first name',
			'tester2' => 'Test last name',
			'Test Username' => '(Test Username)'
		];
		$this->_pInstance->setFormType('contact');
		$this->_pInstance->generate('test');
		$pInputModelDB = $this->_pInstance->createInputModelTaskProcessor();

		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($data, $pInputModelDB->getValuesAvailable());
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
		$this->assertEquals($pInputModelDB->getLabel(), 'Processor');
	}
}
