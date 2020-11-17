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

namespace onOffice\WPlugin\DataView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 * DO NOT MOVE OR RENAME - NAME AND/OR NAMESPACE MAY BE USED IN SERIALIZED DATA
 *
 */

class DataSimilarView
{
	/** */
	const ENABLE_SIMILAR_ESTATES = 'enablesimilarestates';

	/** @var int */
	private $_pageId = 0;

	/** @var bool */
	private $_dataSimilarViewActive = false;

	/** @var DataViewSimilarEstates */
	private $_pDataViewSimilarEstates = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pDataViewSimilarEstates = new DataViewSimilarEstates();
	}


	/**
	 *
	 */

	public function __wakeup()
	{
		if ($this->_pDataViewSimilarEstates === null) {
			$this->_pDataViewSimilarEstates = new DataViewSimilarEstates();
		}
	}


	/** @return int */
	public function getPageId(): int
		{ return $this->_pageId; }

	/** @param int $pageId */
	public function setPageId(int $pageId)
		{ $this->_pageId = $pageId; }

	/** @return DataViewSimilarEstates */
	public function getDataViewSimilarEstates(): DataViewSimilarEstates
		{ return $this->_pDataViewSimilarEstates; }

	/** @param DataViewSimilarEstates $pDataViewSimilarEstates */
	public function setDataViewSimilarEstates(DataViewSimilarEstates $pDataViewSimilarEstates)
		{ $this->_pDataViewSimilarEstates = $pDataViewSimilarEstates; }

	/** @return bool */
	public function getDataSimilarViewActive(): bool
		{ return $this->_dataSimilarViewActive; }

	/** @param bool $dataSimilarViewActive */
	public function setDataSimilarViewActive(bool $dataSimilarViewActive)
		{ $this->_dataSimilarViewActive = $dataSimilarViewActive; }

	/** @return bool */
	public function getRandom(): bool
		{  return false; }

}
