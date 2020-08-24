<?php

declare(strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\Cache\CachedOutput;
use onOffice\WPlugin\Utility\HTTPHeaders;

class TestClassCachedOutput
	extends \WP_UnitTestCase
{
	/**
	 * @throws \Exception
	 */
	public function testOutputCached()
	{
		$pDateTimeImmutable = (new \DateTimeImmutable('2020-08-24 01:01:01'))
			->setTimezone(new \DateTimeZone('CEST'));
		$pMockHeaders = $this->getMockBuilder(HTTPHeaders::class)
			->getMock();
		$pMockHeaders->expects($this->exactly(5))
			->method('addHeader')->withConsecutive(
				['Cache-Control: public', true, null],
				['Cache-Control: must-revalidate', true, null],
				['Cache-Control: max-age=1209600', true, null],
				['ETag: "f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk="', true, null],
				['Expires: Mon, 07 Sep 2020 02:01:01 GMT', true, null]);
		$pCachedOutput = new CachedOutput($pDateTimeImmutable, $pMockHeaders);
		$message = 'Hello World!';

		$this->expectOutputString('Hello World!');

		$pCachedOutput->outputCached($message, 60 * 60 * 24 * 14);
	}


	public function testOutputCachedWithIfNoneMatchHeaders()
	{
		$pDateTimeImmutable = (new \DateTimeImmutable('2020-08-24 01:01:01'))
			->setTimezone(new \DateTimeZone('CEST'));
		$pMockHeaders = $this->getMockBuilder(HTTPHeaders::class)
			->getMock();
		$pMockHeaders->expects($this->once())
			->method('getRequestHeaders')
			->willReturn(['If-None-Match' => '"f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk="']);
		$pMockHeaders->expects($this->once())
			->method('setHttpResponseCode')->with(304);
		$pMockHeaders->expects($this->exactly(5))
			->method('addHeader')->withConsecutive(
				['Cache-Control: public', true, null],
				['Cache-Control: must-revalidate', true, null],
				['Cache-Control: max-age=1209600', true, null],
				['ETag: "f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk="', true, null],
				['Expires: Mon, 07 Sep 2020 02:01:01 GMT', true, null]);
		$pCachedOutput = new CachedOutput($pDateTimeImmutable, $pMockHeaders);
		$this->expectOutputString('');

		$pCachedOutput->outputCached('Hello World!', 60 * 60 * 24 * 14);
	}
}