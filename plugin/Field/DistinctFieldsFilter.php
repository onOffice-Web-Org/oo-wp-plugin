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
		return $pField->getType() === FieldTypes::FIELD_TYPE_MULTISELECT ||
			($pField->getModule() === onOfficeSDK::MODULE_SEARCHCRITERIA &&
			$pField->getType() === FieldTypes::FIELD_TYPE_SINGLESELECT);
	}


	/**
	 *
	 * @param string $key
	 * @param string $distinctField
	 * @return boolean
	 *
	 */

	public function isDistinctField(string $key, string $distinctField)
	{
		$field = str_replace(['[]','__von', '__bis'], '', $key);

		return $field === $distinctField;
	}

	/**
	 * @param string $distinctField
	 * @param array $inputValues
	 * @param string $module
	 * @param array $possibleDistinctFields
	 * @return array
	 * @throws UnknownFieldException
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */

	public function filter(string $distinctField, array $inputValues, string $module, array $possibleDistinctFields): array
	{
		$filter = [];
		$pFieldsCollection = new FieldsCollection();

		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$this->_pFieldsCollectionBuilderShort->addFieldsSearchCriteria($pFieldsCollection);

		foreach ($inputValues as $key => $value) {
			$pString = __String::getNew($key);
			$key = $pString->replace('[]', '');

			if (in_array($key, self::NOT_ALLOWED_KEYS) ||
				in_array($value, self::NOT_ALLOWED_VALUES) ||
				$this->isDistinctField($key, $distinctField) ||
				!in_array($key, $possibleDistinctFields)) {
				continue;
			}

			if ($pString->endsWith('__von') && $module == onOfficeSDK::MODULE_ESTATE){
				$field = $pString->replace('__von', '');
				$filter[$field] = [$this->filterForEstateVon($filter, $key, $value)];
			} elseif ($pString->endsWith('__bis') && $module == onOfficeSDK::MODULE_ESTATE){
				$field = $pString->replace('__bis', '');
				$filter[$field] = [$this->filterForEstateBis($filter, $key, $value)];
			} else {
				$pField = $pFieldsCollection->getFieldByModuleAndName($module, $key);
				$field = $key;

				if ($module == onOfficeSDK::MODULE_SEARCHCRITERIA &&
						FieldTypes::isRangeType($pField->getType())) {
					if (!isset($filter[$field.'__von'])) {
						$filter[$field.'__von'] = [['op' => '<=', 'val' => $value]];
					}

					if (!isset($filter[$field.'__bis'])) {
						$filter[$field.'__bis'] = [['op' => '>=', 'val' => $value]];
					}
				} else {
					$filter[$field] = [$this->createDefaultFilter($pField, $value)];
				}
			}
		}

		return $filter;
	}


	/**
	 *
	 * @param Field $pField
	 * @param mixed $value
	 * @return array
	 *
	 */

	private function createDefaultFilter(Field $pField, $value)
	{
		$filter = [];

		if ($this->isMultiselectableType($pField) || is_array($value)) {
			$filter = ['op' => 'in', 'val' => $value];
		} else {
			$filter = ['op' => '=', 'val'=> $value];
		}

		return $filter;
	}


	/**
	 *
	 * @param array $filter
	 * @param string $field
	 * @param mixed $value
	 * @return array
	 *
	 */

	private function filterForEstateVon(array $filter, string $field, $value)
	{
		$operator = '>=';
		$field = __String::getNew($field)->replace('__von', '');

		if (isset($filter[$field])) {
			$operator = 'between';
			$value1 = $value;
			$value2 = $filter[$field][0]['val'];
			$value = [$value1, $value2];
		}

		return ['op' => $operator, 'val' => $value];
	}


	/**
	 *
	 * @param array $filter
	 * @param string $field
	 * @param mixed $value
	 * @return array
	 *
	 */

	private function filterForEstateBis(array $filter, string $field, $value)
	{
		$operator = '<=';
		$field = __String::getNew($field)->replace('__bis', '');

		if (isset($filter[$field])) {
			$operator = 'between';
			$value1 = $filter[$field][0]['val'];
			$value2 = $value;
			$value = [$value1, $value2];
		}

		return ['op' => $operator, 'val' => $value];
	}
}