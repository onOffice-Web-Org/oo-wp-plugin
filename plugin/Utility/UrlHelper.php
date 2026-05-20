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
