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
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeForm;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationContact;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
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

	/** @var Logger */
	private $_pLogger = null;

	/** @var DataFormConfigurationFactory */
	private $_pDataFormConfigurationFactory = null;

	/** @var Form\FormBuilder */
	private $_pFormBuilder = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pTemplate = $this->getMockBuilder(Template::class)
			->setMethods(['render', 'getImpressum'])
			->getMock();
		$pTemplate->method('render')->willReturn('testResult');
		$this->_pDataFormConfigurationFactory = $this->getMockBuilder(DataFormConfigurationFactory::class)
			->getMock();
		$this->_pLogger = $this->getMockBuilder(Logger::class)
			->getMock();

		$this->_pFormBuilder = $this->getMockBuilder(Form\FormBuilder::class)
			->disableOriginalConstructor()
			->getMock();
		$this->_pContentFilterShortCodeForm = new ContentFilterShortCodeForm
			($pTemplate, $this->_pDataFormConfigurationFactory, $this->_pLogger, $this->_pFormBuilder);
	}

	public function testReplaceShortCodes()
	{
		$pDataFormConfiguration = new DataFormConfigurationContact();
		$pDataFormConfiguration->setFormType(Form::TYPE_APPLICANT_SEARCH);
		$pDataFormConfiguration->setAvailableOptionsFields(['asdf']);
		$this->_pDataFormConfigurationFactory
			->expects($this->once())
			->method('loadByFormName')
			->with('testcontactform')
			->willReturn($pDataFormConfiguration);

		$pForm = $this->getMockBuilder(Form::class)
			->disableOriginalConstructor()
			->getMock();
		$this->_pFormBuilder->expects($this->once())
			->method('build')->with('testcontactform', 'applicantsearch')
			->willReturn($pForm);

		$this->assertEquals('testResult',
			$this->_pContentFilterShortCodeForm->replaceShortCodes(['form' => 'testcontactform']));
	}

	public function testReplaceShortCodesWithError()
	{
		$pException = new Exception('error');
		$this->_pDataFormConfigurationFactory->expects($this->once())->method('loadByFormName')->with($this->anything())
			->will($this->throwException($pException));
		$this->_pLogger->expects($this->once())->method('logErrorAndDisplayMessage')->with($pException)
			->willReturn('Got Exception');
		$this->assertEquals('Got Exception',
			$this->_pContentFilterShortCodeForm->replaceShortCodes(['form' => 'unknown']));
	}

	public function testGetTag()
	{
		$this->assertEquals('oo_form', $this->_pContentFilterShortCodeForm->getTag());
	}
}
