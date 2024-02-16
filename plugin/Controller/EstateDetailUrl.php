<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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


class EstateDetailUrl
{
	const MAXIMUM_WORD_TITLE = 5;


	/**
	 * @param string $url
	 * @param int $estateId
	 * @param string|null $title
	 * @param string|null $oldUrl
	 *
	 * @return string
	 */

	public function createEstateDetailLink(
		string $url,
		int $estateId,
		string $title = null,
		string $oldUrl = null ): string
	{
		$urlLsSwitcher = $url;
		$slashChar     = '';

		if ( $estateId !== 0 ) {
			$urlElements   = parse_url( $url );
			$getParameters = [];

			if ( ! empty( $urlElements['query'] ) ) {
				parse_str( $urlElements['query'], $getParameters );
			}

			if ( ! is_null( $oldUrl ) ) {
				$oldUrlElements = parse_url( $oldUrl );
				$oldUrlPathArr  = explode( '/', $oldUrlElements['path'] );
				if ( empty( end( $oldUrlPathArr ) ) ) {
					$slashChar = '/';
				}
			}

			$urlTemp = $estateId;

			if ( ! empty( $title ) && $this->isOptionShowTitleUrl() ) {
				$urlTemp .= $this->getSanitizeTitle( $title );
			}

			$urlLsSwitcher = $urlElements['scheme'] . '://' . $urlElements['host'] . $urlElements['path'] . $urlTemp . $slashChar;

			if ( ! empty( $getParameters ) ) {
				$urlLsSwitcher .= '?' . http_build_query( $getParameters );
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
		return get_option('onoffice-detail-view-showTitleUrl',  false);
	}


	/**
	 * @param string $title
	 * @return string
	 */

	public function getSanitizeTitle(string $title): string
	{
		$sanitizeTitle = sanitize_title($title);
		$arrSanitizeTitle = explode('-', $sanitizeTitle);
		if (count($arrSanitizeTitle) > self::MAXIMUM_WORD_TITLE) {
			$sanitizeTitle = implode('-', array_splice($arrSanitizeTitle, 0, self::MAXIMUM_WORD_TITLE));
		}

		return '-' . $sanitizeTitle;
	}

	/**
	 * @param int $estateId
	 * @param string|null $title
	 * @param string|null $oldUrl
	 * @param bool $isUrlHaveTitle
	 * @param bool $pEstateRedirection
	 * @return string
	 */
	public function getUrlWithEstateTitle(int $estateId, string $title = null, string $oldUrl = null, bool $isUrlHaveTitle = false, bool $pEstateRedirection = false): string
	{
		$getParameters = [];
		$urlElements   = parse_url( $oldUrl );
		$urlTemp       = $estateId;
		$tickerUrlHasTitleFlag = false;

		if (!empty($title) && $this->isOptionShowTitleUrl()) {
			if ($pEstateRedirection === false && !empty($urlElements['query']) && !$isUrlHaveTitle) {
				$urlTemp .= '';
			} else {
				$tickerUrlHasTitleFlag = true;
				$urlTemp .= $this->getSanitizeTitle($title);
			}
		}

		if ( ! empty( $urlElements['query'] ) ) {
			parse_str( $urlElements['query'], $getParameters );
		}

		$oldUrlPathArr = explode( '/', $urlElements['path'] );
		if ( empty( end( $oldUrlPathArr ) ) ) {
			array_pop( $oldUrlPathArr );
		}
		array_pop( $oldUrlPathArr );
		$newPath = implode( '/', $oldUrlPathArr );

		$urlLsSwitcher = $urlElements['scheme'] . '://' . $urlElements['host'] . $newPath . '/' . $urlTemp;

		if (!empty($getParameters)) {
			if ($pEstateRedirection) {
				$urlLsSwitcher .= '';
			} elseif(!$tickerUrlHasTitleFlag) {
				$urlLsSwitcher .= '?' . http_build_query($getParameters);
			}
		}

		return $urlLsSwitcher;
	}
}