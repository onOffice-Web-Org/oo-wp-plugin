<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Favorites;
use onOffice\WPlugin\Filter\FilterBuilderInputVariables;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DefaultFilterBuilderListView
	implements DefaultFilterBuilder
{
	/** @var DataListView */
	private $_pDataListView = null;

	/** @var FilterBuilderInputVariables */
	private $_pFilterBuilderInputVars = null;

	/** @var array */
	private $_defaultFilter = [
		'veroeffentlichen' => [
			['op' => '=', 'val' => 1],
		],
	];


	/**
	 *
	 * @param DataListView $pDataListView
	 * @param FilterBuilderInputVariables $pFilterBuilder
	 *
	 */

	public function __construct(
		DataListView $pDataListView,
		FilterBuilderInputVariables $pFilterBuilder = null)
	{
		$this->_pDataListView = $pDataListView;
		$this->_pFilterBuilderInputVars = $pFilterBuilder ?? new FilterBuilderInputVariables
			(onOfficeSDK::MODULE_ESTATE, true);

		if ($this->_pFilterBuilderInputVars->getModule() !== onOfficeSDK::MODULE_ESTATE) {
			throw new Exception('Module must be estate.');
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
		$filter = array_merge($this->_defaultFilter, $fieldFilter);

		switch ($this->_pDataListView->getListType()) {
			case DataListView::LISTVIEW_TYPE_FAVORITES:
				$filter = $this->getFavoritesFilter();
				break;
			case DataListView::LISTVIEW_TYPE_REFERENCE:
				$filter = $this->getReferenceViewFilter();
				break;
		}

		return $filter;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getFavoritesFilter(): array
	{
		$ids = Favorites::getAllFavorizedIds();

		$filter = $this->_defaultFilter;
		$filter['Id'] = [
			['op' => 'in', 'val' => $ids],
		];

		return $filter;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getReferenceViewFilter(): array
	{
		$filter = $this->_defaultFilter;
		$filter['referenz'] = [
			['op' => '=', 'val' => 1],
		];

		return $filter;
	}
}