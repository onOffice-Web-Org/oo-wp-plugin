<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

namespace onOffice\WPlugin\Controller\SortList;

class SortListDataModel
{
	/** @var bool */
	private $_ajustableSorting = false;

	/** @var string */
	private $_selectedSortby = '';

	/** @var string */
	private $_selectedSortorder = '';

	/** @var array */
	private $_sortbyUserValues = [];

	/** @var string */
	private $_sortbyDefaultValue = '';

	/** @var int */
	private $_sortbyUserDirection = 0;

	/**
	 * @param bool $isAdjustable
	 */
	public function setAdjustableSorting(bool $isAdjustable)
	{
		$this->_ajustableSorting = $isAdjustable;
	}

	/**
	 * @return bool
	 *
	 */

	public function isAdjustableSorting(): bool
	{
		return $this->_ajustableSorting;
	}

	/**
	 * @param string $sortby
	 */
	public function setSelectedSortby(string $sortby)
	{
		$this->_selectedSortby = $sortby;
	}

	/**
	 * @return string
	 */
	public function getSelectedSortby(): string
	{
		return $this->_selectedSortby;
	}

	/**
	 * @param string $sortorder
	 */
	public function setSelectedSortorder(string $sortorder)
	{
		$this->_selectedSortorder = $sortorder;
	}

	/**
	 * @return string
	 */
	public function getSelectedSortorder(): string
	{
		return $this->_selectedSortorder;
	}

	/**
	 * @param array $values
	 */
	public function setSortByUserValues(array $values)
	{
		$this->_sortbyUserValues = $values;
	}

	/**
	 * @param string $value
	 */
	public function addSortByUserValues(string $value)
	{
		$this->_sortbyUserValues []= $value;
	}

	/**
	 * @return array
	 */
	public function getSortByUserValues(): array
	{
		return $this->_sortbyUserValues;
	}


	public function setSortbyDefaultValue(string $value)
	{
		$this->_sortbyDefaultValue = $value;
	}

	/**
	 * @return string
	 */
	public function getSortbyDefaultValue(): string
	{
		return $this->_sortbyDefaultValue;
	}

	/**
	 * @param int $direction
	 */
	public function setSortbyUserDirection(int $direction)
	{
		$this->_sortbyUserDirection = $direction;
	}

	/**
	 * @return int
	 */
	public function getSortbyUserDirection(): int
	{
		return $this->_sortbyUserDirection;
	}
}