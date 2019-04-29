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

namespace onOffice\WPlugin;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class GeoPosition
{
	/** */
	const FIELD_GEO_POSITION = 'geoPosition';

	/** */
	const ESTATE_LIST_SEARCH_COUNTRY = 'country';

	/** */
	const ESTATE_LIST_SEARCH_ZIP = 'zip';

	/** */
	const ESTATE_LIST_SEARCH_CITY = 'city';

	/** */
	const ESTATE_LIST_SEARCH_STREET = 'street';

	/** */
	const ESTATE_LIST_SEARCH_RADIUS = 'radius';


	/** @var array */
	private $_estateSearchFields = [
		self::ESTATE_LIST_SEARCH_COUNTRY,
		self::ESTATE_LIST_SEARCH_ZIP,
		self::ESTATE_LIST_SEARCH_CITY,
		self::ESTATE_LIST_SEARCH_STREET,
		self::ESTATE_LIST_SEARCH_RADIUS,
	];


	/** @var array */
	private $_searchcriteriaFields = [
		self::ESTATE_LIST_SEARCH_COUNTRY => 'range_land',
		self::ESTATE_LIST_SEARCH_ZIP => 'range_plz',
		self::ESTATE_LIST_SEARCH_CITY => 'range_ort',
		self::ESTATE_LIST_SEARCH_STREET => 'range_strasse',
		self::ESTATE_LIST_SEARCH_RADIUS => 'range',
	];


	/** @return array */
	public function getEstateSearchFields(): array
		{ return $this->_estateSearchFields; }

	/** @return array */
	public function getSearchCriteriaFields(): array
		{ return $this->_searchcriteriaFields; }
}