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

declare(strict_types=1);

namespace onOffice\WPlugin\Field;

use onOffice\WPlugin\Controller\GeoPositionFieldHandlerBase;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Controller\InputVariableReaderConfigFieldsCollection;
use onOffice\WPlugin\DataView\DataViewFilterableFields;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */
class OutputFields
{
	/** @var CompoundFieldsFilter */
	private $_pCompoundFieldsFilter;

	/**
	 * @param CompoundFieldsFilter $pCompoundFieldsFilter
	 */
	public function __construct(
		CompoundFieldsFilter $pCompoundFieldsFilter)
	{
		$this->_pCompoundFieldsFilter = $pCompoundFieldsFilter;
	}

	/**
	 * @param DataViewFilterableFields $pDataView
	 * @param FieldsCollection $pFieldsCollection
	 * @param GeoPositionFieldHandlerBase $pGeoPositionFieldHandler
	 * @param InputVariableReader|null $pInputVariableReader
	 * @return string[] An array of visible fields
	 */
	public function getVisibleFilterableFields(
		DataViewFilterableFields $pDataView,
		FieldsCollection $pFieldsCollection,
		GeoPositionFieldHandlerBase $pGeoPositionFieldHandler,
		InputVariableReader $pInputVariableReader = null): array
	{
		$filterable = $pDataView->getFilterableFields();
		$hidden = $pDataView->getHiddenFields();
		$pInputVariableReader = $pInputVariableReader ?? new InputVariableReader($pDataView->getModule());

		$fieldsArray = array_diff($filterable, $hidden);
		$posGeo = array_search(GeoPosition::FIELD_GEO_POSITION, $fieldsArray);
		$geoFields = [];
		if ($posGeo !== false) {
			unset($fieldsArray[$posGeo]);
			$pGeoPositionFieldHandler->readValues($pDataView);
			$geoFields = $pGeoPositionFieldHandler->getActiveFieldsWithValue();
		}

		$allFields = array_merge($fieldsArray, array_keys($geoFields));
		$valuesDefault = array_map(function($field) use ($geoFields, $pInputVariableReader) {
			$value = $pInputVariableReader->getFieldValueFormatted($field);
			return $value === false ? false : ($value ?? null);
		}, $allFields);

		$resultDefault = array_combine($allFields, $valuesDefault);

		if ($posGeo !== false &&
			empty($resultDefault[GeoPosition::ESTATE_LIST_SEARCH_CITY]) &&
			empty($resultDefault[GeoPosition::ESTATE_LIST_SEARCH_ZIP])) {
			$emptyGeo = array_combine(array_keys($geoFields), array_fill(0, count($geoFields), null));
			$resultDefault = array_merge($resultDefault, $emptyGeo);
		}

		$result = $this->_pCompoundFieldsFilter->mergeListFilterableFields
			($pFieldsCollection, $resultDefault);

		return $result;
	}
}
