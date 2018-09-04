<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Controller\EstateListInputVariableReaderConfigFieldnames;

class GeoPosition
{
	/** */
	const MODUS_TYPE_ADMIN_INTERFACE = 'adminInterface';

	/** */
	const MODUS_TYPE_ADMIN_SEARCH_CRITERIA = 'adminSearchCriteria';

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
	private static $_geoPositionSettings =  array(
		onOfficeSDK::MODULE_ESTATE => array
			(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Geo Position',
				'content' => 'Geografische-Angaben',
			),
		onOfficeSDK::MODULE_SEARCHCRITERIA => array
			(
				'type' => FieldTypes::FIELD_TYPE_VARCHAR,
				'length' => 250,
				'permittedvalues' => array(),
				'default' => null,
				'label' => 'Geo Position',
				'content' => 'Search Criteria',
			),
		);

	/** @var array */
	private static $_settingsGeoPositionFields = array(
		onOfficeSDK::MODULE_ESTATE => array(
			'fields' => array(
				'laengengrad',
				'breitengrad',
				),
		),
		onOfficeSDK::MODULE_SEARCHCRITERIA => array(
			'fields' => array(
				'range_land',
				'range_plz',
				'range_strasse',
				'range',
			),
		),
	);


	/** @var array */
	private static $_estateSearchFields = array(
		self::ESTATE_LIST_SEARCH_COUNTRY,
		self::ESTATE_LIST_SEARCH_ZIP,
		self::ESTATE_LIST_SEARCH_STREET,
		self::ESTATE_LIST_SEARCH_RADIUS,
	);


	/** @var array */
	private static $_requiredRequestFields = array(
		self::ESTATE_LIST_SEARCH_COUNTRY,
		self::ESTATE_LIST_SEARCH_ZIP,
		self::ESTATE_LIST_SEARCH_RADIUS,
	);

	public function __construct()
		{ }

	/**
	 *
	 * @param array $fieldList
	 * @param string $modus
	 * @return array
	 *
	 */

	public function transform(array $fieldList, string $modus):array
	{
		$newFieldList = array();

		switch ($modus)	{
			case self::MODUS_TYPE_ADMIN_INTERFACE:
				$newFieldList = $this->transformAdminInterface($fieldList);
				break;

			case self::MODUS_TYPE_ADMIN_SEARCH_CRITERIA:
				$newFieldList = $this->transformAdminSearchCriteria($fieldList);
				break;
		}

		return $newFieldList;
	}


	/**
	 *
	 * @param array $fieldList
	 * @return array
	 *
	 */

	private function transformAdminInterface(array $fieldList):array
	{
		$geoPositionCounter = 0;
		$module = onOfficeSDK::MODULE_ESTATE;

		foreach ($fieldList as $fieldName => $properties) {
			if (in_array($fieldName, self::$_settingsGeoPositionFields[$module]['fields']))	{
				$geoPositionCounter++;
			}
		}


		if ($geoPositionCounter == count(self::$_settingsGeoPositionFields[$module]['fields']))	{
			foreach (self::$_settingsGeoPositionFields[$module]['fields'] as $field) {
				unset($fieldList[$field]);
			}

			$fieldList[self::FIELD_GEO_POSITION] = self::$_geoPositionSettings[$module];
		}

		return $fieldList;
	}



	/**
	 *
	 * @param array $fieldList
	 * @return array
	 *
	 */

	private function transformAdminSearchCriteria(array $fieldList):array
	{
		$counter = 0;
		$module = onOfficeSDK::MODULE_SEARCHCRITERIA;

		foreach ($fieldList as $fieldName => $properties) {
			if (in_array($fieldName, self::$_settingsGeoPositionFields[$module]['fields']))	{
				unset($fieldList[$fieldName]);
				$counter++;
			}
		}

		if ($counter == count(self::$_settingsGeoPositionFields[$module]['fields'])) {
			$fieldList[self::FIELD_GEO_POSITION] = self::$_geoPositionSettings[$module];
		}

		return $fieldList;
	}


	/**
	 *
	 * @param string $module
	 * @return array
	 *
	 */

	public function getSettingsGeoPositionFields(string $module):array
	{
		$result = array();

		if (array_key_exists($module, self::$_settingsGeoPositionFields)) {
			$result = self::$_settingsGeoPositionFields[$module]['fields'];
		}

		return $result;
	}


	/** @return array */
	public function getEstateSearchFields()
		{ return self::$_estateSearchFields; }


	/**
	 *
	 * @param array $inputs
	 * @return array
	 *
	 */

	public function createGeoRangeSearchParameterRequest(array $inputs)
	{
		$inputValues = array();

		foreach ($inputs as $key => $values) {
			$inputValues[$key] = $values;

			if (in_array($key, self::$_requiredRequestFields) &&
				$inputValues[$key] == '') {
				return null;
			}
		}

		return $inputValues;
	}
}