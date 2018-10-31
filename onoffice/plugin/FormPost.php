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
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Form\CaptchaHandler;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Form\FormPostConfigurationDefault;
use onOffice\WPlugin\FormData;
use onOffice\WPlugin\Types\FieldTypes;

/**
 *
 *
 * Terminology used in this class:
 *
 * - prefix: the prefix of the input form. Must be the name of a form
 * - form No.: Every input's name consists of the prefix + form no to make multiple forms on
 *				one page possible.
 *				The Form No must be incremented at every new form output.
 *
 *
 */

abstract class FormPost
{
	/** */
	const MESSAGE_SUCCESS = 'success';

	/** */
	const MESSAGE_REQUIRED_FIELDS_MISSING = 'fieldmissing';

	/** */
	const MESSAGE_ERROR = 'error';

	/** */
	const MESSAGE_RECAPTCHA_SPAM = 'recaptchaSpam';

	/** */
	const RANGE_VON = '__von';

	/** */
	const RANGE_BIS = '__bis';


	/** @var int */
	private static $_formNo = 0;

	/** @var array */
	private $_formDataInstances = [];

	/** @var FormPostConfiguration */
	private $_pFormPostConfiguration = null;


	/**
	 *
	 * @param FormPostConfiguration $pFormPostConfiguration
	 *
	 */

	public function __construct(FormPostConfiguration $pFormPostConfiguration = null)
	{
		$this->_pFormPostConfiguration = $pFormPostConfiguration ?? new FormPostConfigurationDefault;
	}


	/**
	 *
	 * @param DataFormConfiguration $pConfig
	 * @param int $formNo
	 *
	 */

	public function initialCheck(DataFormConfiguration $pConfig, $formNo = null)
	{
		$pFormData = $this->buildFormData($pConfig, $formNo);
		$pFormData->setFormSent(true);
		$this->setFormDataInstances($pFormData);

		if ($pFormData->getMissingFields() === []) {
			if (!$this->checkCaptcha($pConfig)) {
				$pFormData->setStatus(self::MESSAGE_RECAPTCHA_SPAM);
				return;
			}
		}

		$this->analyseFormContentByPrefix($pFormData);
	}


	/**
	 *
	 * @param DataFormConfiguration $pConfig
	 * @return bool
	 *
	 */

	private function checkCaptcha(DataFormConfiguration $pConfig): bool
	{
		if ($pConfig->getCaptcha() && $this->_pFormPostConfiguration->isCaptchaSetup()) {
			$token = $this->_pFormPostConfiguration->getPostvarCaptchaToken();
			$secret = $this->_pFormPostConfiguration->getCaptchaSecret();
			$pCaptchaHandler = new CaptchaHandler($token, $secret);
			return $pCaptchaHandler->checkCaptcha();
		} else {
			return true;
		}
	}


	/**
	 *
	 * @param DataFormConfiguration $pFormConfig
	 * @param int $formNo
	 * @return FormData
	 *
	 */

	private function buildFormData(DataFormConfiguration $pFormConfig, $formNo): FormData
	{
		$formFields = $this->getAllowedPostVars($pFormConfig);
		$postVariables = $this->_pFormPostConfiguration->getPostVars();
		$formData = array_intersect_key($postVariables, $formFields);
		$pFormData = new FormData($pFormConfig, $formNo);
		$pFormData->setRequiredFields($pFormConfig->getRequiredFields());
		$pFormData->setFormtype($pFormConfig->getFormType());
		$pFormData->setValues($formData);

		return $pFormData;
	}


	/**
	 *
	 * @param DataFormConfiguration $pFormConfig
	 * @return string[]
	 *
	 */

	protected function getAllowedPostVars(DataFormConfiguration $pFormConfig): array
	{
		return $pFormConfig->getInputs();
	}


	/**
	 *
	 * @param FormData $pFormData
	 *
	 */

	abstract protected function analyseFormContentByPrefix(FormData $pFormData);


