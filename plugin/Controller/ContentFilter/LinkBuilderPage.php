<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

namespace onOffice\WPlugin\Controller\ContentFilter;

use onOffice\WPlugin\WP\WPPageWrapper;
use onOffice\WPlugin\WP\WPQueryWrapper;
use function add_query_arg;

/**
 *
 */

class LinkBuilderPage
{
	/** @var WPPageWrapper */
	private $_pWPPageWrapper = null;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper = null;


	/**
	 *
	 * @param WPPageWrapper $pWPPageWrapper
	 * @param WPQueryWrapper $pWPQueryWrapper
	 *
	 */

	public function __construct(
		WPPageWrapper $pWPPageWrapper = null,
		WPQueryWrapper $pWPQueryWrapper = null)
	{
		$this->_pWPQueryWrapper = $pWPQueryWrapper ?? new WPQueryWrapper();
		$this->_pWPPageWrapper = $pWPPageWrapper ?? new WPPageWrapper();
	}


	/**
	 *
	 * @param string $contactFormPath
	 * @param bool $preserveEstateContext
	 * @return string
	 *
	 */

	public function buildLinkByPath(string $contactFormPath, bool $preserveEstateContext): string
	{
		$pPost = $this->_pWPPageWrapper->getPageByPath($contactFormPath);
		$link = $this->_pWPPageWrapper->getPageLinkByPost($pPost);
		$estateId = (int)$this->_pWPQueryWrapper->getWPQuery()->get('estate_id', 0);

		if ($preserveEstateContext && $estateId !== 0) {
			$link = add_query_arg('estate_id', $estateId, $link);
		}

		return $link;
	}
}