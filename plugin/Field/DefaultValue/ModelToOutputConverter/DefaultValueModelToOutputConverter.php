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
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelBool;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelMultiselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelNumericRange;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelSingleselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelText;
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
	 * @param int $formId
	 * @param array $pFields
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function getConvertedMultiFields(int $formId, array $pFields): array
	{
		$pDataModels = [];
		$rows = $this->_pDefaultValueReader->readDefaultMultiValuesSingleSelect($formId, $pFields);

		foreach ($pFields as $pField) {
			$rowData = array_values(array_filter($rows, function ($row) use ($pField) {
				return $pField->getName() == $row->fieldname;
			}));

			if (count($rowData)) {
				switch ($pField) {
					case $pField->getIsRangeField():
						$pDataModel = $this->createDefaultValuesNumericRange($formId, $pField, $rowData);
						$pDataModels[ $pField->getName() ] = $pDataModel[0] ?? '';
						$pDataModels[ $pField->getName() . '__von' ] = $pDataModel['min'] ?? '';
						$pDataModels[ $pField->getName() . '__bis' ] = $pDataModel['max'] ?? '';
						break;
					case FieldTypes::isDateOrDateTime($pField->getType()) || FieldTypes::isNumericType($pField->getType()) || $pField->getType() === FieldTypes::FIELD_TYPE_SINGLESELECT;
						$pDataModel = $this->createDefaultValueModelSingleSelect($formId, $pField, $rowData);
						$pDataModels[ $pField->getName() ] = $pDataModel[0] ?? '';
						break;
					case $pField->getType() === FieldTypes::FIELD_TYPE_MULTISELECT;
						$pDataModel = $this->createDefaultValuesMultiSelect($formId, $pField, $rowData);
						$pDataModels[ $pField->getName() ] = $pDataModel[0] ?? '';
						$pDataModels[ $pField->getName() ] = $pDataModel;
						break;
					case $pField->getType() === FieldTypes::FIELD_TYPE_BOOLEAN;
						$pDataModel = $this->createDefaultValuesBool($formId, $pField, $rowData);
						$pDataModels[ $pField->getName() ] = $pDataModel[0] ?? '';
						break;
					case FieldTypes::isStringType($pField->getType());
						$pDataModel = $this->createDefaultValuesText($formId, $pField, $rowData);
						$pDataModels[ $pField->getName() ] = $pDataModel[0] ?? '';
						$pDataModels[ $pField->getName() ] = ($pDataModel['native'] ?? '') ?: (array_shift($pDataModel) ?? '');
						break;
					case FieldTypes::isRegZusatzSearchcritTypes($pField->getType());
						$pField->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
						$pDataModel = $this->createDefaultValuesMultiSelect($formId, $pField, $rowData);
						$pDataModels[ $pField->getName() ] = $pDataModel[0] ?? '';
						break;
				}
			}
		}

		return $pDataModels;
	}

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getConvertedMultiFieldsForAdmin(int $formId, array $pFields): array
    {
        $pDataModels = [];
        $rows = $this->_pDefaultValueReader->readDefaultMultiValuesSingleSelect($formId, $pFields);

        foreach ($pFields as $pField) {
            $rowData = array_values(array_filter($rows, function ($row) use ($pField) {
                return $pField->getName() == $row->fieldname;
            }));
            switch ($pField) {
                case $pField->getIsRangeField():
                    $pDataModel = $this->createDefaultValuesNumericRange($formId, $pField, $rowData);
                    break;
                case FieldTypes::isDateOrDateTime($pField->getType()) || FieldTypes::isNumericType($pField->getType()) || $pField->getType() === FieldTypes::FIELD_TYPE_SINGLESELECT;
                    $pDataModel = $this->createDefaultValueModelSingleSelect($formId, $pField, $rowData);
                    break;
                case $pField->getType() === FieldTypes::FIELD_TYPE_MULTISELECT;
                    $pDataModel = $this->createDefaultValuesMultiSelect($formId, $pField, $rowData);
                    break;
                case $pField->getType() === FieldTypes::FIELD_TYPE_BOOLEAN;
                    $pDataModel = $this->createDefaultValuesBool($formId, $pField, $rowData);
                    break;
                case FieldTypes::isStringType($pField->getType());
                    $pDataModel = $this->createDefaultValuesText($formId, $pField, $rowData);
                    break;
                case FieldTypes::isRegZusatzSearchcritTypes($pField->getType());
                    $pField->setType(FieldTypes::FIELD_TYPE_MULTISELECT);
                    $pDataModel = $this->createDefaultValuesMultiSelect($formId, $pField, $rowData);
                    break;
            }
            if (isset($pDataModel)) $pDataModels[ $pField->getName() ] = $pDataModel;
        }

        return $pDataModels;
    }

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @param array $rows
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function createDefaultValuesNumericRange(int $formId, Field $pField, array $rows): array
	{
		$pDataModel = new DefaultValueModelNumericRange($formId, $pField);

		if (count($rows) === 2) {
			$pDataModel->setValueFrom((float) $rows[0]->value);
			$pDataModel->setValueTo((float) $rows[1]->value);
		}

		$pConverter = $this->_pOutputConverterFactory->createForNumericRange();
		return $pConverter->convertToRow($pDataModel);
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @param array $rows
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function createDefaultValueModelSingleSelect(int $formId, Field $pField, array $rows): array
	{
		$pDataModel = new DefaultValueModelSingleselect($formId, $pField);
		$pDataModel->setDefaultsId(isset($rows[0]->defaults_id) ? (int) $rows[0]->defaults_id : 0);
		$pDataModel->setValue($rows[0]->value ?? '');

		$pConverter = $this->_pOutputConverterFactory->createForSingleSelect();
		return $pConverter->convertToRow($pDataModel);
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @param array $rows
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function createDefaultValuesMultiSelect(int $formId, Field $pField, array $rows): array
	{
		$values = array_column($rows, 'value');
		$pDataModel = new DefaultValueModelMultiselect($formId, $pField);
		$pDataModel->setValues($values);

		$pConverter = $this->_pOutputConverterFactory->createForMultiSelect();
		return $pConverter->convertToRow($pDataModel);
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @param array $rows
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function createDefaultValuesBool(int $formId, Field $pField, array $rows): array
	{
		$pDataModel = new DefaultValueModelBool($formId, $pField);
		$pDataModel->setDefaultsId(isset($rows[0]->defaults_id) ? (int)$rows[0]->defaults_id : 0);
		$pDataModel->setValue(isset($rows[0]->value) && !empty($rows[0]->value) && (bool)intval($rows[0]->value));

		$pConverter = $this->_pOutputConverterFactory->createForBool();
		return $pConverter->convertToRow($pDataModel);
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @param array $rows
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function createDefaultValuesText(int $formId, Field $pField, array $rows): array
	{
		$pDataModel = new DefaultValueModelText($formId, $pField);

		foreach ($rows as $pRow) {
			$pDataModel->addValueByLocale($pRow->locale, $pRow->value);
		}

		$pConverter = $this->_pOutputConverterFactory->createForText();
		return $pConverter->convertToRow($pDataModel);
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

	private function convertGeneric(int $formId, Field $pField): array
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

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function convertBoolean(int $formId, Field $pField): array
	{
		$pModel = $this->_pDefaultValueReader->readDefaultValuesBool($formId, $pField);
		$pConverter = $this->_pOutputConverterFactory->createForBool();
		return $pConverter->convertToRow($pModel);
	}
}