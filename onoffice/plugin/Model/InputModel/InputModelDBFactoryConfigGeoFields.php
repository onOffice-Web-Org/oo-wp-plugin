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

namespace onOffice\WPlugin\Model\InputModel;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\Exception\UnknownModuleException;
use onOffice\WPlugin\GeoPosition;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class InputModelDBFactoryConfigGeoFields
	implements InputModelDBFactoryConfigBase
{
	/** */
	const FIELDNAME_COUNTRY_ACTIVE = GeoPosition::ESTATE_LIST_SEARCH_COUNTRY.self::SUFFIX_ACTIVE;

	/** */
	const FIELDNAME_STREET_ACTIVE = GeoPosition::ESTATE_LIST_SEARCH_STREET.self::SUFFIX_ACTIVE;

	/** */
	const FIELDNAME_ZIP_ACTIVE = GeoPosition::ESTATE_LIST_SEARCH_ZIP.self::SUFFIX_ACTIVE;

	/** */
	const FIELDNAME_CITY_ACTIVE = GeoPosition::ESTATE_LIST_SEARCH_CITY.self::SUFFIX_ACTIVE;

	/** */
	const FIELDNAME_RADIUS_ACTIVE = GeoPosition::ESTATE_LIST_SEARCH_RADIUS.self::SUFFIX_ACTIVE;

	/** */
	const FIELDNAME_RADIUS = GeoPosition::ESTATE_LIST_SEARCH_RADIUS;


	/** */
	const SUFFIX_ACTIVE = '_active';


	/** */
	const MODULE_TO_TABLE = [
		onOfficeSDK::MODULE_ESTATE => 'oo_plugin_listviews',
		onOfficeSDK::MODULE_SEARCHCRITERIA => 'oo_plugin_forms',
	];

	/** @var string */
	private $_module = '';


	/**
	 *
	 * @param string $module
	 *
	 */

	public function __construct(string $module)
	{
		$this->_module = $module;
	}


	/**
	 *
	 * @return array
	 * @throws UnknownModuleException
	 *
	 */

	public function getConfig(): array
	{
		$table = self::MODULE_TO_TABLE[$this->_module] ?? null;

		if ($table === null) {
			$pException = new UnknownModuleException;
			$pException->setModule($this->_module);
			throw $pException;
		}

		return [
			self::FIELDNAME_COUNTRY_ACTIVE => [
				self::KEY_TABLE => $table,
				self::KEY_FIELD => self::FIELDNAME_COUNTRY_ACTIVE,
			],
			self::FIELDNAME_STREET_ACTIVE => [
				self::KEY_TABLE => $table,
				self::KEY_FIELD => self::FIELDNAME_STREET_ACTIVE,
			],
			self::FIELDNAME_ZIP_ACTIVE => [
				self::KEY_TABLE => $table,
				self::KEY_FIELD => self::FIELDNAME_ZIP_ACTIVE,
			],
			self::FIELDNAME_CITY_ACTIVE => [
				self::KEY_TABLE => $table,
				self::KEY_FIELD => self::FIELDNAME_CITY_ACTIVE,
			],
			self::FIELDNAME_RADIUS_ACTIVE => [
				self::KEY_TABLE => $table,
				self::KEY_FIELD => self::FIELDNAME_RADIUS_ACTIVE,
			],
			self::FIELDNAME_RADIUS => [
				self::KEY_TABLE => $table,
				self::KEY_FIELD => self::FIELDNAME_RADIUS,
			],
		];
	}


	/**
	 *
	 * @return array all activate-X fields
	 *
	 */

	public function getBooleanFields(): array
	{
		return [
			GeoPosition::ESTATE_LIST_SEARCH_COUNTRY => self::FIELDNAME_COUNTRY_ACTIVE,
			GeoPosition::ESTATE_LIST_SEARCH_STREET => self::FIELDNAME_STREET_ACTIVE,
			GeoPosition::ESTATE_LIST_SEARCH_ZIP => self::FIELDNAME_ZIP_ACTIVE,
			GeoPosition::ESTATE_LIST_SEARCH_CITY => self::FIELDNAME_CITY_ACTIVE,
			GeoPosition::ESTATE_LIST_SEARCH_RADIUS => self::FIELDNAME_RADIUS_ACTIVE,
		];
	}
}
