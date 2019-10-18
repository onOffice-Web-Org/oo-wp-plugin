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
use onOffice\WPlugin\Filter\DefaultFilterBuilderListViewAddress;
use onOffice\WPlugin\Language;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DataListViewAddressToAPIParameters
{
	/** @var DataListViewAddress */
	private $_pDataListView = null;

	/** @var int */
	private $_page = 1;

	/** @var DefaultFilterBuilderListViewAddress */
	private $_pDefaultFilterBuilderListViewAddress = null;


	/**
	 *
	 * @param DataListViewAddress $pDataListView
	 *
	 */

	public function __construct(DataListViewAddress $pDataListView,
		DefaultFilterBuilderListViewAddress $pDefaultFilterBuilderListViewAddress = null)
	{
		$this->_pDataListView = $pDataListView;
		$this->_pDefaultFilterBuilderListViewAddress =
			$pDefaultFilterBuilderListViewAddress ?? new DefaultFilterBuilderListViewAddress($pDataListView);
	}



	/**
	 *
	 * @param array $fields
	 * @return array
	 *
	 */

	public function buildParameters(array $fields): array
	{
		$pDataListViewAddress = $this->getDataListView();
		$offset = ($this->_page - 1) * $pDataListViewAddress->getRecordsPerPage();
		$limit = $pDataListViewAddress->getRecordsPerPage();

		$parameters = array(
			'data' => $fields,
			'listoffset' => $offset,
			'listlimit' => $limit,
			'sortby' => $pDataListViewAddress->getSortby(),
			'sortorder' => $pDataListViewAddress->getSortorder(),
			'filter' => $this->_pDefaultFilterBuilderListViewAddress->buildFilter(),
			'filterid' => $pDataListViewAddress->getFilterId(),
			'outputlanguage' => Language::getDefault(),
			'formatoutput' => true,
		);

		if ($pDataListViewAddress->getShowPhoto()) {
			$parameters['data'] []= 'imageUrl';
		}

		return $parameters;
	}


	/** @return DataListViewAddress */
	public function getDataListView(): DataListViewAddress
		{ return $this->_pDataListView; }

	/** @return int */
	public function getPage(): int
		{ return $this->_page; }

	/** @param int $page */
	public function setPage(int $page)
		{ $this->_page = $page; }
}
