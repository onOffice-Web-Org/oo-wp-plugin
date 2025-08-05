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
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBAddress;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigAddress;
use onOffice\WPlugin\Model\InputModelDB;
use WP_UnitTestCase;
use onOffice\WPlugin\Installer\DatabaseChanges;
use onOffice\WPlugin\WP\WPOptionWrapperTest;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Types\Field;

class TestClassFormModelBuilderDBAddress
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/** @var InputModelDBFactory */
	private $_pInputModelFactoryDBEntry;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;

	/** @var FormModelBuilderDBAddress */
	private $_pFormModelBuilderDBAddressSettings;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pInputModelFactoryDBEntry = new InputModelDBFactory(new InputModelDBFactoryConfigAddress);
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$this->_pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
				->onlyMethods(['addFieldsAddressEstate', 'addFieldsEstateDecoratorReadAddressBackend'])
				->setConstructorArgs([$this->_pContainer])
				->getMock();
		$this->_pContainer->set(FieldsCollectionBuilderShort::class, $this->_pFieldsCollectionBuilderShort);
		$this->_pFieldsCollectionBuilderShort->method('addFieldsAddressEstate')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('fax1', onOfficeSDK::MODULE_ADDRESS);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));
		$this->_pFieldsCollectionBuilderShort->method('addFieldsEstateDecoratorReadAddressBackend')
			->with($this->anything())
			->will($this->returnCallback(function(FieldsCollection $pFieldsCollection): FieldsCollectionBuilderShort {
				$pField1 = new Field('fax2', onOfficeSDK::MODULE_ADDRESS);
				$pFieldsCollection->addField($pField1);

				return $this->_pFieldsCollectionBuilderShort;
			}));
		$this->_pFormModelBuilderDBAddressSettings = new FormModelBuilderDBAddress($this->_pContainer);
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBAddress::createInputModelFilter
	 */
	public function testCreateInputModelFilter()
	{
		global $wpdb;

		$pWpOption = new WPOptionWrapperTest();
		$pDbChanges = new DatabaseChanges($pWpOption, $wpdb);
		$pDbChanges->install();
		$pInstance = $this->getMockBuilder(FormModelBuilderDBAddress::class)
			->disableOriginalConstructor()
			->onlyMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();
		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelFilter();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBAddress::createInputModelPictureTypes
	 */
	public function testCreateInputModelPictureTypes()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBAddress::class)
			->disableOriginalConstructor()
			->onlyMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();
		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelPictureTypes();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBAddress::createSearchFieldForFieldLists
	 */
	public function testCreateSearchFieldForFieldLists()
	{
		$pInputModelDB = $this->_pFormModelBuilderDBAddressSettings->createSearchFieldForFieldLists('address', 'searchFieldForFieldLists');
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'searchFieldForFieldLists');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBAddress::createInputModelRecordsPerPage
	 */
	public function testCreateInputModelRecordsPerPage()
	{
		$pInputModelDB = $this->_pFormModelBuilderDBAddressSettings->createInputModelRecordsPerPage();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'number');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBAddress::getInputModelConvertInputTextToSelectField
	 */
	public function testGetInputModelConvertInputTextToSelectField()
	{
		$pInputModelDB = $this->_pFormModelBuilderDBAddressSettings->getInputModelConvertInputTextToSelectField();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals(InputModelBase::HTML_TYPE_CHECKBOX, $pInputModelDB->getHtmlType());
		$this->assertEquals([$this->_pFormModelBuilderDBAddressSettings, 'callbackValueInputModelConvertInputTextToSelectForField'], $pInputModelDB->getValueCallback());
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBAddress::createInputModelBildWebseite
	 */
	public function testCreateInputModelBildWebseite()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBAddress::class)
			->disableOriginalConstructor()
			->onlyMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();
		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelBildWebseite();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'checkbox');
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBAddress::getInputModelCustomLabelLanguageSwitch
	 */
	public function testGetInputModelCustomLabelLanguageSwitch()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBAddress::class)
		                  ->disableOriginalConstructor()
		                  ->addMethods(['readAvailableLanguageNamesUsingNativeName'])
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
}
