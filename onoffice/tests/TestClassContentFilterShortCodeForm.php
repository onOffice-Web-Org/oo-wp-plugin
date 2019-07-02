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

use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeForm;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationContact;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\Field\DistinctFieldsChecker;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\Utility\Logger;
use WP_UnitTestCase;

/**
 *
 */

class TestClassContentFilterShortCodeForm
	extends WP_UnitTestCase
{
	/** @var ContentFilterShortCodeForm */
	private $_pContentFilterShortCodeForm = null;


	/**
	 *
	 */

	public function testDefaultConstructor()
	{
		$pInstance = new ContentFilterShortCodeForm();
		$pTemplate = $pInstance->getTemplate();
		$this->assertInstanceOf(Template::class, $pTemplate);
		$this->assertInstanceOf(Logger::class, $pInstance->getLogger());
		$this->assertInstanceOf(DataFormConfigurationFactory::class, $pInstance->getDataFormConfigurationFactory());
		$this->assertInstanceOf(DistinctFieldsChecker::class, $pInstance->getDistinctFieldsChecker());
		$this->assertInstanceOf(Form\FormBuilder::class, $pInstance->getFormBuilder());
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pTemplate = $this->getMockBuilder(Template::class)
			->setConstructorArgs([''])
			->setMethods(['render', 'getImpressum'])
			->getMock();
		$pTemplate->method('render')->will($this->returnValue('testResult'));
		$pDataFormConfigurationFactory = $this->getMockBuilder(DataFormConfigurationFactory::class)
			->getMock();
		$pLogger = $this->getMockBuilder(Logger::class)
			->getMock();
		$pDistinctFieldsChecker = $this->getMockBuilder(DistinctFieldsChecker::class)
			->getMock();
		$pFormBuilder = $this->getMockBuilder(Form\FormBuilder::class)
			->getMock();
		$this->_pContentFilterShortCodeForm = new ContentFilterShortCodeForm
			($pTemplate, $pDataFormConfigurationFactory, $pLogger, $pDistinctFieldsChecker, $pFormBuilder);
	}


	/**
	 *
	 */

	public function testReplaceShortCodes()
	{
		$pDataFormConfiguration = new DataFormConfigurationContact();
		$pDataFormConfiguration->setFormType(Form::TYPE_APPLICANT_SEARCH);
		$pDataFormConfiguration->setAvailableOptionsFields(['asdf']);
		$pDistinctFieldsChecker = $this->_pContentFilterShortCodeForm->getDistinctFieldsChecker();
		$pDistinctFieldsChecker
			->expects($this->once())
			->method('registerScripts')
			->with(onOfficeSDK::MODULE_SEARCHCRITERIA, ['asdf']);
		$pDataFormConfigurationFactory = $this->_pContentFilterShortCodeForm->getDataFormConfigurationFactory();
		$pDataFormConfigurationFactory
			->expects($this->once())
			->method('loadByFormName')
			->with('testcontactform')
			->will($this->returnValue($pDataFormConfiguration));

		$pFormBuilder = $this->_pContentFilterShortCodeForm->getFormBuilder();
		$pForm = $this->getMockBuilder(Form::class)
			->disableOriginalConstructor()
			->getMock();
		$pFormBuilder->expects($this->once())
			->method('build')->with('testcontactform', 'applicantsearch')
			->will($this->returnValue($pForm));

		$this->assertEquals('testResult',
			$this->_pContentFilterShortCodeForm->replaceShortCodes(['form' => 'testcontactform']));
	}


	/**
	 *
	 */

	public function testReplaceShortCodesWithError()
	{
		$pDataFormConfigFactory = $this->_pContentFilterShortCodeForm->getDataFormConfigurationFactory();
		$pException = new Exception('error');
		$pDataFormConfigFactory->expects($this->once())->method('loadByFormName')->with($this->anything())
			->will($this->throwException($pException));
		$pLogger = $this->_pContentFilterShortCodeForm->getLogger();
		$pLogger->expects($this->once())->method('logErrorAndDisplayMessage')->with($pException)
			->will($this->returnValue('Got Exception'));
		$this->assertEquals('Got Exception',
			$this->_pContentFilterShortCodeForm->replaceShortCodes(['form' => 'unknown']));
	}


	/**
	 *
	 */

	public function testGetTag()
	{
		$this->assertEquals('oo_form', $this->_pContentFilterShortCodeForm->getTag());
	}
}
