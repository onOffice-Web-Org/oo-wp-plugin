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

use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelLabel;
use WP_UnitTestCase;

class TestClassFormModelBuilderDBForm
	extends WP_UnitTestCase
{


	/** @var InputModelDBFactory */
	private $_pInputModelFactoryDBEntry;

	private $_pInstance;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pInputModelFactoryDBEntry = new InputModelDBFactory(new InputModelDBFactoryConfigForm);

		$this->_pInstance = $this->getMockBuilder(FormModelBuilderDBForm::class)
			->disableOriginalConstructor()
			->setMethods(['getInputModelDBFactory', 'getValue'])
			->getMock();

		$this->_pInstance->method('getInputModelDBFactory')->willReturn($this->_pInputModelFactoryDBEntry);
		$this->_pInstance->method('getValue')->willReturn('1');
	}


	/**
	 * @covers onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm::createInputModelRecipientContactForm
	 */
	public function testCreateInputModelRecipientContactForm()
	{
		$pInputModelDB = $this->_pInstance->createInputModelRecipientContactForm();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelDB);
		$this->assertEquals($pInputModelDB->getHtmlType(), 'text');
	}

	public function testCreateInputModelName()
	{

		$pInputModelName = $this->_pInstance->createInputModelName();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelName);
		$this->assertEquals($pInputModelName->getHtmlType(), 'text');
	}


	public function testCreateInputModelEmbedCode()
	{
		$pInputModelFormEmbedCode = $this->_pInstance->createInputModelEmbedCode();
		$this->assertInstanceOf(InputModelLabel::class, $pInputModelFormEmbedCode);
		$this->assertEquals($pInputModelFormEmbedCode->getHtmlType(), 'label');
	}

	public function testCreateInputModelButton()
	{
		$pInputModelButton = $this->_pInstance->createInputModelButton();
		$this->assertInstanceOf(InputModelLabel::class, $pInputModelButton);
		$this->assertEquals($pInputModelButton->getHtmlType(), 'button');
	}

	public function testCreateInputModelResultLimit()
	{
		$pInputModelResultLimit = $this->_pInstance->createInputModelResultLimit();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelResultLimit);
		$this->assertEquals($pInputModelResultLimit->getHtmlType(), 'text');
	}

	public function testCreateInputModelRecipient()
	{
		$pInputModelRecipient = $this->_pInstance->createInputModelRecipient();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelRecipient);
		$this->assertEquals($pInputModelRecipient->getHtmlType(), 'text');
	}

	public function testGetInputModelIsRequired()
	{
		$pInputModelIsRequired = $this->_pInstance->getInputModelIsRequired();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelIsRequired);
		$this->assertEquals($pInputModelIsRequired->getHtmlType(), 'checkbox');
	}

	public function testGetInputModelIsAvailableOptions()
	{
		$pInputModelIsAvailableOptions = $this->_pInstance->getInputModelIsAvailableOptions();
		$this->assertInstanceOf(InputModelDB::class, $pInputModelIsAvailableOptions);
		$this->assertEquals($pInputModelIsAvailableOptions->getHtmlType(), 'checkbox');
	}


}