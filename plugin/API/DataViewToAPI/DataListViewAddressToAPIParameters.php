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

declare (strict_types=1);

namespace onOffice\WPlugin\API\DataViewToAPI;

use onOffice\WPlugin\DataView\DataListViewAddress;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListViewAddressFactory;
use onOffice\WPlugin\Language;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DataListViewAddressToAPIParameters
{
	/** @var DefaultFilterBuilderListViewAddressFactory */
	private $_pFilterBuilderFactory = null;


	/**
	 *
	 * @param DefaultFilterBuilderListViewAddressFactory $pFilterBuilderFactory
	 *
	 */

	public function __construct(DefaultFilterBuilderListViewAddressFactory $pFilterBuilderFactory)
	{
		$this->_pFilterBuilderFactory = $pFilterBuilderFactory;
	}



	/**
	 *
	 * @param array $fields
	 * @param DataListViewAddress $pDataListView
	 * @param int $page
	 * @return array
	 */

	public function buildParameters(array $fields, DataListViewAddress $pDataListView, int $page): array
	{
		$pBuilderListViewAddress = $this->_pFilterBuilderFactory->create($pDataListView);

		$offset = 0;

		if ($page > 0) {
			$offset = ($page - 1) * $pDataListView->getRecordsPerPage();
		}

		$limit = $pDataListView->getRecordsPerPage();

		$parameters = array(
			'data' => $fields,
			'listoffset' => $offset,
			'listlimit' => $limit,
			'sortby' => $pDataListView->getSortby(),
			'sortorder' => $pDataListView->getSortorder(),
			'filter' => $pBuilderListViewAddress->buildFilter(),
			'filterid' => $pDataListView->getFilterId(),
			'outputlanguage' => Language::getDefault(),
			'formatoutput' => true,
		);

		if ($pDataListView->getShowPhoto()) {
			$parameters['data'] []= 'imageUrl';
		}

		return $parameters;
	}
}