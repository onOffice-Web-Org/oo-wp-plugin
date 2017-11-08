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

namespace onOffice\WPlugin;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class EstateDetail
	extends EstateList
{
	/** @var int */
	private $_estateId = null;

	/**
	 *
	 * @return int
	 *
	 */

	protected function getNumEstatePages() {
		return 1;
	}


	/**
	 *
	 * @param int $id
	 *
	 */

	public function loadSingleEstate($id) {
		$this->_estateId = $id;
		$this->loadEstates(1);
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function getDefaultFilter() {
		$pDataView = $this->getDataView();
		$pListViewFilterBuilder = new Filter\DefaultFilterBuilderDetailView($pDataView);
		$published = $pListViewFilterBuilder->buildFilter();

		$filterById = array(
			'Id' => array(
				array('op' => '=', 'val' => $this->_estateId),
			),
		);

		$filterCombined = array_merge($filterById, $published);

		return $filterCombined;
	}


	/**
	 *
	 * @return int
	 *
	 */

	protected function getRecordsPerPage() {
		return 1;
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function addExtraParams() {
		return array();
	}
}
