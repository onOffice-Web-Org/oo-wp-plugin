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

use onOffice\WPlugin\Fieldnames;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Types\FieldTypes;

class DistinctFieldsFilter
{


	/**  */
	const NOT_ALLOWED_KEYS = ['s', '', 'oo_formid', 'oo_formno'];

	/**  */
	const NOT_ALLOWED_VALUES = [''];


	/** @var Fieldnames */
	private $_pFieldnames = null;


	/**
	 *
	 * @param Fieldnames $pFieldnames
	 * @param string $module
	 *
	 */

	public function __construct(Fieldnames $pFieldnames, string $module)
	{
		$this->_pFieldnames = $pFieldnames;
		$this->_module = $module;
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	private function isMultiselectableType(string $field):bool
	{
		return $this->_module == onOfficeSDK::MODULE_SEARCHCRITERIA &&
						in_array($this->_pFieldnames->getType($field, onOfficeSDK::MODULE_ESTATE),
							[FieldTypes::FIELD_TYPE_MULTISELECT, FieldTypes::FIELD_TYPE_SINGLESELECT]);
	}



	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	private function isNumericalType(string $field):bool
	{
		return in_array($this->_pFieldnames->getType($field, onOfficeSDK::MODULE_ESTATE),
							[FieldTypes::FIELD_TYPE_FLOAT, FieldTypes::FIELD_TYPE_INTEGER]);
	}


	/**
	 *
	 * @param string $distinctField
	 * @param array $inputValues
	 * @return array
	 *
	 */

	public function filter(string $distinctField, array $inputValues):array
	{
		$filter = [];

		foreach ($inputValues as $key => $value)
		{
			if (in_array($key, self::NOT_ALLOWED_KEYS) ||
				in_array($value, self::NOT_ALLOWED_VALUES))
			{
				continue;
			}

			$pString = new __String($key);
			$operator = null;
			$field = null;

			$key = $pString->replace('[]', '');

			if ($pString->endsWith('__von') &&
				$this->_module == onOfficeSDK::MODULE_ESTATE)
			{
				$operator = '>=';
				$field = $pString->replace('__von', '');

				if (array_key_exists($field, $filter) &&
						$this->isNumericalType($field))
				{
					$operator = 'between';
					$value1 = $value;
					$value2 = $filter[$field][0]['val'];

					$value = [$value1, $value2];
				}
			}
			elseif ($pString->endsWith('__bis') &&
					$this->_module == onOfficeSDK::MODULE_ESTATE)
			{
				$operator = '<=';
				$field = $pString->replace('__bis', '');

				if (array_key_exists($field, $filter) &&
						$this->isNumericalType($field))
				{
					$operator = 'between';

					$value1 = $filter[$field][0]['val'];
					$value2 = $value;

					$value = [$value1, $value2];
				}
			}
			else
			{
				if (is_array($value))
				{
					$operator = 'in';
				}
				else
				{
					if ($this->isMultiselectableType($key))
					{
						$operator = 'regexp';
					}
					else
					{
						$operator = '=';
					}
				}

				$field = $key;
			}

			if ($field == $distinctField)
			{
				continue;
			}

			if ($this->isNumericalType($field) &&
					$this->_module == onOfficeSDK::MODULE_SEARCHCRITERIA )
			{
				if (!array_key_exists($field.'__von', $filter))
				{
					$filter[$field.'__von'] = [array('op' => '<=', 'val' => $value)];
				}

				if (!array_key_exists($field.'__bis', $filter))
				{
					$filter[$field.'__bis'] = [array('op' => '>=', 'val' => $value)];
				}
			}
			else
			{
				$filter[$field] = [array('op' => $operator, 'val' => $value)];
			}
		}

		return $filter;
	}
}