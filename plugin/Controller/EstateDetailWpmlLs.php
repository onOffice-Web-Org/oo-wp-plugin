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

class EstateDetailWpmlLs
{
	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper;

	/**
	 * EstateDetailWpmlLs constructor.
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
	public function addIdToLsUrl(string $url): string
	{
		$estateId = (int)$this->_pWPQueryWrapper->getWPQuery()->get('estate_id', 0);
		$urlLsSwitcher = $url;

		if ($estateId !== 0){
			$urlLsSwitcher .= $estateId;
		}
		return $urlLsSwitcher;
	}
}