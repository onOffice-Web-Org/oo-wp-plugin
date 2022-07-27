<?php

declare(strict_types=1);

namespace onOffice\tests;

use onOffice\WPlugin\Cache\CachedOutput;
use onOffice\WPlugin\Utility\HTTPHeaders;
use onOffice\WPlugin\Factory\DateTimeImmutableFactory;

class TestClassCachedOutput
	extends \WP_UnitTestCase
{
	/**
	 * @throws \Exception
	 */
	public function testOutputCached()
	{
		$pDateTimeImmutable = new \DateTimeImmutable('2020-08-24 01:01:01', new \DateTimeZone('UTC'));
		$pMockHeaders = $this->getMockBuilder(HTTPHeaders::class)
			->getMock();
		$pMockHeaders->expects($this->exactly(5))
			->method('addHeader')->withConsecutive(
				['Cache-Control: public', true, null],
				['Cache-Control: must-revalidate', true, null],
				['Cache-Control: max-age=1209600', true, null],
				['ETag: "f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk="', true, null],
				['Expires: Mon, 07 Sep 2020 01:01:01 UTC', true, null]);

		$pDateTimeImmutableFactory = $this->getMockBuilder(DateTimeImmutableFactory::class)
			->setMethods(['create'])
			->getMock();

		$pDateTimeImmutableFactory->method('create')->willReturn($pDateTimeImmutable);

		$pCachedOutput = new CachedOutput($pDateTimeImmutableFactory, $pMockHeaders);
		$message = 'Hello World!';

		$this->expectOutputString('Hello World!');

		$pCachedOutput->outputCached($message, 60 * 60 * 24 * 14);
	}


	public function testOutputCachedWithIfNoneMatchHeaders()
	{
		$pDateTimeImmutable = new \DateTimeImmutable('2020-08-24 01:01:01', new \DateTimeZone('UTC'));
		$pMockHeaders = $this->getMockBuilder(HTTPHeaders::class)
			->getMock();
		$pMockHeaders->expects($this->once())
			->method('getRequestHeaderValue')->with('If-None-Match')
			->willReturn('"f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk="');
		$pMockHeaders->expects($this->once())
			->method('setHttpResponseCode')->with(304);
		$pMockHeaders->expects($this->exactly(5))
			->method('addHeader')->withConsecutive(
				['Cache-Control: public', true, null],
				['Cache-Control: must-revalidate', true, null],
				['Cache-Control: max-age=1209600', true, null],
				['ETag: "f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk="', true, null],
				['Expires: Mon, 07 Sep 2020 01:01:01 UTC', true, null]);

		$pDateTimeImmutableFactory = $this->getMockBuilder(DateTimeImmutableFactory::class)
			->setMethods(['create'])
			->getMock();
		$pDateTimeImmutableFactory->method('create')->willReturn($pDateTimeImmutable);

		$pCachedOutput = new CachedOutput($pDateTimeImmutableFactory, $pMockHeaders);
		$this->expectOutputString('');

		$pCachedOutput->outputCached('Hello World!', 60 * 60 * 24 * 14);
	}
}