<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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
use WP_UnitTestCase;
use DI\ContainerBuilder;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Field\FieldnamesEnvironmentTest;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings;

class TestClassFormModelBuilderAddressDetailSettings
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var FieldnamesEnvironmentTest */
	private $_pFieldnamesEnvironment = null;

	/** @var FormModelBuilderAddressDetailSettings */
	private $_pFormModelBuilderAddressDetailSettings;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pFieldnamesEnvironment = new FieldnamesEnvironmentTest();
		$fieldParameters = [
				'labels' => true,
				'showContent' => true,
				'showTable' => true,
				'language' => 'ENG',
				'modules' => ['address', 'estate'],
				'realDataTypes' => true,
		];
		$pSDKWrapperMocker = $this->_pFieldnamesEnvironment->getSDKWrapper();
		$responseGetFields = json_decode
		(file_get_contents(__DIR__ . '/resources/ApiResponseGetFields.json'), true);
		/* @var $pSDKWrapperMocker SDKWrapperMocker */
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'fields', '', $fieldParameters, null, $responseGetFields);
		$this->_pFieldnames = new Fieldnames(new FieldsCollection(), false, $this->_pFieldnamesEnvironment);

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();

		$this->_pFormModelBuilderAddressDetailSettings = new FormModelBuilderAddressDetailSettings($this->_pContainer, $this->_pFieldnames);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::__construct
	 */
	
	public function testConstruct()
	{
		$pInstance = $this->_pFormModelBuilderAddressDetailSettings;
		$this->assertInstanceOf(FormModelBuilderAddressDetailSettings::class, $pInstance);
	}
	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::createSortableFieldList
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::getInputModelCustomLabel
	 */
	public function testCreateSortableFieldList()
	{
		$pFormModelBuilderAddressDetailSettings = $this->_pFormModelBuilderAddressDetailSettings;
		$pFormModelBuilderAddressDetailSettings->generate('test');
		$pInputModelOption = $pFormModelBuilderAddressDetailSettings->createSortableFieldList('address', 'complexSortableDetailList');

		$this->assertInstanceOf(InputModelOption::class, $pInputModelOption);
		$this->assertNotEmpty($pInputModelOption->getValuesAvailable());
		$this->assertEquals($pInputModelOption->getHtmlType(), 'complexSortableDetailList');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::createSearchFieldForFieldLists
	 */
	public function testCreateSearchFieldForFieldLists()
	{
		$pFormModelBuilderAddressDetailSettings = $this->_pFormModelBuilderAddressDetailSettings;
		$pFormModelBuilderAddressDetailSettings->generate('test');
		$pInputModelOption = $pFormModelBuilderAddressDetailSettings->createSearchFieldForFieldLists('address', 'searchFieldForFieldLists');

		$this->assertInstanceOf(InputModelOption::class, $pInputModelOption);
		$this->assertNotEmpty($pInputModelOption->getValuesAvailable());
		$this->assertEquals($pInputModelOption->getHtmlType(), 'searchFieldForFieldLists');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::createInputModelPictureTypes
	 */
	public function testCreateInputModelPictureTypes()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderAddressDetailSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue'])
			->getMock();
		$pInstance->generate('test');

		$pInputModelDB = $pInstance->createInputModelPictureTypes();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::CreateInputModelTemplate
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::getTemplateValueByField
	 */
	public function testCreateInputModelTemplate()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderAddressDetailSettings::class)
			->disableOriginalConstructor()
			->setMethods(['getValue', 'readTemplatePaths'])
			->getMock();
		$pInstance->expects($this->exactly(1))
			->method('readTemplatePaths');
		$pInstance->generate('test');

		$pInputModelDB = $pInstance->createInputModelTemplate();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'templateList');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::createInputModelFieldsConfigByCategory
	 */
	public function testCreateInputModelFieldsConfigByCategory()
	{
		$inputModel = $this->_pFormModelBuilderAddressDetailSettings->createInputModelFieldsConfigByCategory('1', ['field1'], 'label');
		$this->assertEquals('label', $inputModel->getLabel());
		$this->assertEquals('1', $inputModel->getId());
		$this->assertEquals(['field1'], $inputModel->getValuesAvailable());
		$this->assertInstanceOf(InputModelOption::class, $inputModel);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::getInputModelCustomLabelLanguageSwitch
	 */
	public function testGetInputModelCustomLabelLanguageSwitch()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderAddressDetailSettings::class)
		                  ->disableOriginalConstructor()
		                  ->setMethods(['readAvailableLanguageNamesUsingNativeName'])
		                  ->getMock();
						  
		$inputModel = $pInstance->getInputModelCustomLabelLanguageSwitch();
		$this->assertInstanceOf(InputModelDB::class, $inputModel);
		$this->assertEquals('Add custom label language', $inputModel->getLabel());
		$this->assertEquals('language-custom-label', $inputModel->getTable());
		$this->assertEquals('language', $inputModel->getField());

		$values = $inputModel->getValuesAvailable();

		$this->assertContains('Choose Language', $values);
		$this->assertNotContains(get_locale(), $values);
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::generate
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::createButtonModelFieldsConfigByCategory
	 */
	public function testCreateButtonModelFieldsConfigByCategory()
	{
		$this->_pFormModelBuilderAddressDetailSettings->generate('test');
		$inputModel = $this->_pFormModelBuilderAddressDetailSettings->createButtonModelFieldsConfigByCategory('1', ['field1'], 'label');
		$this->assertEquals('label', $inputModel->getLabel());
		$this->assertEquals('1', $inputModel->getId());
		$this->assertEquals(['field1'], $inputModel->getValuesAvailable());
		$this->assertInstanceOf(InputModelOption::class, $inputModel);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings::createInputModelShortCodeForm
	 */
	public function testCreateInputModelShortCodeForm()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderAddressDetailSettings::class)
			  ->disableOriginalConstructor()
			  ->setMethods(['getValue', 'readNameShortCodeForm'])
			  ->getMock();
		$pInstance->expects($this->exactly(1))
				  ->method('readNameShortCodeForm');

		$pInstance->generate('test');
		$pInputModelDB = $pInstance->createInputModelShortCodeForm();
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}
}
