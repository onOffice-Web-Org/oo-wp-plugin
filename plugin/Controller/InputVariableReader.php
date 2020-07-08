<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\Controller;

use onOffice\WPlugin\Types\FieldTypes;
use WP_Locale;
use function number_format_i18n;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputVariableReader
{
	/** @var InputVariableReaderConfig */
	private $_pConfig = null;

	/** @var string */
	private $_module = null;

	/**
	 * @param string $module
	 * @param InputVariableReaderConfig $pConfig
	 */
	public function __construct(string $module, InputVariableReaderConfig $pConfig = null)
	{
		$this->_module = $module;
		$this->_pConfig = $pConfig ?? new InputVariableReaderConfigFieldnames();
	}


	/**
	 *
	 * @param string $field
	 * @return array|string
	 *
	 */

	public function getFieldValue($field)
	{
		$type = $this->getFieldType($field);
		$fieldInputName = $field;
		$fieldValue = null;
		if (FieldTypes::isNumericType($type) ||
			FieldTypes::isDateOrDateTime($type)) {
			$fieldInputNameFrom = $fieldInputName.'__von';
			$fieldInputNameTo = $fieldInputName.'__bis';
			$fieldValueFrom = $this->getValueByFullInputNameAndType($fieldInputNameFrom, $type);
			$fieldValueTo = $this->getValueByFullInputNameAndType($fieldInputNameTo, $type);
			$fieldValue = array($fieldValueFrom, $fieldValueTo);
			if ($fieldValueFrom == 0 && $fieldValueTo == 0) {
				$fieldValue = $this->getValueByFullInputNameAndType($fieldInputName, $type);
			}
		} else {
			$fieldValue = $this->getValueByFullInputNameAndType($fieldInputName, $type);
		}

		return $fieldValue;
	}

	/**
	 *
	 * @param string $fieldInput
	 *
	 * @return array|string
	 */

	public function getFieldValueFormatted(string $fieldInput)
	{
		$fieldType = $this->getFieldType($fieldInput);
		$value = $this->getFieldValue($fieldInput);
		$valueFormatted = $this->formatValue($value, $fieldType);
		return $valueFormatted;
	}


	/**
	 *
	 * @param string|array|float $value
	 * @param string $type
	 * @return string|array
	 *
	 */

	private function formatValue($value, string $type)
	{
		$pFormatter = new InputVariableReaderFormatter;

		return $pFormatter->formatValue($value, $type);
	}


	/**
	 *
	 * @param string $fullInputName
	 * @param string $type
	 * @return array|string
	 *
	 */

	private function getValueByFullInputNameAndType(string $fullInputName, string $type)
	{
		$sanitizers = FieldTypes::getInputVarSanitizers();
		$sanitizer = $sanitizers[$type];

		// Important: don't use FILTER_NULL_ON_FAILURE
		// https://github.com/php/php-src/blob/c03ee1923057b62666a6a4144a9b2920e38b8765/ext/filter/filter.c#L744-L753

		$value = $this->getValue($fullInputName, $sanitizer);
		$pParser = new InputVariableReaderParser($this->_pConfig->getTimezoneString());
		return $pParser->parseValue($value, $type);
	}


	/**
	 *
	 * @param string $name
	 * @param int $filters
	 * @return mixed
	 *
	 */

	private function getValue(string $name, int $filters)
	{
		$getValue = $this->_pConfig->getFilterVariable(INPUT_GET, $name, $filters);
		$postValue = $this->_pConfig->getFilterVariable(INPUT_POST, $name, $filters);
		$value = $getValue ?? $postValue;
		if (is_array($value) && count($value) === 1 && key($value) === 0 &&
			!$this->_pConfig->getIsRequestVarArray($name)) {
			$value = $value[0];
		}

		return $value;
	}


	/**
	 *
	 * @param string $fieldName
	 * @return string
	 *
	 */

	public function getFieldType(string $fieldName): string
	{
		return $this->_pConfig->getFieldType($fieldName, $this->_module);
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getModule(): string
	{
		return $this->_module;
	}
}
