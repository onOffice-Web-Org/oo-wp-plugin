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
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeAddress;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeAddressEnvironment;
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\WPQueryWrapper;
use WP_Query;
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
				'getWPQueryWrapper',
				'getLogger',
			])
			->getMock();

		$pTemplateMock = $this->getMockBuilder(Template::class)
			->setMethods(['render', 'setAddressList', 'withTemplateName'])
			->setConstructorArgs(['adressList-01'])
			->getMock();
		$pTemplateMock->method('withTemplateName')->will($this->returnSelf()); // should be a clone!
		$pLogger = $this->getMockBuilder(Logger::class)
			->setMethods(['logErrorAndDisplayMessage'])
			->getMock();
		$this->_pEnvironment->method('getLogger')->will($this->returnValue($pLogger));

		$pDataListViewAddress = new DataListViewAddress(1, 'test');
		$pDataListViewAddress->setTemplate('adressList-01');
		$pDataListViewAddress->setFilterableFields(['Ort']);

		$this->_pEnvironment->method('createAddressList')
			->will($this->returnValue($this->getMockBuilder(AddressList::class)->getMock()));

		$pMockDataListViewFactoryAddress = $this->getMockBuilder(DataListViewFactoryAddress::class)
			->setMethods(['getListViewByName'])
			->disableOriginalConstructor()
			->getMock();
		$pMockDataListViewFactoryAddress->method('getListViewByName')->will
			($this->returnValue($pDataListViewAddress));

		$this->_pEnvironment->method('getDataListFactory')
			->will($this->returnValue($pMockDataListViewFactoryAddress));
		$this->_pEnvironment->method('getTemplate')->will($this->returnValue($pTemplateMock));

		$pWPQueryWrapperMock = $this->getMockBuilder(WPQueryWrapper::class)
				->getMock();
		$pWPQueryMock = $this->getMockBuilder(WP_Query::class)->getMock();
		$pWPQueryMock->method('get')
			->with('page', 1)->will($this->returnValue(2));
			$pWPQueryWrapperMock->method('getWPQuery')->will($this->returnValue($pWPQueryMock));
		$this->_pEnvironment->method('getWPQueryWrapper')->will($this->returnValue
			($pWPQueryWrapperMock));
	}


	/**
	 *
	 */

	public function testReplaceShortCodes()
	{
		$pTemplateMock = $this->_pEnvironment->getTemplate();
		$pTemplateMock->method('render')->will($this->returnValue('I am the returned text.'));

		$pTemplateMock->expects($this->once())->method('setAddressList')
			->with($this->anything());
		$pTemplateMock->expects($this->once())->method('withTemplateName')->with('adressList-01')
			->will($this->returnSelf());

		$pConfigFilterShortCodeAddress = new ContentFilterShortCodeAddress($this->_pEnvironment);
		$result = $pConfigFilterShortCodeAddress->replaceShortCodes(['view' => 'adressList-01']);
		$this->assertEquals('I am the returned text.', $result);
	}


	/**
	 *
	 */

	public function testReplaceShortCodesException()
	{
		$pTemplateMock = $this->_pEnvironment->getTemplate();
		$pException = new Exception(__CLASS__);
		$pTemplateMock->expects($this->once())->method('render')->will($this->throwException($pException));
		$pLogger = $this->_pEnvironment->getLogger();
		$pLogger->expects($this->once())->method('logErrorAndDisplayMessage')
			->with($pException)->will($this->returnValue('Exception caught'));
		$pConfigFilterShortCodeAddress = new ContentFilterShortCodeAddress($this->_pEnvironment);
		$result = $pConfigFilterShortCodeAddress->replaceShortCodes(['view' => 'testException']);
		$this->assertEquals('Exception caught', $result);
	}


	/**
	 *
	 */

	public function testGetTag()
	{
		$pConfigFilterShortCodeAddress = new ContentFilterShortCodeAddress($this->_pEnvironment);
		$this->assertEquals('oo_address', $pConfigFilterShortCodeAddress->getTag());
	}
}