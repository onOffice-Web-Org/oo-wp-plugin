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

class TestClassFormModelBuilderDBForm
	extends WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/** @var InputModelDBFactory */
	private $_pInputModelFactoryDBEntry;

	/** @var FormModelBuilderDBForm */
	private $_pInstance;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pInputModelFactoryDBEntry = new InputModelDBFactory(new InputModelDBFactoryConfigForm);
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
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
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::CreateInputModelFieldsConfigByCategory
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
}
