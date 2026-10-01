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

declare(strict_types=1);

namespace onOffice\WPlugin\Utility;

class UrlHelper
{
	/** Special characters WordPress would percent-encode instead of transliterating. */
	const SPECIAL_CHARACTER_REPLACEMENTS = [
		'¹' => '1',
		'²' => '2',
		'³' => '3',
		'¼' => '1-4',
		'½' => '1-2',
		'¾' => '3-4',
		'µ' => 'u',
		'№' => 'nr',
		'⁄' => '-',
	];

	/** Web servers may hand the path over re-encoded more than once. */
	const MAX_DECODE_ITERATIONS = 4;

	/**
	 * Builds a slug without percent-encoded octets - a "%" in the path causes 301 loops.
	 *
	 * @param string $title
	 * @param string|null $locale Locale for remove_accents(), null skips the explicit call
	 * @return string
	 */
	public static function sanitizeTitleToSlug(string $title, ?string $locale = null): string
	{
		$title = strtr($title, self::SPECIAL_CHARACTER_REPLACEMENTS);

		if ($locale !== null) {
			$title = remove_accents($title, $locale);
		}

		return self::removePercentEncodedOctets(sanitize_title($title));
	}

	/**
	 * @param string $slug
	 * @return string
	 */
	public static function removePercentEncodedOctets(string $slug): string
	{
		if (strpos($slug, '%') === false) {
			return $slug;
		}

		$slug = (string)preg_replace('/(?:%[a-fA-F0-9]{2})+/', '-', $slug);
		$slug = (string)preg_replace('/-+/', '-', $slug);

		return trim($slug, '-');
	}

	/**
	 * Decodes a URL until it stops changing, so encoding differences do not matter.
	 *
	 * @param string $url
	 * @return string
	 */
	public static function normalizeUrl(string $url): string
	{
		for ($i = 0; $i < self::MAX_DECODE_ITERATIONS; $i++) {
			$decodedUrl = rawurldecode($url);
			if ($decodedUrl === $url) {
				break;
			}
			$url = $decodedUrl;
		}

		return untrailingslashit($url);
	}

	/**
	 * @param string $url
	 * @param string $otherUrl
	 * @return bool
	 */
	public static function isSameLocation(string $url, string $otherUrl): bool
	{
		return self::normalizeUrl($url) === self::normalizeUrl($otherUrl);
	}

	/**
	 * @param mixed $urlElements
	 * @return string
	 */
	public static function buildBaseUrl($urlElements): string
	{
		if (!is_array($urlElements)) {
			return '';
		}

		$scheme = $urlElements['scheme'] ?? '';
		$host = $urlElements['host'] ?? '';

		if ($scheme === '' || $host === '') {
			return '';
		}

		$baseUrl = $scheme . '://' . $host;

		if (isset($urlElements['port'])) {
			$baseUrl .= ':' . $urlElements['port'];
		}

		return $baseUrl;
	}

	/**
	 * @param mixed $urlElements
	 * @return string
	 */
	public static function getPath($urlElements): string
	{
		if (!is_array($urlElements)) {
			return '';
		}

		return is_string($urlElements['path'] ?? null) ? $urlElements['path'] : '';
	}
}
