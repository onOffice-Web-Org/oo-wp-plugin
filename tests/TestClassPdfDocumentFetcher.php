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

use onOffice\WPlugin\API\ApiClientActionGetPdf;
use onOffice\WPlugin\PDF\PdfDocumentFetcher;
use onOffice\WPlugin\PDF\PdfDocumentModel;
use onOffice\WPlugin\PDF\PdfDocumentResult;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;

/**
 *
 */

class TestClassPdfDocumentFetcher
	extends WP_UnitTestCase
{
	/** @var PdfDocumentFetcher */
	private $_pPdfDocumentFetcher = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$pApiClientActionGetPdf = $this->getMockBuilder(ApiClientActionGetPdf::class)
			->setConstructorArgs([new SDKWrapper(), '', ''])
			->setMethods(['getMimeTypeResult', 'sendRequests', 'getResultRecords'])
			->getMock();
		$pApiClientActionGetPdf
			->expects($this->once())
			->method('getMimeTypeResult')
			->will($this->returnValue('application/pdf'));
		$pApiClientActionGetPdf
			->expects($this->once())
			->method('getResultRecords')
			->will($this->returnValue([
				0 => "\0\0\0\0\xab\0xac12345",
			]));
		$this->_pPdfDocumentFetcher = new PdfDocumentFetcher($pApiClientActionGetPdf);
	}


	/**
	 *
	 */

	public function testFetch()
	{
		$pPdfDocumentModel = new PdfDocumentModel(13, 'defaultexpose', 'testConfig');
		$pPdfDocumentModel->setLanguage('ESP');
		$pResponse = $this->_pPdfDocumentFetcher->fetch($pPdfDocumentModel);
		$this->assertInstanceOf(PdfDocumentResult::class, $pResponse);
		$this->assertEquals('application/pdf', $pResponse->getMimetype());
		$this->assertEquals("\0\0\0\0\xab\0xac12345", $pResponse->getBinary());
	}
}
