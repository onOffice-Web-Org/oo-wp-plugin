<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationContact;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\FieldTypes;
use function __;
use function esc_html;

/**
 *
 */

class Form
{
	/** choose this to create a contact form */
	const TYPE_CONTACT = 'contact';

	/** choose this to create an owner form */
	const TYPE_OWNER = 'owner';

	/** choose this to create an aplicant form (with searchcriterias)*/
	const TYPE_INTEREST = 'interest';

	/** choose this to create an applicant-search form */
	const TYPE_APPLICANT_SEARCH = 'applicantsearch';

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/** @var int */
	private $_formNo = null;

	/** @var FormData */
	private $_pFormData = null;

	/** @var array */
	private $_genericSettings = [];


	/**
	 *
	 * @param string $formName
	 * @param string $type
	 *
	 */

	public function __construct(string $formName, string $type)
	{
		$this->setGenericSetting('submitButtonLabel', __('Submit', 'onoffice'));
		$this->setGenericSetting('formId', 'onoffice-form');
		$this->_pFieldnames = new Fieldnames(true);
		$this->_pFieldnames->loadLanguage();
		$pFormPost = FormPostHandler::getInstance($type);
		FormPost::incrementFormNo();
		$this->_formNo = $pFormPost->getFormNo();
		$this->_pFormData = $pFormPost->getFormDataInstance($formName, $this->_formNo);

		// no form sent
		if (is_null($this->_pFormData)) {
			$pFormConfigFactory = new DataFormConfigurationFactory();
			$pFormConfig = $pFormConfigFactory->loadByFormName($formName);
			$this->_pFormData = new FormData($pFormConfig, $this->_formNo);
			$this->_pFormData->setRequiredFields($pFormConfig->getRequiredFields());
			$this->_pFormData->setFormtype($pFormConfig->getFormType());
			$this->_pFormData->setFormSent(false);
		}
	}


	/**
	 *
	 * @param string $field
	 * @return string
	 *
	 */

	private function getModuleOfField(string $field)
	{
		$inputs = $this->getInputFields();
		$module = $inputs[$field] ?? null;

		return $module;
	}

	/**
	 *
	 * @return array
	 *
	 */

