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

namespace onOffice\WPlugin\Field;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\__String;

class DistinctFieldsFilter
{
	/** */
	const NOT_ALLOWED_KEYS = ['s', '', 'oo_formid', 'oo_formno', 'Id'];

	/** */
	const NOT_ALLOWED_VALUES = [''];


	/** @var FieldsCollection */
	private $_pFieldsCollection = null;

	/** @var string */
	private $_module = '';


	/**
	 *
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 * @param string $module
	 *
	 */

	public function __construct(FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort, string $module)
	{
		$this->_pFieldsCollection = new FieldsCollection();
		$pFieldsCollectionBuilderShort->addFieldsAddressEstate($this->_pFieldsCollection);
		$pFieldsCollectionBuilderShort->addFieldsSearchCriteria($this->_pFieldsCollection);
		$this->_module = $module;
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	private function isMultiselectableType(string $field): bool
	{
		$pField = $this->_pFieldsCollection->getFieldByModuleAndName($this->_module, $field);
		return $this->_module == onOfficeSDK::MODULE_SEARCHCRITERIA &&
			in_array($pField->getType(),
				[FieldTypes::FIELD_TYPE_MULTISELECT, FieldTypes::FIELD_TYPE_SINGLESELECT]);
	}



	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	private function isNumericalType(string $field): bool
	{
		$pField = $this->_pFieldsCollection->getFieldByModuleAndName($this->_module, $field);
		return in_array($pField->getType(),
			[FieldTypes::FIELD_TYPE_FLOAT, FieldTypes::FIELD_TYPE_INTEGER]);
	}


	/**
	 *
	 * @param string $distinctField
	 * @param array $inputValues
	 * @return array
	 *
	 */

	public function filter(string $distinctField, array $inputValues): array
	{
		$filter = [];

		foreach ($inputValues as $key => $value) {
			if (in_array($key, self::NOT_ALLOWED_KEYS) || in_array($value, self::NOT_ALLOWED_VALUES)) {
				continue;
			}

			$pString = __String::getNew($key);
			$operator = null;
			$field = null;

			$key = $pString->replace('[]', '');

			if ($pString->endsWith('__von') && $this->_module == onOfficeSDK::MODULE_ESTATE) {
				$operator = '>=';
				$field = $pString->replace('__von', '');

				if (isset($filter[$field]) && $this->isNumericalType($field)) {
					$operator = 'between';
					$value1 = $value;
					$value2 = $filter[$field][0]['val'];
					$value = [$value1, $value2];
				}
			} elseif ($pString->endsWith('__bis') && $this->_module == onOfficeSDK::MODULE_ESTATE) {
				$operator = '<=';
				$field = $pString->replace('__bis', '');

				if (isset($filter[$field]) && $this->isNumericalType($field)) {
					$operator = 'between';
					$value1 = $filter[$field][0]['val'];
					$value2 = $value;
					$value = [$value1, $value2];
				}
			} else {
				if (is_array($value)) {
					$operator = 'in';
				} else {
					if ($this->isMultiselectableType($key)) {
						$operator = 'regexp';
					} else {
						$operator = '=';
					}
				}

				$field = $key;
			}

			if ($field === $distinctField) {
				continue;
			}

			if ($this->isNumericalType($field) && $this->_module === onOfficeSDK::MODULE_SEARCHCRITERIA) {
				if (!isset($filter[$field.'__von'])) {
					$filter[$field.'__von'] = [['op' => '<=', 'val' => $value]];
				}

				if (!isset($filter[$field.'__bis'])) {
					$filter[$field.'__bis'] = [['op' => '>=', 'val' => $value]];
				}
			} else {
				$filter[$field] = [['op' => $operator, 'val' => $value]];
			}
		}

		return $filter;
	}
}