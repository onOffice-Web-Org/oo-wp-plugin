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

use onOffice\WPlugin\WP\WPQueryWrapper;

class EstateDetailUrl
{
	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper;

	/**
	 * EstateDetailUrl constructor.
	 *
	 * @param WPQueryWrapper $pWPQueryWrapper
	 */
	public function __construct(WPQueryWrapper $pWPQueryWrapper)
	{
		$this->_pWPQueryWrapper = $pWPQueryWrapper;
	}


	/**
	 * @param string $url
	 * @return string
	 */
	public function addEstateIdToLanguageSwitcher(string $url): string
	{
		$estateId = (int)$this->_pWPQueryWrapper->getWPQuery()->get('estate_id', 0);
		return $this->createEstateDetailLink($url, $estateId);
	}


	/**
	 * @param string $url
	 * @param int $estateId
	 * @return string
	 */
	public function createEstateDetailLink(string $url, int $estateId): string
	{
		$urlLsSwitcher = $url;

		if ($estateId !== 0){
			$getParameters = parse_url($url, PHP_URL_QUERY);
			if ($getParameters != null && strpos($getParameters, 'lang=') !== false) {
				$urlLsSwitcher = str_replace('?', $estateId.'?', $url);
			} else {
				$urlLsSwitcher .= $estateId;
			}
		}
		return $urlLsSwitcher;
	}
}