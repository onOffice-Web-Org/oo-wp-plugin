<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

use onOffice\WPlugin\Model\InputModel\InputModelConfiguration;
use onOffice\WPlugin\Model\InputModel\InputModelConfigurationFormContact;
use onOffice\WPlugin\Model\InputModel\InputModelDBBuilderGeneric;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use onOffice\WPlugin\Model\InputModelDB;
use WP_UnitTestCase;

/**
 *
 */

class TestClassInputModelDBBuilderGeneric
	extends WP_UnitTestCase
{
	/** @var InputModelDBBuilderGeneric */
	private $_pInputModelDBBuilderGeneric = null;

	/** @var InputModelDBFactory */
	private $_pInputModelDBFactory = null;

	/** @var InputModelConfiguration */
	private $_pInputModelConfiguration = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pInputModelDBFactory = $this->getMockBuilder(InputModelDBFactory::class)
			->setConstructorArgs([new InputModelDBFactoryConfigForm()])
			->getMock();

		$this->_pInputModelDBFactory->method('create')
			->with($this->anything(), $this->anything(), false)
			->will($this->returnCallback(function(string $fieldName, string $label): InputModelDB {
				$pInputModel = new InputModelDB('testname', $label);
				$pInputModel->setField($fieldName);
				return $pInputModel;
			}));

		$this->_pInputModelConfiguration = new InputModelConfigurationFormContact();

		$this->_pInputModelDBBuilderGeneric = new InputModelDBBuilderGeneric
			($this->_pInputModelDBFactory, $this->_pInputModelConfiguration);
	}


	/**
	 *
	 */

	public function testBuildCheckBox()
	{
		$pResult = $this->_pInputModelDBBuilderGeneric->build
			(InputModelDBFactoryConfigForm::INPUT_FORM_ESTATE_CONTEXT_AS_HEADING);
		$this->assertEquals(InputModelDBFactoryConfigForm::INPUT_FORM_ESTATE_CONTEXT_AS_HEADING,
			$pResult->getField());
		$this->assertEquals('Set Estate Context as Heading', $pResult->getLabel());
		$this->assertEquals('checkbox', $pResult->getHtmlType());
		$this->assertEquals(1, $pResult->getValuesAvailable());
		$this->assertEquals(0, $pResult->getValue());
	}


	/**
	 *
	 */

	public function testBuildOther()
	{
		$pResult = $this->_pInputModelDBBuilderGeneric->build
			(InputModelDBFactoryConfigForm::INPUT_FORM_SUBJECT);
		$this->assertEquals(InputModelDBFactoryConfigForm::INPUT_FORM_SUBJECT,
			$pResult->getField());
		$this->assertEquals('Subject (optional)', $pResult->getLabel());
		$this->assertEquals('text', $pResult->getHtmlType());
		$this->assertEquals([], $pResult->getValuesAvailable());
		$this->assertEquals('', $pResult->getValue());
	}


	/**
	 *
	 */

	public function testBuildWithValue()
	{
		$this->_pInputModelDBBuilderGeneric->setValues([
			InputModelDBFactoryConfigForm::INPUT_FORM_ESTATE_CONTEXT_AS_HEADING => 1,
		]);

		$pResult = $this->_pInputModelDBBuilderGeneric->build
			(InputModelDBFactoryConfigForm::INPUT_FORM_ESTATE_CONTEXT_AS_HEADING);
		$this->assertEquals(1, $pResult->getValue());
	}


	public function testBuildWithError()
	{
		$this->expectException(\onOffice\WPlugin\Field\UnknownFieldException::class);
		$this->_pInputModelDBBuilderGeneric->build('testestasdasd');
	}


	/**
	 *
	 */

	public function testGetValues()
	{
		$values = [
			'testabc' => 1,
			'testcde' => 'asd',
		];
		$this->_pInputModelDBBuilderGeneric->setValues($values);
		$this->assertEquals($values, $this->_pInputModelDBBuilderGeneric->getValues());
	}
}
