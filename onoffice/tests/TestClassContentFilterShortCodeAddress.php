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

use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeAddress;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeAddressEnvironment;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\Impressum;
use onOffice\WPlugin\Template;
use WP_UnitTestCase;

/**
 *
 * test class for ContentFilterShortCodeAddress
 *
 */

class TestClassContentFilterShortCodeAddress
	extends WP_UnitTestCase
{

	/** @var ContentFilterShortCodeAddressEnvironment */
	private $_pEnvironment = null;



	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pEnvironment = $this->getMockBuilder(ContentFilterShortCodeAddressEnvironment::class)
				->setMethods([
					'createAddressList',
					'getDataListFactory',
					'getTemplate',
					'getImpressum',
					'getPage',
					'getLogger',
				])
				->getMock();


		$pTemplateMock =  $this->getMockBuilder(Template::class)
				->setConstructorArgs(['adressList-01'])
				->getMock();

		$pDataListViewAddress = new DataListViewAddress(1, 'test');
		$pDataListViewAddress->setTemplate('adressList-01');

		$this->_pEnvironment
				->method('createAddressList')
				->will($this->returnValue($this->getMock(AddressList::class)));

		$pMockDataListViewFactoryAddress = $this->getMockBuilder(DataListViewFactoryAddress::class)
				->setMethods(['getListViewByName'])
				->disableOriginalConstructor()
				->getMock();
		$pMockDataListViewFactoryAddress->method('getListViewByName')->will
			($this->returnValue($pDataListViewAddress));

		$this->_pEnvironment
				->method('getDataListFactory')
				->will($this->returnValue($pMockDataListViewFactoryAddress));

		$this->_pEnvironment
				->method('getTemplate')
				->with('adressList-01')
				->will($this->returnValue($pTemplateMock));

		$pImpressumMock = $this->getMockBuilder(Impressum::class)
				->disableOriginalConstructor()
				->getMock();

		$this->_pEnvironment
				->method('getImpressum')
				->will($this->returnValue($pImpressumMock));

		$this->_pEnvironment
				->method('getPage')
				->will($this->returnValue(1));

	}


	/**
	 *
	 */

	public function testReplaceShortCodes()
	{
		$pTemplateMock = $this->_pEnvironment->getTemplate('adressList-01');
		$pTemplateMock->expects($this->once())->method('setImpressum')->with($this->_pEnvironment->getImpressum());
		$pTemplateMock->expects($this->once())->method('setAddressList')->with($this->getMock(AddressList::class));


		$pConfigFilterShortCodeAddress = new ContentFilterShortCodeAddress($this->_pEnvironment);
		$result = $pConfigFilterShortCodeAddress->replaceShortCodes(['view' => 'adressList-01']);
		$this->assertEquals('', $result);
	}
}