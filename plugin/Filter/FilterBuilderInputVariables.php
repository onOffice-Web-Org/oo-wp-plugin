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

namespace onOffice\WPlugin\Filter;

use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Controller\InputVariableReaderConfig;
use onOffice\WPlugin\Controller\InputVariableReaderConfigFieldnames;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FilterBuilderInputVariables
{
	/** @var InputVariableReaderConfig */
	private $_pInputVariableReaderConf = null;

	/** @var string */
	private $_module = null;

	/** @var bool */
	private $_fuzzySearch = false;

	/**
	 * @param string $module
	 * @param bool $fuzzySearch
	 * @param InputVariableReaderConfig $pInputVariableReaderConf
	 */
	public function __construct(
		string $module,
		bool $fuzzySearch = false,
		InputVariableReaderConfig $pInputVariableReaderConf = null)
	{
		$this->_module = $module;
		$this->_fuzzySearch = $fuzzySearch;
		$this->_pInputVariableReaderConf = $pInputVariableReaderConf ??
			new InputVariableReaderConfigFieldnames();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getPostFieldsFilter(array $filterableFields): array
	{
		$filter = [];
		$pEstateInputVars = new InputVariableReader($this->_module, $this->_pInputVariableReaderConf);

		foreach ($filterableFields as $fieldInput) {
			try {
				$type = $pEstateInputVars->getFieldType($fieldInput);
				$value = $pEstateInputVars->getFieldValue($fieldInput);

				if (is_null($value) || (is_string($value) && __String::getNew($value)->isEmpty())) {
					continue;
				}

				$fieldFilter = $this->getFieldFilter($value, $type);
				$filter[$fieldInput] = $fieldFilter;
			} catch (\Exception $e) {
				$value = $pEstateInputVars->getFieldValue($fieldInput);

				if (is_null($value) || (is_string($value) && __String::getNew($value)->isEmpty())) {
					continue;
				}

				if ($value === '0' || $value === '1' || $value === 0 || $value === 1 || $value === true || $value === false) {
					$boolValue = ($value === '1' || $value === 1 || $value === true) ? 1 : 0;
					$fieldFilter = $this->getFieldFilter($boolValue, FieldTypes::FIELD_TYPE_INTEGER);
				} else {
					$fieldFilter = $this->getFieldFilter($value, 'text');
				}
				$filter[$fieldInput] = $fieldFilter;
			}
		}

		return $filter;
	}


	/**
	 *
	 * @param string|array $fieldValue
	 * @param string $type
	 * @return array
	 *
	 */

	private function getFieldFilter($fieldValue, string $type): array
	{
		$fieldFilter = [];
		if(is_string($fieldValue)){
			$fieldValue = html_entity_decode($fieldValue);
		}
		if (FieldTypes::isNumericType($type) || FieldTypes::isDateOrDateTime($type)) {
			if (!is_array($fieldValue)) {
				$fieldFilter []= ['op' => '=', 'val' => $fieldValue];
			} else {
				if (isset($fieldValue[0])) {
					$fieldFilter []= ['op' => '>=', 'val' => $fieldValue[0]];
				}

				if (isset($fieldValue[1])) {
					$fieldFilter []= ['op' => '<=', 'val' => $fieldValue[1]];
				}
			}
		} elseif ($type === FieldTypes::FIELD_TYPE_MULTISELECT ||
			$type === FieldTypes::FIELD_TYPE_SINGLESELECT) {
			$fieldFilter []= ['op' => 'in', 'val' => $fieldValue];
		} elseif ($type === FieldTypes::FIELD_TYPE_BOOLEAN) {
			$boolValue = ($fieldValue === true || $fieldValue === '1' || $fieldValue === 1) ? 1 : 0;
			$fieldFilter []= ['op' => '=', 'val' => $boolValue];
		} elseif ($type === FieldTypes::FIELD_TYPE_TEXT ||
			($type === FieldTypes::FIELD_TYPE_VARCHAR && $this->_fuzzySearch) && !is_array($fieldValue)) {
			$fieldFilter []= ['op' => 'like', 'val' => '%'.$fieldValue.'%'];
		} else {
			$fieldFilter []= ['op' => '=', 'val' => $fieldValue];
		}

		return $fieldFilter;
	}


	/** @return string */
	public function getModule(): string
		{ return $this->_module; }
}