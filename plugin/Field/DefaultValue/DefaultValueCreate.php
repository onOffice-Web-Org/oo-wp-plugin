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

namespace onOffice\WPlugin\Field\DefaultValue;

use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerInsertException;
use onOffice\WPlugin\Record\RecordManagerInsertGeneric;


/**
 *
 */

class DefaultValueCreate
{
	/** */
	const TABLE_DEFAULTS = 'oo_plugin_fieldconfig_form_defaults';

	/** */
	const TABLE_DEFAULTS_VALUES = 'oo_plugin_fieldconfig_form_defaults_values';


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
	 * @param DefaultValueModelText $pDataModel
	 * @return int
	 * @throws RecordManagerInsertException
	 */

	public function createForText(DefaultValueModelText $pDataModel): int
	{
		$defaultsId = $this->createBase($pDataModel);

		foreach ($pDataModel->getValuesByLocale() as $locale => $value) {
			$this->writeDatabaseValueSingle($defaultsId, $value, $locale);
		}

		return $defaultsId;
	}

	/**
	 *
	 * @param DefaultValueModelSingleselect $pDataModel
	 *
	 * @return int
	 * @throws RecordManagerInsertException
	 */

	public function createForSingleselect(DefaultValueModelSingleselect $pDataModel): int
	{
		$defaultsId = $this->createBase($pDataModel);
		$this->writeDatabaseValueSingle($defaultsId, $pDataModel->getValue());
		return $defaultsId;
	}


	/**
	 *
	 * @param DefaultValueModelMultiselect $pDataModel
	 *
	 * @return int
	 * @throws RecordManagerInsertException
	 *
	 */

	public function createForMultiselect(DefaultValueModelMultiselect $pDataModel): int
	{
		$defaultsId = $this->createBase($pDataModel);

		foreach ($pDataModel->getValues() as $value) {
			$this->writeDatabaseValueSingle($defaultsId, $value);
		}

		return $defaultsId;
	}


	/**
	 *
	 * @param DefaultValueModelBase $pDataModel
	 * @return int
	 *
	 * @throws RecordManagerInsertException
	 *
	 */

	private function createBase(DefaultValueModelBase $pDataModel): int
	{
		$field = $pDataModel->getField()->getName();
		$defaultsId = $this->writeDatabaseGeneral($pDataModel->getFormId(), $field);
		$pDataModel->setDefaultsId($defaultsId);
		return $defaultsId;
	}


	/**
	 *
	 * Step one: write oo_fieldconfig_form_defaults
	 *
	 * @param int $formId
	 * @param string $field
	 * @return int
	 * @throws RecordManagerInsertException
	 *
	 */

	private function writeDatabaseGeneral(int $formId, string $field): int
	{
		$pRecordManager = $this->createRecordManagerDefaults();
		$values = [
			'form_id' => $formId,
			'fieldname' => $field,
		];
		return $pRecordManager->insertByRow([self::TABLE_DEFAULTS => $values]);
	}


	/**
	 *
	 * step two: write to oo_fieldconfig_form_defaults_values
	 *
	 * @param int $defaultsId
	 * @param string $value
	 * @param string $locale
	 *
	 * @throws RecordManagerInsertException
	 *
	 */

	private function writeDatabaseValueSingle(int $defaultsId, string $value, string $locale = '')
	{
		$pRecordManager = $this->createRecordManagerDefaultsValues();
		$values = [
			'defaults_id' => $defaultsId,
			'locale' => $locale,
			'value' => $value,
		];
		$pRecordManager->insertByRow([self::TABLE_DEFAULTS_VALUES => $values]);
	}


	/**
	 *
	 * @return RecordManagerInsertGeneric
	 *
	 */

	private function createRecordManagerDefaults(): RecordManagerInsertGeneric
	{
		return $this->_pRecordManagerFactory->createRecordManagerInsertGeneric(self::TABLE_DEFAULTS);
	}


	/**
	 *
	 * @return RecordManagerInsertGeneric
	 *
	 */

	private function createRecordManagerDefaultsValues(): RecordManagerInsertGeneric
	{
		return $this->_pRecordManagerFactory->createRecordManagerInsertGeneric(self::TABLE_DEFAULTS_VALUES);
	}
}
