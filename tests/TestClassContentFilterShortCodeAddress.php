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
use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\Factory\AddressListFactory;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModel;
use onOffice\WPlugin\Filter\SearchParameters\SearchParametersModelBuilder;
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
	/** @var SearchParametersModelBuilder */
	private $_pSearchParametersModelBuilder;

	/** @var Logger */
	private $_pLogger;

	/** @var DataListViewFactoryAddress */
	private $_pMockDataListViewFactoryAddress;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapperMock;

	/** @var Template */
	private $_pTemplateMock;

	/** @var AddressListFactory  */
	private $_pAddressListFactory;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pTemplateMock = $this->getMockBuilder(Template::class)
			->setMethods(['render', 'withAddressList', 'withTemplateName'])
			->getMock();
		$this->_pTemplateMock->method('withTemplateName')->will($this->returnSelf()); // should be a clone!
		$this->_pLogger = $this->getMockBuilder(Logger::class)
			->setMethods(['logErrorAndDisplayMessage'])
			->getMock();

		$pDataListViewAddress = new DataListViewAddress(1, 'test');
		$pDataListViewAddress->setTemplate('adressList-01');
		$pDataListViewAddress->setFilterableFields(['Ort']);

		$this->_pMockDataListViewFactoryAddress = $this->getMockBuilder(DataListViewFactoryAddress::class)
			->setMethods(['getListViewByName'])
			->disableOriginalConstructor()
			->getMock();
		$this->_pMockDataListViewFactoryAddress->method('getListViewByName')->will
			($this->returnValue($pDataListViewAddress));


		$this->_pWPQueryWrapperMock = $this->getMockBuilder(WPQueryWrapper::class)
				->getMock();
		$pWPQueryMock = $this->getMockBuilder(WP_Query::class)->getMock();
		$pWPQueryMock->method('get')
			->with('page', 1)->will($this->returnValue(2));
		$this->_pWPQueryWrapperMock->method('getWPQuery')->will($this->returnValue($pWPQueryMock));

		$this->_pSearchParametersModelBuilder = $this->getMockBuilder(SearchParametersModelBuilder::class)
			->setMethods(['build'])
			->disableOriginalConstructor()
			->getMock();

		$pSearchParametersModel = new SearchParametersModel();
		$this->_pSearchParametersModelBuilder->method('build')->with($this->anything())->willReturn($pSearchParametersModel);

		$this->_pAddressListFactory = $this->getMockBuilder(AddressListFactory::class)
			->setMethods(['create'])
			->disableOriginalConstructor()
			->getMock();

		$this->_pAddressListFactory->method('create')->willReturn($this->getMockBuilder(AddressList::class)->getMock());
	}


	/**
	 *
	 */

	public function testReplaceShortCodes()
	{
		$this->_pTemplateMock->method('withTemplateName')->will($this->returnSelf()); // should be a clone!
		$this->_pTemplateMock->method('render')->will($this->returnValue('I am the returned text.'));

		$this->_pTemplateMock->expects($this->once())->method('withAddressList')
			->with($this->anything())
			->will($this->returnSelf());
		$this->_pTemplateMock->expects($this->once())->method('withTemplateName')->with('adressList-01')
			->will($this->returnSelf());

		$pConfigFilterShortCodeAddress = new ContentFilterShortCodeAddress(
			$this->_pSearchParametersModelBuilder,
			$this->_pAddressListFactory,
			$this->_pLogger,
			$this->_pMockDataListViewFactoryAddress,
			$this->_pTemplateMock,
			$this->_pWPQueryWrapperMock
		);
		$result = $pConfigFilterShortCodeAddress->replaceShortCodes(['view' => 'adressList-01']);
		$this->assertEquals('I am the returned text.', $result);
	}


	/**
	 *
	 */

	public function testReplaceShortCodesException()
	{
		$this->_pTemplateMock->expects($this->once())
			->method('withAddressList')
			->will($this->returnSelf());
		$pException = new Exception(__CLASS__);
		$this->_pTemplateMock->expects($this->once())->method('render')->will($this->throwException($pException));
		$this->_pLogger->expects($this->once())->method('logErrorAndDisplayMessage')
			->with($pException)->will($this->returnValue('Exception caught'));

		$pConfigFilterShortCodeAddress = new ContentFilterShortCodeAddress(
			$this->_pSearchParametersModelBuilder,
			$this->_pAddressListFactory,
			$this->_pLogger,
			$this->_pMockDataListViewFactoryAddress,
			$this->_pTemplateMock,
			$this->_pWPQueryWrapperMock
		);
		$result = $pConfigFilterShortCodeAddress->replaceShortCodes(['view' => 'testException']);
		$this->assertEquals('Exception caught', $result);
	}


	/**
	 *
	 */

	public function testGetTag()
	{
		$pConfigFilterShortCodeAddress = new ContentFilterShortCodeAddress(
			$this->_pSearchParametersModelBuilder,
			$this->_pAddressListFactory,
			$this->_pLogger,
			$this->_pMockDataListViewFactoryAddress,
			$this->_pTemplateMock,
			$this->_pWPQueryWrapperMock
		);
		$this->assertEquals('oo_address', $pConfigFilterShortCodeAddress->getTag());
	}
}