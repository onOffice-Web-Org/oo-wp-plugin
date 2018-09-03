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

use onOffice\WPlugin\DataFormConfiguration;
use onOffice\WPlugin\Form;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class DataFormConfigurationFactory
{
	/** @var string */
	private $_type = null;

	/** @var DataFormConfigurationFactoryDependencyConfigBase */
	private $_pDependencyConfig = null;


	/** @var array */
	private $_formClassMapping = [
		Form::TYPE_CONTACT => DataFormConfigurationContact::class,
		Form::TYPE_OWNER => DataFormConfigurationOwner::class,
		Form::TYPE_INTEREST => DataFormConfigurationInterest::class,
		Form::TYPE_APPLICANT_SEARCH => DataFormConfigurationApplicantSearch::class,
	];


	/**
	 *
	 * @param string $type Optional when loading by ID/name
	 * @param DataFormConfigurationFactoryDependencyBase $pDependencyConfig
	 *
	 */

	public function __construct(string $type = null,
		DataFormConfigurationFactoryDependencyConfigBase $pDependencyConfig = null)
	{
		$this->_type = $type;

		if ($pDependencyConfig === null) {
			$pDependencyConfig = new DataFormConfigurationFactoryDependencyConfigDefault();
		}

		$this->_pDependencyConfig = $pDependencyConfig;
	}


	/**
	 *
	 * @param bool $adminInterface
	 *
	 */

	public function setIsAdminInterface(bool $adminInterface)
	{
		$this->_pDependencyConfig->setAdminInterface($adminInterface);
	}


	/**
	 *
	 * @param bool $setDefaultFields
	 * @throws UnknownFormException
	 * @return DataFormConfiguration
	 *
	 */

	public function createEmpty(bool $setDefaultFields = true)
	{
		if (!isset($this->_formClassMapping[$this->_type])) {
			throw new UnknownFormException;
		}

		$class = $this->_formClassMapping[$this->_type];
		/* @var $pConfig DataFormConfiguration */
		$pConfig = new $class;
		$pConfig->setFormType($this->_type);

		if ($setDefaultFields) {
			$pConfig->setDefaultFields();
		}

		return $pConfig;
	}


	/**
	 *
	 * @param int $formId
	 * @return DataFormConfiguration
	 *
	 */

	public function loadByFormId(int $formId)
	{
		$rowMain = $this->_pDependencyConfig->getMainRowById($formId);
		$this->_type = $rowMain['form_type'];
		$pConfig = $this->createByRow($rowMain);
		$rowFields = $this->_pDependencyConfig->getFieldsByFormId($formId);

		foreach ($rowFields as $fieldRow) {
			$this->configureFieldsByRow($fieldRow, $pConfig);
		}

		return $pConfig;
	}


	/**
	 *
	 * @param string $name
	 * @return DataFormConfiguration
	 *
	 */

	public function loadByFormName(string $name)
	{
		$rowMain = $this->_pDependencyConfig->getMainRowByName($name);

		$this->_type = $rowMain['form_type'];
		$formId = $rowMain['form_id'];
		$pConfig = $this->createByRow($rowMain);
		$rowFields = $this->_pDependencyConfig->getFieldsByFormId($formId);

		foreach ($rowFields as $fieldRow) {
			$this->configureFieldsByRow($fieldRow, $pConfig);
		}

		return $pConfig;
	}


	/**
	 *
	 * @param array $row
	 * @return DataFormConfiguration
	 *
	 */

	private function createByRow(array $row)
	{
		$pConfig = $this->createEmpty(false);
		$this->configureGeneral($row, $pConfig);

		switch ($this->_type) {
			case Form::TYPE_CONTACT:
				$this->configureContact($row, $pConfig);
				break;
			case Form::TYPE_OWNER:
				$this->configureOwner($row, $pConfig);
				break;
			case Form::TYPE_INTEREST:
				$this->configureInterest($row, $pConfig);
				break;
			case Form::TYPE_APPLICANT_SEARCH:
				$this->configureApplicantSearch($row, $pConfig);
				break;
		}

		return $pConfig;
	}


	/**
	 *
	 * @param array $row
	 * @param DataFormConfiguration $pFormConfiguration
	 *
	 */

	private function configureFieldsByRow(array $row,
		DataFormConfiguration\DataFormConfiguration $pFormConfiguration)
	{
		$fieldName = $row['fieldname'];
		$module = $row['module'];
		$pFormConfiguration->addInput($fieldName, $module);

		if ($row['required'] == 1) {
			$pFormConfiguration->addRequiredField($fieldName);
		}
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
		$pConfig->setCreateAddress((bool)$row['createaddress']);
		$pConfig->setCheckDuplicateOnCreateAddress((bool)$row['checkduplicates']);
	}


	/**
	 *
	 * @param array $row
	 * @param DataFormConfiguration $pConfig
	 *
	 */

	private function configureGeneral(array $row,
			DataFormConfiguration\DataFormConfiguration $pConfig)
	{
		$pConfig->setFormName($row['name']);
		$pConfig->setTemplate($row['template']);

		if (array_key_exists('form_type', $row)) {
			$pConfig->setFormType($row['form_type']);
		}
	}


	/**
	 *
	 * @param array $row
	 * @param DataFormConfigurationApplicantSearch $pConfig
	 *
	 */

	private function configureApplicantSearch(array $row, DataFormConfigurationApplicantSearch $pConfig)
	{
		$pConfig->setLimitResults($row['limitresults']);
	}


	/**
	 *
	 * @param array $row
	 * @param DataFormConfigurationOwner $pConfig
	 *
	 */

	private function configureOwner(array $row, DataFormConfigurationOwner $pConfig)
	{
		$pConfig->setRecipient($row['recipient']);
		$pConfig->setSubject($row['subject']);
		$pConfig->setPages($row['pages']);
		$pConfig->setCheckDuplicateOnCreateAddress((bool)$row['checkduplicates']);
	}


	/**
	 *
	 * @param array $row
	 * @param DataFormConfigurationInterest $pConfig
	 *
	 */

	private function configureInterest(array $row, DataFormConfigurationInterest $pConfig)
	{
		$pConfig->setRecipient($row['recipient']);
		$pConfig->setSubject($row['subject']);
		$pConfig->setCheckDuplicateOnCreateAddress($row['checkduplicates']);
	}


	/**
	 *
	 * @param array $rows rows from fieldconfig table
	 * @param DataFormConfiguration $pConfig
	 *
	 */

	public function addModulesByFields(array $rows, DataFormConfiguration $pConfig)
	{
		if (!array_key_exists('fieldname', $rows)) {
			return;
		}

		foreach ($rows['fieldname'] as $fieldName) {
			$module = null;
			if (isset($rows['module'][$fieldName])) {
				$module = $rows['module'][$fieldName];
			}

			$pConfig->addInput($fieldName, $module);

			if (!isset($rows['required'])) {
				continue;
			}

			$required = in_array($fieldName, $rows['required'], true);

			if ($required) {
				$pConfig->addRequiredField($fieldName);
			}
		}
	}
}
