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

use DI\Container;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Form\FormAddressCreator;
use onOffice\WPlugin\Form\FormPostOwnerConfigurationDefault;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;

/**
 *
 */

class TestClassFormPostOwnerConfigurationDefault
	extends WP_UnitTestCase
{
	/** @var FormPostOwnerConfigurationDefault */
	private $_pSubject = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSubject = new FormPostOwnerConfigurationDefault
			(new SDKWrapper(), new FormAddressCreator(new SDKWrapper,
				new FieldsCollectionBuilderShort(new Container())));
	}


	/**
	 *
	 */

	public function testGetEstateListInputVariableReader()
	{
		$pVariableReader = $this->_pSubject->getEstateListInputVariableReader();
		$this->assertInstanceOf(InputVariableReader::class, $pVariableReader);
		$this->assertEquals(onOfficeSDK::MODULE_ESTATE, $pVariableReader->getModule());
	}


	/**
	 *
	 */

	public function testGetReferrer()
	{
		$this->assertEquals('', $this->_pSubject->getReferrer());
	}


	/**
	 *
	 */

	public function testGetSDKWrapper()
	{
		$this->assertInstanceOf(SDKWrapper::class, $this->_pSubject->getSDKWrapper());
	}


	/**
	 *
	 */

	public function testGetFormAddressCreator()
	{
		$this->assertInstanceOf(FormAddressCreator::class, $this->_pSubject->getFormAddressCreator());
	}
}
