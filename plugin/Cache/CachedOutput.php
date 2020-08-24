<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Cache;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use Exception;
use onOffice\WPlugin\Utility\HTTPHeaders;
use RuntimeException;

class CachedOutput
{
	/** @var DateTimeImmutable */
	private $_pDateTime;

	/** @var HTTPHeaders */
	private $_pHTTPHeaders;

	/**
	 * @param DateTimeImmutable $pDateTime
	 * @param HTTPHeaders $pHTTPHeaders
	 */
	public function __construct(DateTimeImmutable $pDateTime, HTTPHeaders $pHTTPHeaders)
	{
		$this->_pDateTime = $pDateTime;
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
		$pDateTime = $this->_pDateTime->add(new DateInterval('PT'.$intervalSpecLifetime.'S'));
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