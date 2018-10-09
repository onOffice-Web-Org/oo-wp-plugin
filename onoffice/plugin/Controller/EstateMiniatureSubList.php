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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

interface EstateMiniatureSubList
{
	/**
	 *
	 * load the actual data
	 *
	 * @param EstateListBase $pEstateList
	 *
	 */

	public function loadByMainEstates(EstateListBase $pEstateList);

	/**
	 *
	 * @param int $mainEstateId
	 * @return string
	 *
	 */

	public function generateHtmlOutput(int $mainEstateId): string;


	/**
	 *
	 * @param int $estateId
	 * @return int[]
	 *
	 */

	public function getSubEstateIds(int $estateId): array;


	/**
	 *
	 * @param int $estateId
	 * @return int
	 *
	 */

	public function getSubEstateCount(int $estateId): int;
}
