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
	 * @return array
	 */
	public function getRequestHeaders(): array;

	/**
	 * @param string $headerName
	 * @return string
	 */
	public function getRequestHeaderValue(string $headerName): string;

	/**
	 * @param int $responseCode
	 * @return mixed
	 */
	public function setHttpResponseCode(int $responseCode);

	public function addHeader(string $header, bool $replace = true, int $responseCode = null);
}