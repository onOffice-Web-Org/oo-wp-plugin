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

use onOffice\WPlugin\Utility\Logger;
use onOffice\WPlugin\WP\UnknownPageException;
use function shortcode_atts;

/**
 *
 */

class ContentFilterShortCodeLink
	implements ContentFilterShortCode
{
	/** @var Logger */
	private $_pLogger = null;

	/** @var LinkBuilderPage */
	private $_pLinkBuilderPage = null;


	/**
	 *
	 * @param LinkBuilderPage $pLinkBuilderContact
	 *
	 */

	public function __construct(
		Logger $pLogger,
		LinkBuilderPage $pLinkBuilderContact)
	{
		$this->_pLogger = $pLogger;
		$this->_pLinkBuilderPage = $pLinkBuilderContact;
	}


	/**
	 *
	 * @param array $attributesInput
	 * @return string
	 *
	 */

	public function replaceShortCodes(array $attributesInput): string
	{
		$attributes = shortcode_atts([
			'contexts' => '',
			'path' => '',
		], $attributesInput);

		$contexts = array_map('trim', explode(',', $attributes['contexts']));
		$addEstateId = in_array('estate', $contexts, true);

		try {
			return $this->_pLinkBuilderPage->buildLinkByPath($attributes['path'], $addEstateId);
		} catch (UnknownPageException $pUnknownPageException) {
			return $this->_pLogger->logErrorAndDisplayMessage($pUnknownPageException);
		}
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getTag(): string
	{
		return 'oo_link';
	}
}
