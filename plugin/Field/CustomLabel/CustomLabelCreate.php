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

namespace onOffice\WPlugin\Field\CustomLabel;

use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerInsertException;
use onOffice\WPlugin\Record\RecordManagerInsertGeneric;


/**
 *
 */
class CustomLabelCreate
{

	/** @var RecordManagerFactory */
	private $_pRecordManagerFactory;


	/**
	 *
	 * @param RecordManagerFactory $pRecordManagerFactory
	 *
	 */

	public function __construct(RecordManagerFactory $pRecordManagerFactory)
	{
		$this->_pRecordManagerFactory = $pRecordManagerFactory;
	}


	/**
	 *
	 * @param CustomLabelModelField $pDataModel
	 * @return int
	 * @throws RecordManagerInsertException
	 */

	public function createForField(CustomLabelModelField $pDataModel, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField): int
	{
		$field = $pDataModel->getField()->getName();
		$customsLabelsId = $this->writeDatabaseGeneral($pDataModel->getFormId(), $field, $pCustomsLabelConfigurationField);
		foreach ($pDataModel->getValuesByLocale() as $locale => $value) {
			$this->writeDatabaseValueSingle($customsLabelsId, $value, $locale, $pTranslateLabelConfigurationField);
		}
		return $customsLabelsId;
	}

	/**
	 *
	 * Step one: write oo_fieldconfig_form_customs_labels
	 * step two: write to oo_fieldconfig_estate_translated_labels
	 *
	 * @param int $formId
	 * @param string $field
	 * @return int
	 * @throws RecordManagerInsertException
	 *
	 */

	private function writeDatabaseGeneral(int $formId, string $field, $pCustomsLabelConfigurationField): int
	{
		$pRecordManager = $this->createRecordManagerDefaults($pCustomsLabelConfigurationField);
		$values = [
			'form_id' => $formId,
			'fieldname' => $field,
		];
		return $pRecordManager->insertByRow([$pCustomsLabelConfigurationField => $values]);
	}


	/**
	 *
	 * step two: write to oo_fieldconfig_form_translated_labels
	 * step two: write to oo_fieldconfig_estate_translated_labels
	 *
	 * @param int $customsLabelsId
	 * @param string $value
	 * @param string $locale
	 * @param string $pTranslateLabelConfigurationField
	 *
	 * @throws RecordManagerInsertException
	 *
	 */

	private function writeDatabaseValueSingle(int $customsLabelsId, string $value, string $locale = '', $pTranslateLabelConfigurationField = null)
	{
		$pRecordManager = $this->createRecordManagerCustomsLabels($pTranslateLabelConfigurationField);
		$values = [
			'input_id' => $customsLabelsId,
			'locale' => $locale,
			'value' => $value,
		];
		$pRecordManager->insertByRow([$pTranslateLabelConfigurationField => $values]);
	}


	/**
	 *
	 * @return RecordManagerInsertGeneric
	 *
	 */

	private function createRecordManagerDefaults($pCustomsLabelConfigurationField): RecordManagerInsertGeneric
	{
		return $this->_pRecordManagerFactory->createRecordManagerInsertGeneric($pCustomsLabelConfigurationField);
	}


	/**
	 *
	 * @return RecordManagerInsertGeneric
	 *
	 */

	private function createRecordManagerCustomsLabels($pTranslateLabelConfigurationField): RecordManagerInsertGeneric
	{
		return $this->_pRecordManagerFactory->createRecordManagerInsertGeneric($pTranslateLabelConfigurationField);
	}
}
