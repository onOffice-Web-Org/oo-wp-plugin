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
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;



/**
 *
 */

class FormFieldValidator
{

	/** @var FieldsCollection */
	private $_pFieldsCollection = null;

	private $_pFieldsCollectionBuilderShort = null;


	/** @var array */
	static private $_possibleModules = [
		onOfficeSDK::MODULE_ADDRESS,
		onOfficeSDK::MODULE_ESTATE,
		onOfficeSDK::MODULE_SEARCHCRITERIA,
	];

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
	 * @param array $formData
	 * @return array
	 *
	 */

	public function validate(array $formData): array
	{
		$this->_pFieldsCollectionBuilderShort->addFieldsAddressEstate($this->_pFieldsCollection);
		$this->_pFieldsCollectionBuilderShort->addFieldsSearchCriteria($this->_pFieldsCollection);

		$sanitizedData = [];

		foreach ($formData as $key => $value)
		{
			$sanitizedData[$key] = $this->validateField($key, $value);
		}

		return $sanitizedData;
	}


	/**
	 *
	 * @param string $fieldname
	 * @param mixed $value
	 * @return mixed
	 *
	 */

	private function validateField(string $fieldname, $value)
	{
		foreach (self::$_possibleModules as $module)
		{
			if ($this->_pFieldsCollection->containsFieldByModule($module, $fieldname))
			{
				$pField = $this->_pFieldsCollection->getFieldByModuleAndName($module, $fieldname);
				$type = $pField->getType();

				$value = $this->validateByType($value, $type);
				break;
			}
		}
		return $value;
	}


	/**
	 *
	 * @param mixed $value
	 * @param string $type
	 * @return mixed
	 *
	 */

	private function validateByType($value, string $type)
	{
		$filter = FILTER_DEFAULT;

		$filters = FieldTypes::getInputVarSanitizers();

		if (array_key_exists($type, $filters))
		{
			$filter = $filters[$type];
		}

		$options = null;

		if ($filter === FILTER_VALIDATE_INT)
		{
			$options = ['options' => ['default' => intval($value)]];
			$returnValue = filter_var($value, $filter, $options);
		}
		else
		{
			$returnValue = filter_var($value, $filter);

			if ($type == FieldTypes::FIELD_TYPE_FLOAT)
			{
				$returnValue = (float) $returnValue;
			}
		}

		return $returnValue;
	}
}