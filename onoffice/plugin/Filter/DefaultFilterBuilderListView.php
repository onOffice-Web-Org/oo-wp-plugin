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
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Favorites;
use onOffice\WPlugin\GeoPosition;

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

	/** @var DefaultFilterBuilderListViewEnvironment */
	private $_pEnvironment = null;

	/** @var array */
	private $_defaultFilter = [
		'veroeffentlichen' => [
			['op' => '=', 'val' => 1],
		],
	];


	/**
	 *
	 * @param DataListView $pDataListView
	 * @param DefaultFilterBuilderListViewEnvironment $pEnvironment
	 * @throws Exception
	 *
	 */

	public function __construct(
		DataListView $pDataListView,
		DefaultFilterBuilderListViewEnvironment $pEnvironment = null)
	{
		$this->_pDataListView = $pDataListView;
		$this->_pEnvironment = $pEnvironment ?? new DefaultFilterBuilderListViewEnvironmentDefault();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function buildFilter(): array
	{
		$filterableFields = $this->_pDataListView->getFilterableFields();

		// Geo position will be done later
		if (in_array(GeoPosition::FIELD_GEO_POSITION, $filterableFields)) {
			$position = array_search(GeoPosition::FIELD_GEO_POSITION, $filterableFields, true);
			unset($filterableFields[$position]);
		}

		$fieldFilter = $this->_pEnvironment->getFilterBuilderInputVariables()->getPostFieldsFilter($filterableFields);
		$filter = array_merge($this->_defaultFilter, $fieldFilter);

		switch ($this->_pDataListView->getListType()) {
			case DataListView::LISTVIEW_TYPE_FAVORITES:
				$filter = $this->getFavoritesFilter();
				break;
			case DataListView::LISTVIEW_TYPE_REFERENCE:
				$filter = $this->getReferenceViewFilter();
				break;
		}

		$filterWithRegion = $this->addSubRegionFilter($filter);

		return $filterWithRegion;
	}


	/**
	 *
	 * @param array $baseFilter
	 * @return array
	 *
	 */

	private function addSubRegionFilter(array $baseFilter): array
	{
		if (in_array('regionaler_zusatz', $this->_pDataListView->getFilterableFields(), true)) {
			$additionalRegions = [];
			$pRegionController = $this->_pEnvironment->getRegionController();
			$pRegionController->fetchRegions();
			$regionValue = (array)$this->_pEnvironment
				->getInputVariableReader()->getFieldValue('regionaler_zusatz');
			foreach ($regionValue as $region) {
				$additionalRegions = array_merge($additionalRegions,
					$pRegionController->getSubRegionsByParentRegion($region));
				$additionalRegions []= $region;
			}

			if ($additionalRegions !== []) {
				$baseFilter['regionaler_zusatz'] = [
					['op' => 'in', 'val' => $additionalRegions],
				];
			}
		}
		return $baseFilter;
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