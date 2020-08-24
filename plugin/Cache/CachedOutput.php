<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Cache;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
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
	 */
	public function __construct(DateTimeImmutable $pDateTime, HTTPHeaders $pHTTPHeaders)
	{
		$this->_pDateTime = $pDateTime;
		$this->_pHTTPHeaders = $pHTTPHeaders;
	}

	/**
	 * @param string $content
	 * @param string $intervalSpecLifetime
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
		$this->_pHTTPHeaders->addHeader('Expires: '.$pDateTime->format(DateTimeInterface::RFC7231));
		$requestHeaders = $this->_pHTTPHeaders->getRequestHeaders();
		$ifNoneMatchHeader = array_change_key_case($requestHeaders, CASE_LOWER)['if-none-match'] ?? '';
		if ($eTag === $ifNoneMatchHeader) {
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