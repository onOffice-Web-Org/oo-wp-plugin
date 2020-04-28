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

use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\PDF\PdfDownloadException;
use Symfony\Component\Process\Process;
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
	/** @var Process */
	private static $_pProcess;

	static public function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		$command = ['php', '-S', 'localhost:8008', '-t', './resources/HTTP/'];
		self::$_pProcess = new Process($command, __DIR__);
		self::$_pProcess->start();
	}

	/**
	 * @param string $returnUrl
	 * @return APIClientActionGeneric
	 */
	public function getApiClientAction(string $returnUrl): APIClientActionGeneric
	{
		$pApiClientAction = $this->getMockBuilder(APIClientActionGeneric::class)
			->setConstructorArgs([new SDKWrapper(), '', ''])
			->setMethods(['sendRequests', 'getResultRecords'])
			->getMock();

		$pApiClientAction->expects($this->once())->method('sendRequests');
		$pApiClientAction
			->expects($this->once())
			->method('getResultRecords')
			->will($this->returnValue([0 => [
				'elements' => [
					0 => $returnUrl,
			]]]));
		return $pApiClientAction;
	}

	/**
	 *
	 */
	public function testFetch()
	{
		$pApiClientAction = $this->getApiClientAction('http://localhost:8008/test.txt');
		$PdfDocumentFetcher = new PdfDocumentFetcher($pApiClientAction);
		$pPdfDocumentModel = new PdfDocumentModel(13, 'defaultexpose');
		$pPdfDocumentModel->setLanguage('ESP');
		$pResponse = $PdfDocumentFetcher->fetch($pPdfDocumentModel);
		$this->assertInstanceOf(PdfDocumentResult::class, $pResponse);
		$this->assertSame(12, $pResponse->getContentLength());
		$this->assertSame('text/plain; charset=UTF-8', $pResponse->getContentType());
		$this->assertInstanceOf(\Iterator::class, $pResponse->getIterator());
		$this->assertSame(['Hello World!'], iterator_to_array($pResponse->getIterator()));
	}

	public function testFetchUnSuccessful()
	{
		$pApiClientAction = $this->getApiClientAction('http://localhost:8008/does/not/exist.txt');
		$PdfDocumentFetcher = new PdfDocumentFetcher($pApiClientAction);
		$pPdfDocumentModel = new PdfDocumentModel(13, 'defaultexpose');
		$pPdfDocumentModel->setLanguage('ESP');
		$this->expectException(PdfDownloadException::class);
		$PdfDocumentFetcher->fetch($pPdfDocumentModel);
	}

	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();
		self::$_pProcess->stop();
	}
}
