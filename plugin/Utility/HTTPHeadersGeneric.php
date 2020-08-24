<?php

declare(strict_types=1);

namespace onOffice\WPlugin\Utility;

class HTTPHeadersGeneric
	implements HTTPHeaders
{
	/**
	 * @codeCoverageIgnore
	 * @return bool
	 */
	public function headersSent(): bool
	{
		return headers_sent();
	}

	/**
	 * @param string $headerName
	 * @return string
	 */
	public function getRequestHeaderValue(string $headerName): string
	{
		$key = 'HTTP_'.strtoupper(str_replace('-', '_', $headerName));
		// stripslashes()? See https://developer.wordpress.org/reference/functions/stripslashes_deep/#more-information
		return stripslashes($_SERVER[$key] ?? '');
	}

	/**
	 * @param int $responseCode
	 * @codeCoverageIgnore
	 */
	public function setHttpResponseCode(int $responseCode)
	{
		http_response_code($responseCode);
	}

	/**
	 * @param string $header
	 * @param bool $replace
	 * @param int $responseCode
	 */
	public function addHeader(string $header, bool $replace = true, int $responseCode = null)
	{
		if ($responseCode !== null) {
			// @codeCoverageIgnoreStart
			header($header, $replace, $responseCode);
			// @codeCoverageIgnoreEnd
		} else {
			header($header, $replace);
		}
	}
}