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
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\PDF\PdfDocumentFetcher;
use onOffice\WPlugin\PDF\PdfDocumentModel;
use onOffice\WPlugin\PDF\PdfDownloadException;
use onOffice\WPlugin\SDKWrapper;
use Symfony\Component\Process\Process;
use WP_UnitTestCase;

/**
 *
 */

class TestClassPdfDocumentFetcher
	extends WP_UnitTestCase
{
	/** @var Process */
	private static $_pProcess;

	/** @var APIClientActionGeneric */
	private $_pAPIClientAction;

	static public function setUpBeforeClass()
	{
		parent::set_up_before_class();
		$command = ['php', '-S', 'localhost:8008', '-t', './resources/HTTP/'];
		self::$_pProcess = new Process($command, __DIR__);
		self::$_pProcess->start();
		sleep(5);
	}

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pAPIClientAction = $this->getMockBuilder(APIClientActionGeneric::class)
			->setConstructorArgs([new SDKWrapper(), '', ''])
			->setMethods(['sendRequests', 'getResultRecords'])
			->getMock();

		$this->_pAPIClientAction->method('sendRequests');
		$this->_pAPIClientAction
			->method('getResultRecords')
			->willReturn([0 => [
				'elements' => [
					0 => 'http://localhost:8008/test.txt',
				]]]);
	}

	/**
	 * @throws ApiClientException
	 */
	public function testFetchUrl()
	{
		$pPdfDocumentFetcher = new PdfDocumentFetcher($this->_pAPIClientAction);
		$pPdfDocumentModel = new PdfDocumentModel(13, 'defaultexpose');
		$pPdfDocumentModel->setLanguage('ESP');
		$result = $pPdfDocumentFetcher->fetchUrl($pPdfDocumentModel);
		$this->assertSame('http://localhost:8008/test.txt', $result);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws PdfDownloadException
	 */
	public function testProxyResult()
	{
		$pPdfDocumentFetcher = new PdfDocumentFetcher($this->_pAPIClientAction);
		$pPdfDocumentModel = new PdfDocumentModel(13, 'defaultexpose');
		$pPdfDocumentModel->setLanguage('ESP');
		$pPdfDocumentModel->setEstateIdExternal('EXT1337');
		$pPdfDocumentModel->setTemplate('urn:onoffice-de-ns:smart:2.5:pdf:expose:lang:alaska');

		$this->expectOutputString('Hello World!');
		$pPdfDocumentFetcher->proxyResult($pPdfDocumentModel, 'http://localhost:8008/test.txt');
		$this->assertContains('Content-Type: text/plain; charset=UTF-8', xdebug_get_headers());
		$this->assertContains('Content-Length: 12', xdebug_get_headers());
		$this->assertContains('Content-Disposition: attachment; filename="alaska_EXT1337.pdf"', xdebug_get_headers());
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @throws PdfDownloadException
	 */
	public function testProxyResultUnsuccessful()
	{
		$pPdfDocumentModel = new PdfDocumentModel(13, 'defaultexpose');
		$pPdfDocumentFetcher = new PdfDocumentFetcher($this->_pAPIClientAction);

		$this->expectException(PdfDownloadException::class);
		$this->expectOutputString('');
		$pPdfDocumentFetcher->proxyResult($pPdfDocumentModel, 'http://localhost:8008/does/not/exist.txt');
	}

	public static function tearDownAfterClass()
	{
		parent::tearDownAfterClass();
		self::$_pProcess->stop();
	}
}
