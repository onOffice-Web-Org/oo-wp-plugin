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

use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class GeoPosition
{
	/** */
	const MODE_TYPE_ADMIN_INTERFACE = 'adminInterface';

	/** */
	const MODE_TYPE_ADMIN_SEARCH_CRITERIA = 'adminSearchCriteria';

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
	private $_geoPositionSettings = [
		onOfficeSDK::MODULE_ESTATE => [
			'type' => FieldTypes::FIELD_TYPE_VARCHAR,
			'length' => 250,
			'permittedvalues' => [],
			'default' => null,
			'label' => 'Geo Position',
			'content' => 'Geografische-Angaben',
			'tablename' => '',
			'module' => onOfficeSDK::MODULE_SEARCHCRITERIA,
		],
		onOfficeSDK::MODULE_SEARCHCRITERIA => [
			'type' => FieldTypes::FIELD_TYPE_VARCHAR,
			'length' => 250,
			'permittedvalues' => [],
			'default' => null,
			'label' => 'Geo Position',
			'content' => 'Search Criteria',
			'tablename' => '',
			'module' => onOfficeSDK::MODULE_SEARCHCRITERIA,
		],
	];

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


	/** @var array */
	private $_requiredRequestFields = [
		self::ESTATE_LIST_SEARCH_COUNTRY,
		self::ESTATE_LIST_SEARCH_ZIP,
		self::ESTATE_LIST_SEARCH_RADIUS,
	];


	/**
	 *
	 * @param array $fieldList
	 * @param string $mode
	 * @return array
	 * @throws Exception
	 *
	 */

	public function transform(array $fieldList, string $mode): array
	{
		$modeToModule = [
			self::MODE_TYPE_ADMIN_INTERFACE => onOfficeSDK::MODULE_ESTATE,
			self::MODE_TYPE_ADMIN_SEARCH_CRITERIA => onOfficeSDK::MODULE_SEARCHCRITERIA,
		];

		if (!isset($modeToModule[$mode])) {
			throw new Exception('Unknown Mode!');
		}

		$newFieldList = $this->transformAdmin($fieldList, $modeToModule[$mode]);

		return $newFieldList;
	}


	/**
	 *
	 * @param array $fieldList
	 * @return array
	 *
	 */

	private function transformAdmin(array $fieldList, string $module): array
	{
		$geoPositionFields = $this->_settingsGeoPositionFields[$module];
		$allFields = array_keys($fieldList);

		$hasAllGeoPosFields = array_intersect($geoPositionFields, $allFields) == $geoPositionFields;

		if ($hasAllGeoPosFields) {
			$fieldList = array_diff_key($fieldList, array_flip($geoPositionFields));
			$fieldList[self::FIELD_GEO_POSITION] = $this->_geoPositionSettings[$module];
		}

		return $fieldList;
	}


	/**
	 *
	 * @param array $inputs
	 * @return array
	 *
	 */

	public function createGeoRangeSearchParameterRequest(array $inputs): array
	{
		$inputValues = [];
		$radius = empty($inputs[self::ESTATE_LIST_SEARCH_RADIUS]) ? 10 :
			$inputs[self::ESTATE_LIST_SEARCH_RADIUS];
		$inputs[self::ESTATE_LIST_SEARCH_RADIUS] = $radius;

		foreach ($inputs as $key => $values) {
			$inputValues[$key] = $values;

			if (in_array($key, $this->_requiredRequestFields) && empty($values)) {
				return [];
			}
		}

		return $inputValues;
	}


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