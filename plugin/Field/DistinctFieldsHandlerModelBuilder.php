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

namespace onOffice\WPlugin\Field;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\WP\WPScriptStyleBase;
use function json_decode;

/**
 *
 */

class DistinctFieldsHandlerModelBuilder
{
	/** @var RequestVariablesSanitizer */
	private $_pRequestVariables = null;


	/**
	 *
	 * @param RequestVariablesSanitizer $pRequestVariables
	 * @param WPScriptStyleBase $pScriptStyle
	 *
	 */

	public function __construct(
		RequestVariablesSanitizer $pRequestVariables)
	{
		$this->_pRequestVariables = $pRequestVariables;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getDistinctValues(): array
	{
		$values = $this->_pRequestVariables->getFilteredPost
			(DistinctFieldsHandler::PARAMETER_DISTINCT_VALUES,
				FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
		if ($values === false) {
			$values = [];
		}
		return $values;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getInputValues(): array
	{
		return json_decode($this->_pRequestVariables->getFilteredPost
			(DistinctFieldsHandler::PARAMETER_INPUT_VALUES), true) ?? [];
	}


	/**
	 *
	 * @return DistinctFieldsHandlerModel
	 *
	 */

	public function buildDataModel(): DistinctFieldsHandlerModel
	{
		$module = $this->_pRequestVariables->getFilteredPost(DistinctFieldsHandler::PARAMETER_MODULE) ?? '';
		$inputValuesRaw = $this->getInputValues();

		$allInputValues = $this->buildInputValuesForModule($module, $inputValuesRaw);
		$inputValues = $allInputValues['inputValues'];
		$geoValues = $allInputValues['geoValues'];

		$pDataModel = new DistinctFieldsHandlerModel();
		$pDataModel->setModule($module);
		$pDataModel->setDistinctFields($this->getDistinctValues());
		$pDataModel->setInputValues($inputValues);
		$pDataModel->setGeoPositionFields($geoValues);

		return $pDataModel;
	}


	/**
	 *
	 * @param string $module
	 * @param array $inputValues
	 * @return array
	 *
	 */

	private function buildInputValuesForModule(string $module, array $inputValues): array
	{
		$pGeoPosition = new GeoPosition();
		$geoRangeValues = [];

		if ($module === onOfficeSDK::MODULE_ESTATE) {
			foreach ($inputValues as $key => $values) {
				if (in_array($key, $pGeoPosition->getEstateSearchFields())) {
					$geoRangeValues[$key] = $values;
					unset($inputValues[$key]);
				}
			}
		} elseif ($module == onOfficeSDK::MODULE_SEARCHCRITERIA) {
			$mapping = array_flip($pGeoPosition->getSearchCriteriaFields());
			foreach ($inputValues as $key => $values) {
				if (isset($mapping[$key])) {
					$geoRangeValues[$mapping[$key]] = $values;
					unset($inputValues[$key]);
				}
			}
		}

		return ['inputValues' => $inputValues, 'geoValues' => $geoRangeValues];
	}
}