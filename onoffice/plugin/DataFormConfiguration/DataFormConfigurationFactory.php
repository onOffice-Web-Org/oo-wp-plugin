<?php

/**
 *
 *    Copyright (C) 2017 onOffice GmbH
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

namespace onOffice\WPlugin\DataFormConfiguration;

use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationContact;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationApplicantSearch;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataFormConfigurationFactory
{
	/** @var array */
	private $_formClassMapping = array
		(
			Form::TYPE_CONTACT => 'DataFormConfigurationContact',
			Form::TYPE_FREE => 'DataFormConfiguration',
			Form::TYPE_OWNER => 'DataFormConfigurationContact',
			Form::TYPE_INTEREST => 'DataFormConfigurationContact',
			Form::TYPE_APPLICANT_SEARCH => 'DataFormConfigurationApplicantSearch',
		);


	/**
	 *
	 * @param string $formType
	 * @return DataFormConfiguration
	 * @throws UnknownFormException
	 *
	 */

	public function createEmptyByType($formType)
	{
		if (!array_key_exists($formType, $this->_formClassMapping))
		{
			throw new UnknownFormException;
		}

		/* @var $pConfig DataFormConfiguration */
		$pConfig = new $this->_formClassMapping[$formType];
		$pConfig->setFormType($formType);

		return $pConfig;
	}


	/**
	 *
	 * @param int $formId
	 * @return DataFormConfiguration;
	 *
	 */

	public function loadByFormId($formId)
	{
		$pRecordManagerRead = new RecordManagerReadForm();

		$rowMain = $pRecordManagerRead->getRowById($formId);
		$pConfig = $this->createByRow($rowMain);
		$rowFields = $pRecordManagerRead->readFieldsByFormId($formId);
		$this->addModulesByFields($rowFields, $pConfig);

		return $pConfig;
	}


	/**
	 *
	 * @param array $row
	 * @return DataFormConfiguration\DataFormConfiguration
	 *
	 */

	private function createByRow(array $row)
	{
		$type = $row['form_type'];
		$pConfig = $this->createEmptyByType($type);
		$this->configureGeneral($row, $pConfig);

		switch ($type)
		{
			case Form::TYPE_CONTACT:
			case Form::TYPE_OWNER:
			case Form::TYPE_INTEREST:
				$this->configureContact($row, $pConfig);
				break;
			case Form::TYPE_APPLICANT_SEARCH:
				$this->configureApplicant($row, $pConfig);
				break;
		}

		return $pConfig;
	}


	/**
	 *
	 * @param array $row
	 * @param DataFormConfiguration $pConfig
	 *
	 */

	private function configureContact(array $row, DataFormConfigurationContact $pConfig)
	{
		$pConfig->setRecipient($row['recipient']);
		$pConfig->setSubject($row['subject']);
	}


	/**
	 *
	 * @param array $row
	 * @param DataFormConfiguration $pConfig
	 *
	 */

	private function configureGeneral(array $row, DataFormConfiguration $pConfig)
	{
		$pConfig->setLanguage($row['language']);
	}


	/**
	 *
	 * @param array $row
	 * @param DataFormConfigurationApplicantSearch $pConfig
	 *
	 */

	private function configureApplicant(array $row, DataFormConfigurationApplicantSearch $pConfig)
	{
		$pConfig->setLimitResults($row['limitResults']);
	}


	/**
	 *
	 * @param array $rows rows from fieldconfig table
	 * @param DataFormConfiguration $pConfig
	 *
	 */

	private function addModulesByFields(array $rows, DataFormConfiguration $pConfig)
	{
		foreach ($rows as $row)
		{
			$pConfig->addInput($row['fieldname'], $row['module']);

			if ($row['required'] == 1)
			{
				$pConfig->addRequiredField($row['fieldname']);
			}
		}
	}
}
