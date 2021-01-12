<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Cache;

use DateInterval;
use DateTime;
use onOffice\WPlugin\Factory\DateTimeImmutableFactory;
use Exception;
use onOffice\WPlugin\Utility\HTTPHeaders;
use RuntimeException;

class CachedOutput
{
	/** @var DateTimeImmutableFactory */
	private $_pDateTimeFactory;

	/** @var HTTPHeaders */
	private $_pHTTPHeaders;

	/**
	 * @param DateTimeImmutableFactory $pDateTimeFactory
	 * @param HTTPHeaders $pHTTPHeaders
	 */
	public function __construct(DateTimeImmutableFactory $pDateTimeFactory, HTTPHeaders $pHTTPHeaders)
	{
		$this->_pDateTimeFactory = $pDateTimeFactory;
		$this->_pHTTPHeaders = $pHTTPHeaders;
	}

	/**
	 * @param string $content
	 * @param int $intervalSpecLifetime
	 * @return void
	 * @throws Exception
	 */
	public function outputCached(string $content, int $intervalSpecLifetime)
	{
		if ($this->_pHTTPHeaders->headersSent()) {
			throw new RuntimeException('Headers sent');
		}

		$pDateTimeImmutable = $this->_pDateTimeFactory->create();
		$pDateTime = $pDateTimeImmutable->add(new DateInterval('PT'.$intervalSpecLifetime.'S'));
		$eTag = $this->createETagValueQuoted($content);
		$this->_pHTTPHeaders->addHeader('Cache-Control: public');
		$this->_pHTTPHeaders->addHeader('Cache-Control: must-revalidate');
		$this->_pHTTPHeaders->addHeader('Cache-Control: max-age='.$intervalSpecLifetime);
		$this->_pHTTPHeaders->addHeader('ETag: '.$eTag);
		$this->_pHTTPHeaders->addHeader('Expires: '.$pDateTime->format(DateTime::RFC7231));
		if ($eTag === $this->_pHTTPHeaders->getRequestHeaderValue('If-None-Match')) {
			$this->_pHTTPHeaders->setHttpResponseCode(304);
			return;
		}
		echo $content;
	}

	/**
	 * @param string $content
	 * @return string
	 */
	private function createETagValueQuoted(string $content): string
	{
		$eTag = base64_encode(hash('sha256', $content, true));
		return sprintf('"%s"', $eTag);
	}
}