	/**
	 *
	 * @param string $prefix
	 * @param int $formNo
	 * @return FormData
	 *
	 */

	public function getFormDataInstance(string $prefix, $formNo)
	{
		if (isset($this->_formDataInstances[$prefix][$formNo])) {
			return $this->_formDataInstances[$prefix][$formNo];
		}

		return null;
	}


	/**
	 *
	 * @param FormData $pFormData
	 *
	 */

	public function setFormDataInstances(FormData $pFormData)
	{
		$formNo = $pFormData->getFormNo();
		$prefix = $pFormData->getDataFormConfiguration()->getFormName();
		$this->_formDataInstances[$prefix][$formNo] = $pFormData;
	}


	/**
	 *
	 */

	public static function incrementFormNo()
	{
		self::$_formNo++;
	}


	/**
	 *
	 * @return int
	 *
	 */

	public function getFormNo()
	{
		return self::$_formNo;
	}


	/**
	 *
	 * @param array $inputFormFields
	 * @param bool $numberAsRange
	 * @return array
	 *
	 */

	protected function getFormFieldsConsiderSearchcriteria($inputFormFields, $numberAsRange = true)
	{
		$pSDKWrapper = $this->_pFormPostConfiguration->getSDKWrapper();
		$pFieldnames = new Fieldnames();
		$pFieldnames->setSDKWrapper($pSDKWrapper);
		$pFieldnames->loadLanguage();
		$fieldList = $pFieldnames->getFieldList(onOfficeSDK::MODULE_SEARCHCRITERIA);

		$fields = array_unique(array_keys($fieldList));
		$module = array_fill(0, count($fields), 'searchcriteria');
		$inputFormFields += array_combine($fields, $module);

		if ($numberAsRange) {
			foreach ($fieldList as $name => $properties) {
				if (FieldTypes::isRangeType($properties['type'])) {
					unset($inputFormFields[$name]);
					$inputFormFields[$name.self::RANGE_VON] = 'searchcriteria';
					$inputFormFields[$name.self::RANGE_BIS] = 'searchcriteria';
				}
			}
		}

		return $inputFormFields;
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @param bool $mergeExisting
	 * @return bool
	 *
	 */

	protected function createOrCompleteAddress(FormData $pFormData, $mergeExisting = false)
	{
		$requestParams = $this->getAddressDataForApiCall($pFormData);
		$requestParams['checkDuplicate'] = $mergeExisting;
		$pSDKWrapper = $this->_pFormPostConfiguration->getSDKWrapper();

		$pApiClientAction = new APIClientActionGeneric($pSDKWrapper,
			onOfficeSDK::ACTION_ID_CREATE, 'address');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue();
		$pSDKWrapper->sendRequests();

		if ($pApiClientAction->getResultStatus() === true) {
			$result = $pApiClientAction->getResultRecords();
			return $result[0]['id'] ?? false;
		}

		return false;
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @return array
	 *
	 */

	private function getAddressDataForApiCall(FormData $pFormData)
	{
		$inputs = $pFormData->getDataFormConfiguration()->getInputs();
		$addressData = [];
		$values = $pFormData->getValues();

		foreach ($values as $input => $value) {
			$inputName = $pFormData->getFieldNameOfInput($input);
			if (onOfficeSDK::MODULE_ADDRESS !== $inputs[$inputName]) {
				continue;
			}

			$fieldType = $this->_pFormPostConfiguration->getTypeForInput($input, $inputs[$inputName]);
			$fieldNameAliases = [
				'Telefon1' => 'phone',
				'Email' => 'email',
				'Telefax1' => 'fax',
			];

			$fieldNameAliased = $fieldNameAliases[$inputName] ?? $inputName;
			$addressData[$fieldNameAliased] = $value;

			if ($fieldType === FieldTypes::FIELD_TYPE_MULTISELECT && !is_array($value)) {
				$addressData[$fieldNameAliased] = [$value];
			}
		}

		return $addressData;
	}
}
