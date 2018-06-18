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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Utility\__String;
use WP_Locale;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class EstateListInputVariableReader
{
	/** @var EstateListInputVariableReaderConfig */
	private $_pConfig = null;


	/**
	 *
	 * @param EstateListInputVariableReaderConfig $pConfig
	 *
	 */

	public function __construct(EstateListInputVariableReaderConfig $pConfig = null)
	{
		$this->_pConfig = $pConfig;

		if ($this->_pConfig === null) {
			$this->_pConfig = new EstateListInputVariableReaderConfigFieldnames();
		}
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
		if (FieldTypes::isNumericType($type)) {
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

		$value = $this->_pConfig->getValue($fullInputName, $sanitizer, FILTER_FORCE_ARRAY);
		return $this->parseValue($value, $type);
	}


	/**
	 *
	 * @param mixed $value
	 * @param string $type
	 * @return string
	 *
	 */

	private function parseValue($value, $type)
	{
		if (is_array($value)) {
			return array_map(function($val) use ($type) {
				return $this->parseValue($val, $type);
			}, $value);
		} else if ($value === null) {
			return null;
		}

		switch ($type) {
			case FieldTypes::FIELD_TYPE_FLOAT:
				$value = $this->parseFloat($value);
				break;

			case FieldTypes::FIELD_TYPE_BOOLEAN:
				$value = $this->parseBool($value);
				break;
		}

		return $value;
	}


	/**
	 *
	 * @global WP_Locale $wp_locale
	 * @param string $floatString
	 * @return float
	 *
	 */

	private function parseFloat(string $floatString)
	{
		if (__String::getNew($floatString)->isEmpty()) {
			return null;
		}

		global $wp_locale;
		$stringThousand = __String::getNew($floatString)->replace
			($wp_locale->number_format['thousands_sep'] , '');
		$stringDec = __String::getNew($stringThousand)->replace
			($wp_locale->number_format['decimal_point'] , '.');

		return floatval($stringDec);
	}


	/**
	 *
	 * @param string $boolString
	 * @return bool
	 *
	 */

	private function parseBool(string $boolString)
	{
		if (__String::getNew($boolString)->isEmpty() || $boolString === 'u') {
			return null;
		}

		return $boolString === 'y';
	}


	/**
	 *
	 * @param string $fieldName
	 * @return string
	 *
	 */

	public function getFieldType(string $fieldName): string
	{
		return $this->_pConfig->getFieldType($fieldName, onOfficeSDK::MODULE_ESTATE);
	}
}
