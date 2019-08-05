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
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\__String;

/**
 *
 */

class DistinctFieldsFilter
{
	/** */
	const NOT_ALLOWED_KEYS = ['s', '', 'oo_formid', 'oo_formno', 'Id'];

	/** */
	const NOT_ALLOWED_VALUES = [''];


	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;



	/**
	 *
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 *
	 */

	public function __construct(FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort)
	{
		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
	}


	/**
	 *
	 * @param Field $pField
	 * @return bool
	 *
	 */

	private function isMultiselectableType(Field $pField): bool
	{
		return $pField->getModule() == onOfficeSDK::MODULE_SEARCHCRITERIA &&
			in_array($pField->getType(),
				[FieldTypes::FIELD_TYPE_MULTISELECT, FieldTypes::FIELD_TYPE_SINGLESELECT]);
	}



	/**
	 *
	 * @param string $distinctField
	 * @param array $inputValues
	 * @param string $module
	 * @return array
	 *
	 */

	public function filter(string $distinctField, array $inputValues, string $module): array
	{
		$filter = [];

		$pFieldsCollection = new FieldsCollection();

		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$this->_pFieldsCollectionBuilderShort->addFieldsSearchCriteria($pFieldsCollection);

		foreach ($inputValues as $key => $value) {
			if (in_array($key, self::NOT_ALLOWED_KEYS) || in_array($value, self::NOT_ALLOWED_VALUES)) {
				continue;
			}

			$pString = __String::getNew($key);
			$operator = null;
			$field = null;


			$key = $pString->replace('[]', '');

			if ($pString->endsWith('__von') && $module == onOfficeSDK::MODULE_ESTATE) {
				$operator = '>=';
				$field = $pString->replace('__von', '');
				$pField = $pFieldsCollection->getFieldByModuleAndName($module, $field);

				if (isset($filter[$field]) && FieldTypes::isNumericType($pField->getType())) {
					$operator = 'between';
					$value1 = $value;
					$value2 = $filter[$field][0]['val'];
					$value = [$value1, $value2];
				}
			} elseif ($pString->endsWith('__bis') && $module == onOfficeSDK::MODULE_ESTATE) {
				$operator = '<=';
				$field = $pString->replace('__bis', '');
				$pField = $pFieldsCollection->getFieldByModuleAndName($module, $field);

				if (isset($filter[$field]) && FieldTypes::isNumericType($pField->getType())) {
					$operator = 'between';
					$value1 = $filter[$field][0]['val'];
					$value2 = $value;
					$value = [$value1, $value2];
				}
			} else {
				if (is_array($value)) {
					$operator = 'in';
				} else {
					$pField = $pFieldsCollection->getFieldByModuleAndName($module, $key);
					if ($this->isMultiselectableType($pField)) {
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

			$pField = $pFieldsCollection->getFieldByModuleAndName($module, $field);
			if (FieldTypes::isNumericType($pField->getType()) && $module === onOfficeSDK::MODULE_SEARCHCRITERIA) {
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