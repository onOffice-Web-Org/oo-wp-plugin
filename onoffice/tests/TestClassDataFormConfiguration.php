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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Form;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassDataFormConfiguration
	extends WP_UnitTestCase
{
	/** @var DataFormConfiguration */
	private $_pDataFormConfiguration = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pDataFormConfiguration = new DataFormConfiguration();
		$pDataFormConfiguration->addInput('test1');
		$pDataFormConfiguration->addInput('test2', onOfficeSDK::MODULE_ESTATE);
		$pDataFormConfiguration->addInput('test3', onOfficeSDK::MODULE_ESTATE);
		$pDataFormConfiguration->addRequiredField('test2');
		$pDataFormConfiguration->setCaptcha(true);
		$pDataFormConfiguration->setFormName('testformname');
		$pDataFormConfiguration->setFormType(Form::TYPE_INTEREST);
		$pDataFormConfiguration->setLanguage('ENG');
		$pDataFormConfiguration->setTemplate('test/testtemplate.php');
		$pDataFormConfiguration->setAvailableOptionsFields(['test1', 'test2']);
		$pDataFormConfiguration->addAvailableOptionsField('test3');
		$pDataFormConfiguration->setId(3);
		$pDataFormConfiguration->setShowEstateContext(true);

		$this->_pDataFormConfiguration = $pDataFormConfiguration;
	}


	/**
	 *
	 */

	public function testGetterSetter()
	{
		$pDataFormConfiguration = $this->_pDataFormConfiguration;
		$inputsExpectation = [
			'test1' => null,
			'test2' => onOfficeSDK::MODULE_ESTATE,
			'test3' => onOfficeSDK::MODULE_ESTATE,
		];

		$this->assertEquals($inputsExpectation, $pDataFormConfiguration->getInputs());
		$this->assertTrue($pDataFormConfiguration->getCaptcha());
		$pDataFormConfiguration->setCaptcha(false);
		$this->assertFalse($pDataFormConfiguration->getCaptcha());
		$this->assertEquals('testformname', $pDataFormConfiguration->getFormName());
		$this->assertEquals(Form::TYPE_INTEREST, $pDataFormConfiguration->getFormType());
		$this->assertEquals('ENG', $pDataFormConfiguration->getLanguage());
		$this->assertEquals(['test2'], $pDataFormConfiguration->getRequiredFields());
		$this->assertEquals('test/testtemplate.php', $pDataFormConfiguration->getTemplate());
		$this->assertEquals(['test1', 'test2', 'test3'],
			$pDataFormConfiguration->getAvailableOptionsFields());
		$this->assertEquals(3, $pDataFormConfiguration->getId());
		$this->assertEquals(Form::TYPE_INTEREST, $pDataFormConfiguration->getViewType());
		$this->assertEquals('form', $pDataFormConfiguration->getModule());
		$this->assertTrue($pDataFormConfiguration->getShowEstateContext());
	}


	/**
	 *
	 */

	public function testSetInputs()
	{
		$pDataFormConfiguration = $this->_pDataFormConfiguration;

		$newInputs = [
			'test5' => onOfficeSDK::MODULE_SEARCHCRITERIA,
			'test6' => onOfficeSDK::MODULE_ESTATE,
			'test7' => null,
		];

		$pDataFormConfiguration->setInputs($newInputs);
		$this->assertEquals($newInputs, $pDataFormConfiguration->getInputs());
	}


	/**
	 *
	 */

	public function testSetDefaultFields()
	{
		$pDataFormConfiguration = new DataFormConfiguration();

		// does nothing in this class
		$pDataFormConfiguration->setDefaultFields();
		$this->assertEquals([], $pDataFormConfiguration->getInputs());
		$this->assertEquals([], $pDataFormConfiguration->getAvailableOptionsFields());
		$this->assertFalse($pDataFormConfiguration->getShowEstateContext());
	}
}
