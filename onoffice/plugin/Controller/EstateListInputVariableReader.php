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
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class EstateListInputVariableReader
{
	/** @var Fieldnames */
	private $_pFieldNames = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pFieldNames = new Fieldnames();
		$this->_pFieldNames->loadLanguage();
	}


	/**
	 *
	 * @param string $field
	 * @return array|string
	 *
	 */

	public function getFieldValue($field)
	{
		$fieldInformation = $this->_pFieldNames->getFieldInformation
			($field, onOfficeSDK::MODULE_ESTATE);
		$type = $fieldInformation['type'];
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

	private function getValueByFullInputNameAndType($fullInputName, $type)
	{
		$sanitizers = FieldTypes::getInputVarSanitizers();
		$sanitizer = $sanitizers[$type];
		$getValue = filter_input(INPUT_GET, $fullInputName, $sanitizer, FILTER_FORCE_ARRAY);
		$postValue = filter_input(INPUT_POST, $fullInputName, $sanitizer, FILTER_FORCE_ARRAY);
		$value = $getValue ? $getValue : $postValue;

		if (is_array($value) && count($value) === 1 && key($value) === 0 &&
			!is_array($_REQUEST[$fullInputName])) {
			$value = $value[0];
		}

		return $value;
	}
}
