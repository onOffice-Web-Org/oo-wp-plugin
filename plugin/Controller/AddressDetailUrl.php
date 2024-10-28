<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

namespace onOffice\WPlugin\Controller;


class AddressDetailUrl
{
	/**
	 * @param string $url
	 * @param int $addressId
	 * @param string|null $title
	 * @param string|null $oldUrl
	 * @param bool $flag
	 *
	 * @return string
	 */

	public function createAddressDetailLink(
		string $url,
		int    $addressId,
		string $title = null,
		string $oldUrl = null,
		bool   $flag = false): string
	{
		$urlLsSwitcher = $url;
		$slashChar = '';

		if ($addressId !== 0) {
			$urlElements = parse_url($url);
			$getParameters = [];

			if (!empty($urlElements['query'])) {
				parse_str($urlElements['query'], $getParameters);
			}

			if (!is_null($oldUrl)) {
				$oldUrlElements = parse_url($oldUrl);
				$oldUrlPathArr = explode('/', $oldUrlElements['path']);
				if (empty(end($oldUrlPathArr)) || $flag) {
					$slashChar = '/';
				}
			}

			$urlTemp = $addressId;

			if (!empty($title) && $this->isOptionShowTitleUrl()) {
				$urlTemp .= $this->getSanitizeTitle($title, $flag);
			}

			$urlLsSwitcher = $urlElements['scheme'] . '://' . $urlElements['host'] . $urlElements['path'] . $urlTemp . $slashChar;

			if (!empty($getParameters)) {
				$urlLsSwitcher .= '?' . http_build_query($getParameters);
			}
		}

		return $urlLsSwitcher;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function isOptionShowTitleUrl()
	{
		return get_option('onoffice-address-detail-view-showInfoUserUrl', false);
	}


	/**
	 * @param string $title
	 * @param bool $flag
	 * @return string
	 */

	public function getSanitizeTitle(string $title, bool $flag = false): string
	{
		$sanitizeTitle = $flag ? sanitize_title(remove_accents($title)) : sanitize_title($title);
		return '-' . $sanitizeTitle;
	}

	/**
	 * @param int $addressId
	 * @param string|null $title
	 * @param string|null $oldUrl
	 * @param bool $isUrlHaveTitle
	 * @param bool $pAddressRedirection
	 * @return string
	 */
	public function getUrlWithAddressTitle(int $addressId, string $title = null, string $oldUrl = null, bool $isUrlHaveTitle = false, bool $pAddressRedirection = false): string
	{
		$getParameters = [];
		$urlElements = parse_url($oldUrl);
		$urlTemp = $addressId;

		if (!empty($title) && $this->isOptionShowTitleUrl()) {
			if ($pAddressRedirection === false && !empty($urlElements['query']) && !$isUrlHaveTitle) {
				$urlTemp .= '';
			} else {
				$urlTemp .= $this->getSanitizeTitle($title);
			}
		}

		if (!empty($urlElements['query'])) {
			parse_str($urlElements['query'], $getParameters);
		}

		$oldUrlPathArr = explode('/', $urlElements['path']);
		if (empty(end($oldUrlPathArr))) {
			array_pop($oldUrlPathArr);
		}
		array_pop($oldUrlPathArr);
		$newPath = implode('/', $oldUrlPathArr);

		$urlLsSwitcher = $urlElements['scheme'] . '://' . $urlElements['host'] . $newPath . '/' . $urlTemp;

		if (!empty($getParameters)) {
			$urlLsSwitcher = add_query_arg($getParameters, $urlLsSwitcher);
		}

		return $urlLsSwitcher;
	}
}
