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

use onOffice\WPlugin\Field\DefaultValue\DefaultValueEstateCreate;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelDate;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelMultiselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelNumericRange;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelSingleselect;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueModelText;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Record\RecordManagerInsertException;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */

class DefaultValueEstateRowSaver
{
	/** @var DefaultValueEstateCreate */
	private $_pDefaultValueCreate;

	/** @var Language */
	private $_pLanguage;

	/**
	 * @param DefaultValueEstateCreate $pDefaultValueCreate
	 * @param Language $pLanguage
	 */
	public function __construct(
		DefaultValueEstateCreate $pDefaultValueCreate,
		Language $pLanguage)
	{
		$this->_pDefaultValueCreate = $pDefaultValueCreate;
		$this->_pLanguage = $pLanguage;
	}

	/**
	 * @param int $estateId
	 * @param array $row
	 * @param FieldsCollection $pUsedFieldsCollection
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function saveDefaultValues(int $estateId, array $row, FieldsCollection $pUsedFieldsCollection)
	{
		foreach ($row as $field => $values) {
			$values = is_object($values) ? (array) $values : $values;
			if ($values !== [] && $values !== '') {

				$pField = $pUsedFieldsCollection->getFieldByKeyUnsafe($field);
				$this->saveForFoundType($estateId, $pField, $values);
			}
		}
	}

	/**
	 * @param int $estateId
	 * @param Field $pField
	 * @param mixed $values
	 * @throws RecordManagerInsertException
	 */
	private function saveForFoundType(int $estateId, Field $pField, $values)
	{
		$isSingleValue = $pField->getType() === FieldTypes::FIELD_TYPE_SINGLESELECT;
		$isMultiSelect = $pField->getType() === FieldTypes::FIELD_TYPE_MULTISELECT;
		$isBoolean = $pField->getType() === FieldTypes::FIELD_TYPE_BOOLEAN;
		$isStringType = FieldTypes::isStringType($pField->getType());
		$isDate =  FieldTypes::isDateOrDateTime($pField->getType());
		$isNumberType = FieldTypes::isNumericType($pField->getType());

		if ($pField->getIsRangeField() || $isNumberType) {
			$this->saveNumericRange($estateId, $pField, $values);
		} elseif ($isSingleValue || $isBoolean) {
			$this->saveGeneric($estateId, $pField, $values);
		} elseif ($isMultiSelect) {
			$this->saveMultiSelect($estateId, $pField, $values);
		} elseif ($isStringType) {
			$this->saveText($estateId, $pField, $values);
		} elseif ($isDate) {
			$this->saveDate($estateId, $pField, $values);
		}
	}

	/**
	 * @param int $estateId
	 * @param Field $pField
	 * @param string $value
	 * @throws RecordManagerInsertException
	 */
	private function saveGeneric(int $estateId, Field $pField, string $value)
	{
		$pModel = new DefaultValueModelSingleselect($estateId, $pField);
		$pModel->setValue($value);
		$this->_pDefaultValueCreate->createForSingleSelect($pModel);
	}

	/**
	 * @param int $estateId
	 * @param Field $pField
	 * @param array $values
	 * @throws RecordManagerInsertException
	 */
	private function saveMultiSelect(int $estateId, Field $pField, array $values)
	{
		$pModel = new DefaultValueModelMultiselect($estateId, $pField);
		$pModel->setValues($values);
		$this->_pDefaultValueCreate->createForMultiselect($pModel);
	}

	/**
	 *
	 * @param int $estateId
	 * @param Field $pField
	 * @param array $values
	 * @throws RecordManagerInsertException
	 */
	private function saveText(int $estateId, Field $pField, array $values)
	{
		$pModel = new DefaultValueModelText($estateId, $pField);

		foreach ($values as $locale => $value) {
			$this->addLocaleToModelForText($pModel, $locale, $value);
		}
		$this->_pDefaultValueCreate->createForText($pModel);
	}

	/**
	 * @param int $estateId
	 * @param Field $pField
	 * @param array $values
	 * @throws RecordManagerInsertException
	 */
	private function saveNumericRange(int $estateId, Field $pField, array $values)
	{
		$pModel = new DefaultValueModelNumericRange($estateId, $pField);
		$pModel->setValueFrom(floatval($values['min'] ?? .0));
		$pModel->setValueTo(floatval($values['max'] ?? .0));
		$this->_pDefaultValueCreate->createForNumericRange($pModel);
	}

	/**
	 * @param DefaultValueModelText $pModel
	 * @param string $locale
	 * @param string $value
	 */
	private function addLocaleToModelForText(DefaultValueModelText $pModel, string $locale, string $value)
	{
		if ($locale === 'native') {
			$locale = $this->_pLanguage->getLocale();
		}
		$pModel->addValueByLocale($locale, $value);
	}

	/**
	 * @param int $estateId
	 * @param Field $pField
	 * @param array $values
	 * @throws RecordManagerInsertException
	 */
	private function saveDate(int $estateId, Field $pField, array $values)
	{
		$pModel = new DefaultValueModelDate($estateId, $pField);
		$pModel->setValueFrom(($values['min']));
		$pModel->setValueTo(($values['max']));
		$this->_pDefaultValueCreate->createForDate($pModel);
	}
}