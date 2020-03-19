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

namespace onOffice\WPlugin\Controller\ContentFilter;

class ContentFilterShortCodeEstate
	implements ContentFilterShortCode
{
	/** @var ContentFilterShortCodeEstateDetail */
	private $_pContentFilterShortCodeEstateDetail;

	/**
	 * @param ContentFilterShortCodeEstateDetail $pContentFilterShortCodeEstateDetail
	 */
	public function __construct(
		ContentFilterShortCodeEstateDetail $pContentFilterShortCodeEstateDetail)
	{
		$this->_pContentFilterShortCodeEstateDetail = $pContentFilterShortCodeEstateDetail;
	}

	/**
	 * @param array $attributesInput
	 * @return string The new content
	 */
	public function replaceShortCodes(array $attributesInput): string
	{
		$view = $attributesInput['view'] ?? '';
		if ($view === $this->_pContentFilterShortCodeEstateDetail->getViewName()) {
			return $this->_pContentFilterShortCodeEstateDetail->render($attributesInput);
		}
		return '';
	}

	/**
	 * @return string Name of the tag
	 */
	public function getTag(): string
	{
		return 'oo_estate';
	}
}