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
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use WP_UnitTestCase;

class TestClassFormModelBuilderDBForm
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/** @var InputModelDBFactory */
	private $_pInputModelFactoryDBEntry;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pInputModelFactoryDBEntry = new InputModelDBFactory(new InputModelDBFactoryConfigForm);
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
	}

	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelRecipientContactForm
	 */
	public function testCreateInputModelRecipientContactForm()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBForm::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelRecipientContactForm();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'text');
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
		$this->assertEquals($pInputModelDB->getHtmlType(), 'select');
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
			->setMethods([ 'buildFieldsCollection'])
			->getMock();

		$ex = $this->_pContainer->get(APIClientCredentialsException::class);


		$fieldCollectionBuilder->method('buildFieldsCollection')->will($this->throwException($ex));

		$this->_pContainer->set(FieldsCollectionBuilder::class, $fieldCollectionBuilder);

		$pInstance = $this->_pContainer->get(FormModelBuilderDBForm::class);

		$result = $pInstance->getDataContactType('address');

		$this->assertEquals([], $result);
	}

	public function testCreateInputModelRecipient()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBForm::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelRecipient();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'text');
	}

	public function testCreateInputModelName()
	{
		$pInstance = $this->getMockBuilder(FormModelBuilderDBForm::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();

		$pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$pInstance->method('getValue')->willReturn('1');

		$pInputModelDB = $pInstance->createInputModelName();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'text');
	}
}
