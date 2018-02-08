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

use Exception;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\Filter\DefaultFilterBuilderDetailView;
use onOffice\WPlugin\Types\MovieLinkTypes;

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
	 * @throws Exception
	 *
	 */

	protected function getPreloadEstateFileCategories()
	{
		$fileCategories = parent::getPreloadEstateFileCategories();

		$pDataView = $this->getDataView();

		if (!$pDataView instanceof DataDetailView) {
			throw new Exception('DataView must be instance of DataDetailView!');
		}

		$movieLinksActive = $pDataView->getMovieLinks() !== MovieLinkTypes::MOVIE_LINKS_NONE;

		if ($movieLinksActive) {
			$fileCategories []= MovieLinkTypes::FILE_TYPE_MOVIE_LINK;
		}

		return $fileCategories;
	}


	/**
	 *
	 * @return array
	 *
	 */

	protected function getDefaultFilter() {
		$pListViewFilterBuilder = new DefaultFilterBuilderDetailView();
		$pListViewFilterBuilder->setEstateId($this->_estateId);
		$filter = $pListViewFilterBuilder->buildFilter();

		return $filter;
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
