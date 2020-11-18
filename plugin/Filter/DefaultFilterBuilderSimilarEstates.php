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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DefaultFilterBuilderSimilarEstates
	implements DefaultFilterBuilder
{
	/** @var FilterConfigurationSimilarEstates */
	private $_pFilterConfigurationSimilarEstates = null;

	/** @var int[] */
	private $_excludeIds = [];


	/**
	 *
	 * @param FilterConfigurationSimilarEstates $pFilterConfigurationSimilarEstates
	 *
	 */

	public function __construct(FilterConfigurationSimilarEstates $pFilterConfigurationSimilarEstates)
	{
		$this->_pFilterConfigurationSimilarEstates = $pFilterConfigurationSimilarEstates;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function buildFilter(): array
	{
		$filter = [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
		];

		$pFilterConfiguration = $this->_pFilterConfigurationSimilarEstates;
		$pDataListView = $pFilterConfiguration->getDataViewSimilarEstates();
		$postalCode = $pFilterConfiguration->getPostalCode();
        $showArchived = $pFilterConfiguration->getShowArchived();

		if ($pDataListView->getSameEstateKind()) {
			$filter['objektart'] []= ['op' => '=', 'val' => $pFilterConfiguration->getEstateKind()];
		}

		if ($pDataListView->getSameMarketingMethod()) {
			$filter['vermarktungsart'] []= ['op' => '=', 'val' => $pFilterConfiguration->getMarketingMethod()];
		}

		if ($pDataListView->getSamePostalCode() && $postalCode !== '') {
			$filter['plz'] []= ['op' => '=', 'val' => $pFilterConfiguration->getPostalCode()];
		}

        if ($pDataListView->getDontShowArchived() && $showArchived !== 'status2obj_archiviert') {
            $filter['status2'] []= ['op' => '!=', 'val' => ['status2obj_archiviert'] ];
        }

		if ($this->_excludeIds !== []) {
			$filter['Id'] []= ['op' => 'not in', 'val' => $this->_excludeIds];
		}
		return $filter;
	}


	/** @return array */
	public function getExcludeIds(): array
		{ return $this->_excludeIds; }

	/** @param array $excludeIds */
	public function setExcludeIds(array $excludeIds)
		{ $this->_excludeIds = $excludeIds; }
}
