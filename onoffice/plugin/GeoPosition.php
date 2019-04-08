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

use onOffice\SDK\onOfficeSDK;

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
	const ESTATE_LIST_SEARCH_STREET = 'street';

	/** */
	const ESTATE_LIST_SEARCH_RADIUS = 'radius';


	/** @var array */
	private $_settingsGeoPositionFields = [
		onOfficeSDK::MODULE_ESTATE => [
			'laengengrad',
			'breitengrad',
		],
		onOfficeSDK::MODULE_SEARCHCRITERIA => [
			'range_land',
			'range_plz',
			'range_strasse',
			'range',
		],
	];

	/** @var array */
	private $_settingsGeoPositionFieldsWithoutRange = [
		'range_land',
		'range_plz',
		'range_strasse',
	];


	/** @var array */
	private $_estateSearchFields = [
		self::ESTATE_LIST_SEARCH_COUNTRY,
		self::ESTATE_LIST_SEARCH_ZIP,
		self::ESTATE_LIST_SEARCH_STREET,
		self::ESTATE_LIST_SEARCH_RADIUS,
	];


	/**
	 *
	 * @param string $module
	 * @return array
	 *
	 */

	public function getSettingsGeoPositionFields(string $module): array
	{
		return $this->_settingsGeoPositionFields[$module] ?? [];
	}


	/** @return array */
	public function getSettingsGeoPositionFieldsWithoutRadius(): array
		{ return $this->_settingsGeoPositionFieldsWithoutRange; }

	/** @return array */
	public function getEstateSearchFields(): array
		{ return $this->_estateSearchFields; }
}