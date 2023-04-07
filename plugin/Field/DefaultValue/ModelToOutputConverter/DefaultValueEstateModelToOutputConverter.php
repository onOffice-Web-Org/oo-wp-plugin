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

namespace onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter;

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueEstateRead;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */

class DefaultValueEstateModelToOutputConverter
{
	/** @var DefaultValueModelToOutputConverterFactory */
	private $_pOutputConverterFactory;

	/** @var DefaultValueEstateRead */
	private $_pDefaultValueReader;


	/**
	 *
	 * @param DefaultValueModelToOutputConverterFactory $pOutputConverterFactory
	 * @param DefaultValueEstateRead $pDefaultValueReader
	 *
	 */

	public function __construct(
		DefaultValueModelToOutputConverterFactory $pOutputConverterFactory,
		DefaultValueEstateRead $pDefaultValueReader)
	{
		$this->_pOutputConverterFactory = $pOutputConverterFactory;
		$this->_pDefaultValueReader = $pDefaultValueReader;
	}


	/**
	 * @param int $estateId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getConvertedField(int $estateId, Field $pField): array
	{
		$isSingleValue = FieldTypes::isDateOrDateTime($pField->getType()) ||
			FieldTypes::isNumericType($pField->getType()) ||
			$pField->getType() === FieldTypes::FIELD_TYPE_SINGLESELECT;
		$isMultiSelect = $pField->getType() === FieldTypes::FIELD_TYPE_MULTISELECT;
		$isBoolean = $pField->getType() === FieldTypes::FIELD_TYPE_BOOLEAN;
		$isStringType = FieldTypes::isStringType($pField->getType());
		$isRegZusatz = FieldTypes::isRegZusatzSearchcritTypes($pField->getType());

		if ($pField->getIsRangeField()) {
			return $this->convertNumericRange($estateId, $pField);
		} elseif ($isSingleValue) {
			return $this->convertGeneric($estateId, $pField);
		} elseif ($isMultiSelect) {
			return $this->convertMultiSelect($estateId, $pField);
		} elseif ($isBoolean) {
			return $this->convertBoolean($estateId, $pField);
		} elseif ($isStringType) {
			return $this->convertText($estateId, $pField);
		} elseif ($isRegZusatz) {
			$pField->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
			return $this->convertMultiSelect($estateId, $pField);
		}
		return [];
	}


	/**
	 *
	 * @param int $estateId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	private function convertGeneric(int $estateId, Field $pField): array
	{
		$pModel = $this->_pDefaultValueReader->readDefaultValuesSingleselect($estateId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForSingleSelect();
		return $pConverter->convertToRow($pModel);
	}

	/**
	 * @param int $estateId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function convertMultiSelect(int $estateId, Field $pField): array
	{
		$pModel = $this->_pDefaultValueReader->readDefaultValuesMultiSelect($estateId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForMultiSelect();
		return $pConverter->convertToRow($pModel);
	}

	/**
	 * @param int $estateId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function convertText(int $estateId, Field $pField): array
	{
		$pModel = $this->_pDefaultValueReader->readDefaultValuesText($estateId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForText();
		return $pConverter->convertToRow($pModel);
	}

	/**
	 * @param int $estateId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function convertNumericRange(int $estateId, Field $pField): array
	{
		$pModel = $this->_pDefaultValueReader->readDefaultValuesNumericRange($estateId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForNumericRange();
		return $pConverter->convertToRow($pModel);
	}

	/**
	 * @param int $estateId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function convertBoolean(int $estateId, Field $pField): array
	{
		$pModel = $this->_pDefaultValueReader->readDefaultValuesBool($estateId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForBool();
		return $pConverter->convertToRow($pModel);
	}
}