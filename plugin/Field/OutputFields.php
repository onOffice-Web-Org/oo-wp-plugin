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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\GeoPositionFieldHandlerBase;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\DataView\DataViewFilterableFields;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\FieldsCollection;

/**
 *
 */

class OutputFields
{
	/** @var DataViewFilterableFields */
	private $_pDataView = null;

	/** @var InputVariableReader */
	private $_pInputVariableReader = null;

	/** @var GeoPositionFieldHandlerBase */
	private $_pGeoPositionFieldHandler = null;

	/** @var CompoundFieldsFilter */
	private $_pCompoundFieldsFilter = null;

	/**
	 *
	 * @param DataViewFilterableFields $pDataListView
	 * @param GeoPositionFieldHandlerBase $pGeoPositionFieldHandler
	 * @param CompoundFieldsFilter $pCompoundFieldsFilter
	 * @param InputVariableReader $pInputVariableReader
	 * 
	 */

	public function __construct(
		DataViewFilterableFields $pDataListView,
		GeoPositionFieldHandlerBase $pGeoPositionFieldHandler,
		CompoundFieldsFilter $pCompoundFieldsFilter,
		InputVariableReader $pInputVariableReader = null)
	{
		$this->_pDataView = $pDataListView;
		$this->_pInputVariableReader = $pInputVariableReader ??
			new InputVariableReader($pDataListView->getModule());
		$this->_pGeoPositionFieldHandler = $pGeoPositionFieldHandler;
		$this->_pCompoundFieldsFilter = $pCompoundFieldsFilter;
	}


	/**
	 *
	 * @return string[] An array of visible fields
	 *
	 */

	public function getVisibleFilterableFields(FieldsCollection $pFieldsCollection): array
	{
		$filterable = $this->_pDataView->getFilterableFields();
		$hidden = $this->_pDataView->getHiddenFields();

		$fieldsArray = array_diff($filterable, $hidden);
		$posGeo = array_search(GeoPosition::FIELD_GEO_POSITION, $fieldsArray);
		$geoFields = [];
		if ($posGeo !== false) {
			unset($fieldsArray[$posGeo]);
			$this->_pGeoPositionFieldHandler->readValues($this->_pDataView);
			$geoFields = $this->_pGeoPositionFieldHandler->getActiveFieldsWithValue();
		}

		$allFields = array_merge($fieldsArray, array_keys($geoFields));
		$allFields = $this->filterActiveFields($allFields, $pFieldsCollection);

		$valuesDefault = array_map(function($field) use ($geoFields) {
			return $this->_pInputVariableReader->getFieldValueFormatted($field) ?? $geoFields[$field] ?? null;
		}, $allFields);

		$resultDefault = array_combine($allFields, $valuesDefault);

		$result = $this->_pCompoundFieldsFilter->mergeListFilterableFields(
				$pFieldsCollection, $resultDefault);

		return $result;
	}

	/**
	 * @param $allFields
	 * @param FieldsCollection $pFieldsCollection
	 * @return array
	 */
	private function filterActiveFields($allFields, FieldsCollection $pFieldsCollection): array
	{
		$activeFields = [];
		foreach ($allFields as $field){
			if ($pFieldsCollection->containsFieldByModule(onOfficeSDK::MODULE_ESTATE, $field)) {
				$activeFields []= $field;
			}
		}
		return $activeFields;
	}

	/** @return DataViewFilterableFields */
	public function getDataView(): DataViewFilterableFields
		{ return $this->_pDataView; }

	/** @return InputVariableReader */
	public function getInputVariableReader(): InputVariableReader
		{ return $this->_pInputVariableReader; }
}
