<?php

/**
 *
 *    Copyright (C) 2019 onOffice Software AG
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
 *
 */

declare (strict_types=1);

namespace onOffice\WPlugin\Form;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\InputVariableReaderFormatter;
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */

class FormFieldValidator
{
	/** @var RequestVariablesSanitizer */
	private $_pRequestSanitizer;

	/** @var SearchcriteriaFields */
	private $_pSearchcriteriaFields = null;

	/** @var array */
	private $_multipleSingleSelectAllowed = [
		'objekttyp',
		'regionaler_zusatz',
	];

	/**
	 *
	 * @param RequestVariablesSanitizer $pRequestSanitizer
	 * @param SearchcriteriaFields $pSearchcriteriaFields
	 *
	 */

	public function __construct(
			RequestVariablesSanitizer $pRequestSanitizer,
			SearchcriteriaFields $pSearchcriteriaFields)
	{
		$this->_pRequestSanitizer = $pRequestSanitizer;
		$this->_pSearchcriteriaFields = $pSearchcriteriaFields;
	}

	/**
	 * @param array $formFields
	 * @param FieldsCollection $pFieldsCollection
	 * @return array
	 *
	 * @throws UnknownFieldException
	 */
	public function getValidatedValues(array $formFields, FieldsCollection $pFieldsCollection): array
	{
		$sanitizedData = [];

		foreach ($formFields as $fieldName => $module) {
			$name = $this->_pSearchcriteriaFields->getFieldNameOfInput($fieldName);
			$pField = $pFieldsCollection->getFieldByModuleAndName($module, $name);
			$dataType = $pField->getType();

			if (!$this->isEmptyValue($fieldName, $dataType)) {
				$value = $this->getValueFromRequest($dataType, $fieldName, $module, $name);
				$sanitizedData[$fieldName] = $this->getValidatedValue($value, $pField);
			}
		}

		return $sanitizedData;
	}


	/**
	 *
	 * @param mixed $value
	 * @param Field $pField
	 * @return mixed
	 *
	 */

	private function getValidatedValue($value, Field $pField)
	{
		$returnValue = $value;

		if (FieldTypes::isMultipleSelectType($pField->getType()) &&
			!FieldTypes::isRegZusatzSearchcritTypes($pField->getType())) {
			if (is_array($value) && $value != []) {
				$returnValue = array_intersect($value, array_keys($pField->getPermittedvalues()));
			} elseif (in_array($value, array_keys($pField->getPermittedvalues()))){
				$returnValue = $value;
			}
		}
		return $returnValue;
	}

	/**
	 *
	 * @param string $fieldName
	 * @param string $type
	 * @return bool
	 *
	 */

	private function isEmptyValue(string $fieldName, string $type = null): bool
	{
		if (FieldTypes::isMultipleSelectType($type)) {
			$value = $this->_pRequestSanitizer->getFilteredPost($fieldName, FILTER_DEFAULT, FILTER_FORCE_ARRAY);
			return array_filter($value) === [];
		}

		if ($type === 'boolean') {
			return false;
		}

		$value = $this->_pRequestSanitizer->getFilteredPost($fieldName, FILTER_SANITIZE_STRING);
		return trim($value) === '';
	}


	/**
	 *
	 * @param string $fieldName
	 * @param string $module
	 * @return bool
	 *
	 */

	private function isMultipleSingleSelectAllowed(string $fieldName, string $module): bool
	{
		return $module == onOfficeSDK::MODULE_SEARCHCRITERIA &&
				in_array($fieldName, $this->_multipleSingleSelectAllowed);
	}


	/**
	 *
	 * @param string $dataType
	 * @param string $fieldName
	 * @param string $module
	 * @param string $name
	 * @return mixed
	 *
	 */

	private function getValueFromRequest(string $dataType, string $fieldName, string $module, string $name)
	{
		$filter = FILTER_DEFAULT;
		$filters = FieldTypes::getInputVarSanitizers();

		if (array_key_exists($dataType, $filters)) {
			$filter = $filters[$dataType];
		}

		if ($filter === FILTER_VALIDATE_INT) {
			$filter = FILTER_SANITIZE_STRING;
		}

		if ($dataType == FieldTypes::FIELD_TYPE_MULTISELECT ||
			$this->isMultipleSingleSelectAllowed($fieldName, $module)){
			$returnValue = $this->_pRequestSanitizer->getFilteredPost($fieldName, FILTER_DEFAULT, FILTER_FORCE_ARRAY);
		} else {
			$returnValue = $this->_pRequestSanitizer->getFilteredPost($fieldName, $filter);
		}

		if (in_array($name, InputVariableReaderFormatter::APPLY_THOUSAND_SEPARATOR_FIELDS)) {
			$onofficeSettingsThousandSeparator = get_option('onoffice-settings-thousand-separator');
			switch ($onofficeSettingsThousandSeparator) {
				case InputVariableReaderFormatter::COMMA_THOUSAND_SEPARATOR:
					$returnValue = str_replace(',', '', $returnValue);
					break;
				case InputVariableReaderFormatter::DOT_THOUSAND_SEPARATOR:
					$returnValue = str_replace(['.', ','], ['', '.'], $returnValue);
					break;
			}
		}

		switch ($dataType) {
			case FieldTypes::FIELD_TYPE_INTEGER:
				$returnValue = (int) $returnValue;
				break;

			case FieldTypes::FIELD_TYPE_FLOAT:
				$returnValue = (float) $returnValue;
				break;

			case FieldTypes::FIELD_TYPE_BOOLEAN:
				if (empty($returnValue)) {
					$returnValue = 'n';
				}
				break;
		}

		return $returnValue;
	}
}