<?php

/**
 *
 *    Copyright (C) 2021 onOffice GmbH
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

namespace onOffice\WPlugin\Field\CustomLabel\ModelToOutputConverter;

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelRead;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */
class CustomLabelModelToOutputConverter
{
	/** @var CustomLabelModelToOutputConverterFactory */
	private $_pOutputConverterFactory;

	/** @var CustomLabelRead */
	private $_pCustomLabelReader;


	/**
	 *
	 * @param CustomLabelModelToOutputConverterFactory $pOutputConverterFactory
	 * @param CustomLabelRead $pCustomLabelReader
	 *
	 */

	public function __construct(
		CustomLabelModelToOutputConverterFactory $pOutputConverterFactory,
		CustomLabelRead $pCustomLabelReader
	) {
		$this->_pOutputConverterFactory = $pOutputConverterFactory;
		$this->_pCustomLabelReader = $pCustomLabelReader;
	}


	/**
	 * @param int $formId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getConvertedField(int $formId, Field $pField): array
	{
		$isStringType = FieldTypes::isStringType($pField->getType());

		if ($isStringType) {
			return $this->convertText($formId, $pField);
		}
		return [];
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
		$pModel = $this->_pCustomLabelReader->readCustomLabelsText($formId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForText();
		return $pConverter->convertToRow($pModel);
	}
}