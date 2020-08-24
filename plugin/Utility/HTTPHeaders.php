<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Utility;

interface HTTPHeaders
{
	/**
	 * @return bool
	 */
	public function headersSent(): bool;

	/**
	 * @param string $headerName
	 * @return string
	 */
	public function getRequestHeaderValue(string $headerName): string;

	/**
	 * @param int $responseCode
	 */
	public function setHttpResponseCode(int $responseCode);

	/**
	 * @param string $header
	 * @param bool $replace
	 * @param int|null $responseCode
	 */
	public function addHeader(string $header, bool $replace = true, int $responseCode = null);
}