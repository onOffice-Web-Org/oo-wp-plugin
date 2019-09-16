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
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\RequestVariablesSanitizer;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;



/**
 *
 */

class FormFieldValidator
{

	/** @var FieldsCollection */
	private $_pFieldsCollection = null;

	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilderShort = null;

	/** @var RequestVariablesSanitizer */
	private $_pRequestSanitizer = null;

	/**
	 *
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort
	 *
	 */

	public function __construct(FieldsCollectionBuilderShort $pFieldsCollectionBuilderShort)
	{
		$this->_pFieldsCollection = new FieldsCollection();
		$this->_pFieldsCollectionBuilderShort = $pFieldsCollectionBuilderShort;
	}


	/**
	 *
	 * @param array $formFields
	 * @return array
	 *
	 */

	public function getValidatedValues(array $formFields): array
	{
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($this->_pFieldsCollection);
		$this->_pFieldsCollectionBuilderShort->addFieldsSearchCriteria($this->_pFieldsCollection);
		$this->_pRequestSanitizer = new RequestVariablesSanitizer;

		$sanitizedData = [];

		foreach ($formFields as $fieldName => $module){
			if (!$this->isEmptyValue($fieldName)){
				$dataType = FieldTypes::FIELD_TYPE_VARCHAR;
				if ($module != null)
				{
					$dataType = $this->getTypeByFieldname($fieldName, $module);
				}
				$sanitizedData[$fieldName] = $this->getValueFromRequest($dataType, $fieldName);
			}
		}

		return $sanitizedData;
	}


	/**
	 *
	 * @param type $fieldName
	 * @return bool
	 *
	 */

	private function isEmptyValue(string $fieldName): bool
	{
		$value = $this->_pRequestSanitizer->getFilteredPost($fieldName, FILTER_SANITIZE_STRING);

		return trim($value) === '';
	}


	/**
	 *
	 * @param string $fieldname
	 * @return string
	 *
	 */

	private function getTypeByFieldname(string $fieldname, string $module): string
	{
		$type = FieldTypes::FIELD_TYPE_VARCHAR;

		if ($this->_pFieldsCollection->containsFieldByModule($module, $fieldname)){
			$pField = $this->_pFieldsCollection->getFieldByModuleAndName($module, $fieldname);
			$type = $pField->getType();
		}

		return $type;
	}


	/**
	 *
	 * @param string $dataType
	 * @param string $fieldName
	 * @return mixed
	 *
	 */

	private function getValueFromRequest(string $dataType, string $fieldName)
	{
		$filter = FILTER_DEFAULT;
		$filters = FieldTypes::getInputVarSanitizers();

		if (array_key_exists($dataType, $filters)){
			$filter = $filters[$dataType];
		}

		if ($filter === FILTER_VALIDATE_INT){
			$filter = FILTER_SANITIZE_STRING;
		}

		$returnValue = $this->_pRequestSanitizer->getFilteredPost($fieldName, $filter);

		switch ($dataType){
			case FieldTypes::FIELD_TYPE_INTEGER:
				$returnValue = (int) $returnValue;
				break;

			case FieldTypes::FIELD_TYPE_FLOAT:
				$returnValue = (float) $returnValue;
				break;
		}

		return $returnValue;
	}
}