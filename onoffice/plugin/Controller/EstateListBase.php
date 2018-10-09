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

namespace onOffice\WPlugin\Controller;

use onOffice\WPlugin\ArrayContainer;
use onOffice\WPlugin\DataView\DataView;
use onOffice\WPlugin\Filter\DefaultFilterBuilder;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */


interface EstateListBase
{
	/**
	 *
	 * @param DataView $pDataView
	 *
	 */

	public function __construct(DataView $pDataView);


	/**
	 *
	 * @return ArrayContainer|bool
	 *
	 */

	public function estateIterator();


	/**
	 *
	 * performs the request
	 *
	 */

	public function loadEstates();


	/**
	 *
	 * @return int[]
	 *
	 */

	public function getEstateIds(): array;


	/**
	 *
	 * resets internal iterator handle
	 *
	 */

	public function resetEstateIterator();


	/**
	 *
	 * @return DefaultFilterBuilder
	 *
	 */

	public function getDefaultFilterBuilder(): DefaultFilterBuilder;


	/**
	 *
	 * @param DefaultFilterBuilder $pDefaultFilterBuilder
	 *
	 */

	public function setDefaultFilterBuilder(DefaultFilterBuilder $pDefaultFilterBuilder);


	/** @return DataView */
	public function getDataView(): DataView;


	/** @return bool */
	public function getShuffleResult(): bool;

	/** @param bool $shuffleResult */
	public function setShuffleResult(bool $shuffleResult);
}
