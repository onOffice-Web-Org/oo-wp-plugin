<?php
/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

use onOffice\WPlugin\Utility\UrlHelper;

/**
 * Follows the redirects of a detail page request the way a browser would.
 */
trait RedirectChainTrait
{
	/** Number of redirects a request may take before the chain is considered a loop. */
	const MAX_REDIRECTS = 5;

	/**
	 * The path as it is handed over to WordPress by different web server setups.
	 *
	 * @return array
	 */
	public function dataServerBehaviours(): array
	{
		return [
			// Apache with mod_rewrite to index.php: REQUEST_URI is passed through unchanged.
			'unchanged path' => [static function (string $path): string {
				return $path;
			}],
			// PATH_INFO based setups: WordPress re-encodes every percent sign (see WP::parse_request).
			'double encoded path' => [static function (string $path): string {
				return str_replace('%', '%25', $path);
			}],
			// Setups handing over the already decoded path, e.g. PATH_INFO style rewrite rules.
			'decoded path' => [static function (string $path): string {
				return rawurldecode($path);
			}],
			// Proxies and CDNs normalizing percent encoded octets to upper case.
			'upper case octets' => [static function (string $path): string {
				return (string)preg_replace_callback('/%[a-fA-F0-9]{2}/', static function (array $matches): string {
					return strtoupper($matches[0]);
				}, $path);
			}],
		];
	}

	/**
	 * @param string $requestUri
	 * @param callable $serverBehaviour Turns the requested path into the path WordPress receives
	 * @param callable $dispatchRequest Runs the redirector, returns the redirect target or null
	 *
	 * @return string[] The redirect targets in the order they were sent
	 */
	private function followRedirects(string $requestUri, callable $serverBehaviour, callable $dispatchRequest): array
	{
		$redirects = [];
		$currentUri = $requestUri;

		for ($hop = 0; $hop <= self::MAX_REDIRECTS; $hop++) {
			$this->prepareRequest($currentUri, $serverBehaviour);
			$redirectUrl = $dispatchRequest();

			if ($redirectUrl === null) {
				return $redirects;
			}

			$this->assertFalse(
				UrlHelper::isSameLocation($redirectUrl, home_url($currentUri)),
				'redirect loop: ' . $currentUri . ' is redirected to itself (' . $redirectUrl . ')'
			);

			$redirects[] = $redirectUrl;
			$currentUri = $this->buildNextRequestUri($redirectUrl);
		}

		$this->fail('redirect loop: ' . $requestUri . ' -> ' . implode(' -> ', $redirects));
	}

	/**
	 * @param string $requestUri
	 * @param callable $serverBehaviour
	 *
	 * @return void
	 */
	private function prepareRequest(string $requestUri, callable $serverBehaviour)
	{
		global $wp;

		$path = (string)wp_parse_url($requestUri, PHP_URL_PATH);
		$query = (string)wp_parse_url($requestUri, PHP_URL_QUERY);

		$_SERVER['REQUEST_URI'] = $requestUri;
		$_GET = [];
		if ($query !== '') {
			parse_str($query, $_GET);
		}

		// WordPress trims the slashes of the requested path, see WP::parse_request().
		$wp->request = $serverBehaviour(trim($path, '/'));
	}

	/**
	 * WordPress adds the trailing slash of the permalink structure to the redirect target.
	 *
	 * @param string $redirectUrl
	 *
	 * @return string
	 */
	private function buildNextRequestUri(string $redirectUrl): string
	{
		$nextUri = user_trailingslashit((string)wp_parse_url($redirectUrl, PHP_URL_PATH), 'page');
		$query = (string)wp_parse_url($redirectUrl, PHP_URL_QUERY);

		return $query === '' ? $nextUri : $nextUri . '?' . $query;
	}
}