	public function getInputFields(): array
	{
		$inputs = $this->getDataFormConfiguration()->getInputs();
		$inputsAll = array_merge($inputs, $this->getFormSpecificFields());
		return $inputsAll;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function getFormSpecificFields(): array
	{
		$newFields = [];
		$pDataFormConfiguration = $this->getDataFormConfiguration();

		if ($pDataFormConfiguration->getFormType() === self::TYPE_CONTACT &&
			$pDataFormConfiguration instanceof DataFormConfigurationContact) {
			if ($pDataFormConfiguration->getNewsletterCheckbox()) {
				$newFields['newsletter'] = onOfficeSDK::MODULE_ADDRESS;
			}
		}

		return $newFields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getRequiredFields(): array
	{
		$requiredFields = $this->getDataFormConfiguration()->getRequiredFields();
		$requiredFieldsWithGeo = $this->executeGeoPositionFix($requiredFields);

		return $requiredFieldsWithGeo;
	}


	/**
	 *
	 * @param array $requiredFields
	 * @return array
	 *
	 */

	private function executeGeoPositionFix(array $requiredFields): array
	{
		if (in_array(GeoPosition::FIELD_GEO_POSITION, $requiredFields))	{
			$pGeoPosition = new GeoPosition();
			$geoPositionFields = $pGeoPosition->getSettingsGeoPositionFields
				(onOfficeSDK::MODULE_SEARCHCRITERIA);
			unset($requiredFields[GeoPosition::FIELD_GEO_POSITION]);
			$requiredFields = array_merge($requiredFields, $geoPositionFields);
		}

		return $requiredFields;
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function isRequiredField(string $field): bool
	{
		$requiredFields = $this->getRequiredFields();
		$pGeoPosition = new GeoPosition();

		if (in_array($field, $pGeoPosition->getSettingsGeoPositionFields
				(onOfficeSDK::MODULE_SEARCHCRITERIA)) &&
					in_array(GeoPosition::FIELD_GEO_POSITION, $requiredFields)) {
			return true;
		}

		return in_array($field, $requiredFields);
	}


	/**
	 *
	 * @return DataFormConfiguration
	 *
	 */

	private function getDataFormConfiguration()
	{
		return $this->_pFormData->getDataFormConfiguration();
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getFormStatus()
	{
		return $this->_pFormData->getStatus();
	}


	/**
	 *
	 * @param string $field
	 * @param bool $raw
	 *
	 * @return string
	 *
	 */

	public function getFieldLabel(string $field, bool $raw = false): string
	{
		$module = $this->getModuleOfField($field);
		$label = $this->_pFieldnames->getFieldLabel($field, $module);

		if (false === $raw) {
			$label = esc_html($label);
		}

		return $label;
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function isSearchcriteriaField(string $field): bool
	{
		$module = $this->getModuleOfField($field);
		return $module === onOfficeSDK::MODULE_SEARCHCRITERIA;
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function inRangeSearchcriteriaInfos(string $field): bool
	{
		$module = $this->getModuleOfField($field);

		return $module === onOfficeSDK::MODULE_SEARCHCRITERIA &&
			$this->_pFieldnames->inRangeSearchcriteriaInfos($field);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getSearchcriteriaRangeInfos(): array
	{
		return $this->_pFieldnames->getSearchcriteriaRangeInfos();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getUmkreisFields(): array
	{
		return $this->_pFieldnames->getUmkreisFields();
	}


	/**
	 *
	 * @param string $field
	 * @return array
	 *
	 */

	public function getSearchcriteriaRangeInfosForField(string $field): array
	{
		$returnValues = [];
		$module = $this->getModuleOfField($field);

		if ($module === onOfficeSDK::MODULE_SEARCHCRITERIA &&
			$this->_pFieldnames->inRangeSearchcriteriaInfos($field)) {
			$returnValues = $this->_pFieldnames->getRangeSearchcriteriaInfosForField($field);
		}

		return $returnValues;
	}


	/**
	 *
	 * @param string $field
	 * @param bool $raw
	 *
	 * @return array
	 *
	 */

	public function getPermittedValues($field, $raw = false): array
	{
		$module = $this->getModuleOfField($field);
		$fieldType = $this->getFieldType($field);
		$isMultiselectOrSingleselect = in_array($fieldType, [
			FieldTypes::FIELD_TYPE_MULTISELECT,
			FieldTypes::FIELD_TYPE_SINGLESELECT,
		], true);

		$result = [];

		if ($isMultiselectOrSingleselect) {
			$result = $this->_pFieldnames->getPermittedValues($field, $module);

			if (!$raw) {
				$result = $this->escapePermittedValues($result);
			}
		}

		return $result;
	}


	/**
	 *
	 * @param string $field
	 * @return string
	 *
	 */

	public function getFieldType($field)
	{
		$module = $this->getModuleOfField($field);
		$fieldType = $this->_pFieldnames->getType($field, $module);
		return $fieldType;
	}


	/**
	 *
	 * @param array $keyValues
	 * @return array
	 *
	 */

	private function escapePermittedValues(array $keyValues)
	{
		$result = array();

		foreach ($keyValues as $key => $value) {
			$result[esc_html($key)] = esc_html($value);
		}

		return $result;
	}


	/**
	 *
	 * @param string $field
	 * @param bool $raw
	 * @param bool $forceEvenIfSuccess
	 * @return string
	 *
	 */

	public function getFieldValue($field, $raw = false, $forceEvenIfSuccess = false)
	{
		$values = $this->_pFormData->getValues();
		$fieldValue = $values[$field] ?? '';

		if (!$this->_pFormData->getFormSent() && !$forceEvenIfSuccess) {
			return '';
		}

		if ($raw) {
			return $fieldValue;
		} else {
			return esc_html($fieldValue);
		}
	}


	/**
	 *
	 * @param string $field
	 * @param string $message
	 * @return string
	 *
	 */

	public function getMessageForField($field, $message)
	{
		if (in_array($field, $this->_pFormData->getMissingFields(), true)) {
			return esc_html($message);
		}
		return null;
	}


	/**
	 *
	 * @param string $field
	 * @return bool
	 *
	 */

	public function isMissingField($field)
	{
		return $this->_pFormData->getFormSent() &&
			in_array($field, $this->_pFormData->getMissingFields(), true);
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getFormNo()
	{
		return esc_html($this->_formNo);
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getFormId()
	{
		return esc_html($this->getDataFormConfiguration()->getFormName());
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function needsReCaptcha(): bool
	{
		return $this->getDataFormConfiguration()->getCaptcha();
	}


	/**
	 *
	 * @param string $settingName
	 * @return string
	 *
	 */

	public function getGenericSetting(string $settingName)
	{
		return $this->_genericSettings[$settingName] ?? null;
	}


	/**
	 *
	 * @param string $settingName
	 * @param string $value
	 *
	 */

	public function setGenericSetting(string $settingName, $value)
	{
		$this->_genericSettings[$settingName] = $value;
	}


	/**
	 *
	 * @return array
	 *
	 */

	static public function getFormTypesLabeled()
	{
		$formTypes = array(
			self::TYPE_CONTACT => 'Contact Form',
			self::TYPE_APPLICANT_SEARCH => 'Applicant Search',
			self::TYPE_INTEREST => 'Applicant Form with Search Criteria',
			self::TYPE_OWNER => 'Owner\'s Form',
		);

		return $formTypes;
	}

	/** @return array */
	public function getResponseFieldsValues()
		{ return $this->_pFormData->getResponseFieldsValues(); }
}