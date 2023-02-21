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

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\WPlugin\API\APIEmptyResultException;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Utility\Logger;

class ContentFilterShortCodeEstate
	implements ContentFilterShortCode
{
	/** @var ContentFilterShortCodeEstateDetail */
	private $_pContentFilterShortCodeEstateDetail;

	/** @var ContentFilterShortCodeEstateList */
	private $_pContentFilterShortCodeEstateList;

	/** @var Logger */
	private $_pLogger;

	/**
	 * @param ContentFilterShortCodeEstateDetail $pContentFilterShortCodeEstateDetail
	 * @param ContentFilterShortCodeEstateList $pContentFilterShortCodeEstateList
	 * @param Logger $pLogger
	 */
	public function __construct(
		ContentFilterShortCodeEstateDetail $pContentFilterShortCodeEstateDetail,
		ContentFilterShortCodeEstateList $pContentFilterShortCodeEstateList,
		Logger $pLogger)
	{
		$this->_pContentFilterShortCodeEstateDetail = $pContentFilterShortCodeEstateDetail;
		$this->_pContentFilterShortCodeEstateList = $pContentFilterShortCodeEstateList;
		$this->_pLogger = $pLogger;
	}

	/**
	 * @param array $attributesInput
	 * @return string The new content
	 */
	public function replaceShortCodes(array $attributesInput): string
	{
		try {
			return $this->buildReplacementString($attributesInput);
		} catch (Exception $pException) {
			return $this->_pLogger->logErrorAndDisplayMessage($pException);
		}
	}

	/**
	 * @param array $attributesInput
	 * @return string
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 * @throws UnknownViewException
	 * @throws HttpFetchNoResultException
	 * @throws APIEmptyResultException
	 */
	private function buildReplacementString(array $attributesInput): string
	{
		$attributes = shortcode_atts([
			'view' => null,
			'units' => null,
		], $attributesInput);
		if ($attributes['view'] === $this->_pContentFilterShortCodeEstateDetail->getViewName() || $attributes['view'] === $this->_pContentFilterShortCodeEstateDetail->getViewNameReplace()) {
			return $this->_pContentFilterShortCodeEstateDetail->render($attributes);
		}
		return $this->_pContentFilterShortCodeEstateList->render($attributes);
	}

	/**
	 * @return string Name of the tag
	 */
	public function getTag(): string
	{
		return 'oo_estate';
	}
}