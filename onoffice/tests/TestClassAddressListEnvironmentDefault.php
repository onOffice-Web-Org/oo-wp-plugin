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

namespace onOffice\tests;

use onOffice\WPlugin\API\DataViewToAPI\DataListViewAddressToAPIParameters;
use onOffice\WPlugin\Controller\AddressListEnvironmentDefault;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Field\OutputFields;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\ViewFieldModifier\ViewFieldModifierHandler;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassAddressListEnvironmentDefault
	extends WP_UnitTestCase
{
	/** @var AddressListEnvironmentDefault */
	private $_pEnvironment = null;

	/** @var DataListViewAddress */
	private $_pListView = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pEnvironment = new AddressListEnvironmentDefault();
		$this->_pListView = new DataListViewAddress(3, 'testList');
		$this->_pListView->setFields([
			'imageUrl',
			'testField1',
		]);
	}


	/**
	 *
	 */

	public function testGetDataListViewAddressToAPIParameters()
	{
		$pDataListViewAddressToAPIParameters = $this->_pEnvironment
			->getDataListViewAddressToAPIParameters($this->_pListView);
		$this->assertInstanceOf(DataListViewAddressToAPIParameters::class,
			$pDataListViewAddressToAPIParameters);
		$this->assertEquals($this->_pListView,
			$pDataListViewAddressToAPIParameters->getDataListView());
	}


	/**
	 *
	 */

	public function testGetFieldnames()
	{
		$pFieldnames = $this->_pEnvironment->getFieldnames();
		$this->assertInstanceOf(Fieldnames::class, $pFieldnames);
	}


	/**
	 *
	 */

	public function testGetOutputFields()
	{
		$pOutputFields = $this->_pEnvironment->getOutputFields($this->_pListView);
		$this->assertInstanceOf(OutputFields::class, $pOutputFields);
	}


	/**
	 *
	 */

	public function testGetSDKWrapper()
	{
		$pSDKWrapper = $this->_pEnvironment->getSDKWrapper();
		$this->assertInstanceOf(SDKWrapper::class, $pSDKWrapper);
	}


	/**
	 *
	 */

	public function testGetViewFieldModifierHandler()
	{
		$pViewFieldModifierHandler = $this->_pEnvironment->getViewFieldModifierHandler
			($this->_pListView->getFields());
		$this->assertInstanceOf(ViewFieldModifierHandler::class, $pViewFieldModifierHandler);
	}
}
