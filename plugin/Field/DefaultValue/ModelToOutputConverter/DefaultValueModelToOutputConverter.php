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

namespace onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter;

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueRead;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */

class DefaultValueModelToOutputConverter
{
	/** @var DefaultValueModelToOutputConverterFactory */
	private $_pOutputConverterFactory;

	/** @var DefaultValueRead */
	private $_pDefaultValueReader;


	/**
	 *
	 * @param DefaultValueModelToOutputConverterFactory $pOutputConverterFactory
	 * @param DefaultValueRead $pDefaultValueReader
	 *
	 */

	public function __construct(
		DefaultValueModelToOutputConverterFactory $pOutputConverterFactory,
		DefaultValueRead $pDefaultValueReader)
	{
		$this->_pOutputConverterFactory = $pOutputConverterFactory;
		$this->_pDefaultValueReader = $pDefaultValueReader;
	}


	/**
	 *
	 * @param int $formId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	public function getConvertedField(int $formId, Field $pField): array
	{
		if ($pField->getIsRangeField()) {
			return $this->convertNumericRange($formId, $pField);
		}

		switch ($pField->getType()) {
			case FieldTypes::FIELD_TYPE_SINGLESELECT:
				return $this->convertSingleSelect($formId, $pField);
			case FieldTypes::FIELD_TYPE_MULTISELECT:
				return $this->convertMultiSelect($formId, $pField);

			case FieldTypes::FIELD_TYPE_TEXT:
			case FieldTypes::FIELD_TYPE_VARCHAR:
				return $this->convertText($formId, $pField);
		}
		return [];
	}


	/**
	 *
	 * @param int $formId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 *
	 */

	private function convertSingleSelect(int $formId, Field $pField): array
	{
		$pModel = $this->_pDefaultValueReader->readDefaultValuesSingleselect($formId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForSingleSelect();
		return $pConverter->convertToRow($pModel);
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function convertMultiSelect(int $formId, Field $pField): array
	{
		$pModel = $this->_pDefaultValueReader->readDefaultValuesMultiSelect($formId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForMultiSelect();
		return $pConverter->convertToRow($pModel);
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function convertText(int $formId, Field $pField): array
	{
		$pModel = $this->_pDefaultValueReader->readDefaultValuesText($formId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForText();
		return $pConverter->convertToRow($pModel);
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function convertNumericRange(int $formId, Field $pField): array
	{
		$pModel = $this->_pDefaultValueReader->readDefaultValuesNumericRange($formId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForNumericRange();
		return $pConverter->convertToRow($pModel);
	}
}