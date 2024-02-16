<?php

/**
 *
 *    Copyright (C) 2023 onOffice GmbH
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

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */

class EstateFields
{
	/** @var FieldsCollectionBuilderShort */
	private $_pFieldsCollectionBuilder = null;

	/**
	 * @param FieldsCollectionBuilderShort $pFieldsCollectionBuilder
	 */
	public function __construct(FieldsCollectionBuilderShort $pFieldsCollectionBuilder)
	{
		$this->_pFieldsCollectionBuilder = $pFieldsCollectionBuilder;
	}

	/**
	 * @param array $inputFormFields
	 * @param FieldsCollection $pFieldsCollection
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	public function getFieldLabelsOfInputs(array $inputFormFields, FieldsCollection $pFieldsCollection): array
	{
		$this->_pFieldsCollectionBuilder
			->addFieldsAddressEstateWithRegionValues($pFieldsCollection);

		$output = [];
		foreach ($inputFormFields as $name => $value) {
			$module = onOfficeSDK::MODULE_ESTATE;
			$pField = $pFieldsCollection->getFieldByModuleAndName($module, $name);

			if (FieldTypes::isMultipleSelectType($pField->getType())) {
				if (is_array($value)) {
					$tmpOutput = [];
					foreach ($value as $val) {
						$tmpOutput []= (array_key_exists($val, $pField->getPermittedvalues()) ? $pField->getPermittedvalues()[$val] : $val);
					}
					$output[$pField->getLabel()] = implode(', ', $tmpOutput);
				} else {
					$output[$pField->getLabel()] = (array_key_exists($value, $pField->getPermittedvalues()) ? $pField->getPermittedvalues()[$value] : $value);
				}
			} elseif (FieldTypes::FIELD_TYPE_BOOLEAN === $pField->getType()) {
				if ($value == '1') {
					$output[$pField->getLabel()] = __('Yes', 'onoffice-for-wp-websites');
				} else {
					$output[$pField->getLabel()] = __('No', 'onoffice-for-wp-websites');
				}
			} else {
				$output[$pField->getLabel()] = $value;
			}
		}

		return $output;
	}
}
