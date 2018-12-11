<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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

namespace onOffice\WPlugin\Filter;

use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListViewAddress;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DefaultFilterBuilderListViewAddress
	implements DefaultFilterBuilder
{
	/** @var DataListViewAddress */
	private $_pDataListView = null;

	/** @var FilterBuilderInputVariables */
	private $_pFilterBuilderInputVars = null;


	/**
	 *
	 * @param DataListViewAddress $pDataListView
	 * @param FilterBuilderInputVariables $pFilterBuilder
	 *
	 */

	public function __construct(
		DataListViewAddress $pDataListView,
		FilterBuilderInputVariables $pFilterBuilder = null)
	{
		$this->_pDataListView = $pDataListView;
		$this->_pFilterBuilderInputVars = $pFilterBuilder ?? new FilterBuilderInputVariables
			(onOfficeSDK::MODULE_ADDRESS, true);

		if ($this->_pFilterBuilderInputVars->getModule() !== onOfficeSDK::MODULE_ADDRESS) {
			throw new Exception('Module must be address.');
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function buildFilter(): array
	{
		$filterableFields = $this->_pDataListView->getFilterableFields();
		$fieldFilter = $this->_pFilterBuilderInputVars->getPostFieldsFilter($filterableFields);

		$defaultFilter = [
			'homepage_veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
		];

		return array_merge($defaultFilter, $fieldFilter);
	}
}
