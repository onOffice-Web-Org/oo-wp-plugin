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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModelDB;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassFormModel
	extends WP_UnitTestCase
{
	/** @var FormModel */
	private $_pFormModel = null;

	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pFormModel = new FormModel();
	}


	/**
	 *
	 */

	public function testInputModel()
	{
		$pInputModel1 = new InputModelDB('test1', 'Test1');
		$pInputModel2 = new InputModelDB('test2', 'Test1');
		$this->assertEquals([], $this->_pFormModel->getInputModel());

		$this->_pFormModel->addInputModel($pInputModel1);
		$this->assertEquals([$pInputModel1], $this->_pFormModel->getInputModel());

		$this->_pFormModel->addInputModel($pInputModel2);
		$this->assertEquals([$pInputModel1, $pInputModel2], $this->_pFormModel->getInputModel());
	}


	/**
	 *
	 */

	public function testGroupSlug()
	{
		$this->assertEquals('', $this->_pFormModel->getGroupSlug());
		$this->_pFormModel->setGroupSlug('testGroupSlug');
		$this->assertEquals('testGroupSlug', $this->_pFormModel->getGroupSlug());
	}


	/**
	 *
	 */

	public function testInvisibleForm()
	{
		$this->assertFalse($this->_pFormModel->getIsInvisibleForm());
		$this->_pFormModel->setIsInvisibleForm(true);
		$this->assertTrue($this->_pFormModel->getIsInvisibleForm());
	}


	/**
	 *
	 */

	public function testLabel()
	{
		$this->assertEquals('', $this->_pFormModel->getLabel());
		$this->_pFormModel->setLabel('testLabel');
		$this->assertEquals('testLabel', $this->_pFormModel->getLabel());
	}


	/**
	 *
	 */

	public function testOoModule()
	{
		$this->assertEquals('', $this->_pFormModel->getOoModule());
		$this->_pFormModel->setOoModule(onOfficeSDK::MODULE_ESTATE);
		$this->assertEquals(onOfficeSDK::MODULE_ESTATE, $this->_pFormModel->getOoModule());
	}


	/**
	 *
	 */

	public function testPageSlug()
	{
		$this->assertEquals('', $this->_pFormModel->getPageSlug());
		$this->_pFormModel->setPageSlug('testPageSlug');
		$this->assertEquals('testPageSlug', $this->_pFormModel->getPageSlug());
	}


	/**
	 *
	 */

	public function testTextCallback()
	{
		$pClosure1 = $this->_pFormModel->getTextCallback();
		$this->assertNull($pClosure1());

		$this->_pFormModel->setTextCallback(function() { return 'Hello'; });
		$pClosure2 = $this->_pFormModel->getTextCallback();
		$this->assertEquals('Hello', $pClosure2());
	}
}
