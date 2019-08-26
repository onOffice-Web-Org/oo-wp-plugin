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

use onOffice\WPlugin\PDF\PdfDocumentFetcher;
use onOffice\WPlugin\PDF\PdfDocumentModel;
use onOffice\WPlugin\PDF\PdfDocumentModelValidator;
use onOffice\WPlugin\PDF\PdfDocumentResult;
use onOffice\WPlugin\PDF\PdfDownload;
use WP_UnitTestCase;

/**
 *
 */

class TestClassPdfDownload
	extends WP_UnitTestCase
{
	/** @var PdfDocumentModelValidator */
	private $_pPdfDocumentModelValidator = null;

	/** @var PdfDocumentFetcher */
	private $_pPdfDocumentFetcher = null;

	/** @var PdfDownload */
	private $_pPdfDownload = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pPdfDocumentFetcher = $this->getMockBuilder(PdfDocumentFetcher::class)
			->disableOriginalConstructor()
			->getMock();
		$this->_pPdfDocumentModelValidator = $this->getMockBuilder(PdfDocumentModelValidator::class)
			->disableOriginalConstructor()
			->getMock();
		$this->_pPdfDownload = new PdfDownload($this->_pPdfDocumentFetcher, $this->_pPdfDocumentModelValidator);
	}


	/**
	 *
	 */

	public function testDownload()
	{
		$pResponse = new PdfDocumentResult('application/pdf', "\0\0\xab");
		$this->_pPdfDocumentFetcher
			->expects($this->once())->method('fetch')->will($this->returnValue($pResponse));
		$pModel = new PdfDocumentModel(13, 'testTemplate', 'testView');
		$this->assertSame($pResponse, $this->_pPdfDownload->download($pModel));
	}
}
