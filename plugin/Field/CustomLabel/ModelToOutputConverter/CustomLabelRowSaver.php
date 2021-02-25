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

namespace onOffice\WPlugin\Field\CustomLabel\ModelToOutputConverter;

use onOffice\WPlugin\Field\CustomLabel\CustomLabelCreate;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelModelText;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Record\RecordManagerInsertException;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 */
class CustomLabelRowSaver
{
	/** @var CustomLabelCreate */
	private $_pCustomLabelCreate;

	/** @var Language */
	private $_pLanguage;

	/**
	 * @param CustomLabelCreate $pCustomLabelCreate
	 * @param Language $pLanguage
	 */
	public function __construct(
		CustomLabelCreate $pCustomLabelCreate,
		Language $pLanguage
	) {
		$this->_pCustomLabelCreate = $pCustomLabelCreate;
		$this->_pLanguage = $pLanguage;
	}

	/**
	 * @param int $formId
	 * @param array $row
	 * @param FieldsCollection $pUsedFieldsCollection
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */
	public function saveCustomLabels(int $formId, array $row, FieldsCollection $pUsedFieldsCollection)
	{
		foreach ($row as $field => $values) {
			$values = is_object($values) ? (array)$values : $values;
			if ($values !== [] && $values !== '') {
				$pField = $pUsedFieldsCollection->getFieldByKeyUnsafe($field);
				$this->saveForFoundType($formId, $pField, $values);
			}
		}
	}

	/**
	 * @param int $formId
	 * @param Field $pField
	 * @param mixed $values
	 * @throws RecordManagerInsertException
	 */
	private function saveForFoundType(int $formId, Field $pField, $values)
	{
		$isStringType = FieldTypes::isStringType($pField->getType());

		if ($isStringType) {
			$this->saveText($formId, $pField, $values);
		}
	}

	/**
	 *
	 * @param int $formId
	 * @param Field $pField
	 * @param array $values
	 * @throws RecordManagerInsertException
	 */
	private function saveText(int $formId, Field $pField, array $values)
	{
		$pModel = new CustomLabelModelText($formId, $pField);

		foreach ($values as $locale => $value) {
			$this->addLocaleToModelForText($pModel, $locale, $value);
		}
		$this->_pCustomLabelCreate->createForText($pModel);
	}

	/**
	 * @param CustomLabelModelText $pModel
	 * @param string $locale
	 * @param string $value
	 */
	private function addLocaleToModelForText(CustomLabelModelText $pModel, string $locale, string $value)
	{
		if ($locale === 'native') {
			$locale = $this->_pLanguage->getLocale();
		}
		$pModel->addValueByLocale($locale, $value);
	}
}