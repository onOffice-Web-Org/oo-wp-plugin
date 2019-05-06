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

use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
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

	/** @var int */
	private $_absolutCountResults = 0;


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
	 * @return void
	 *
	 */

	public function initialCheck(DataFormConfiguration $pConfig, $formNo = null)
	{
		$pFormData = $this->buildFormData($pConfig, $formNo);
		$pFormData->setFormSent(true);
		$this->setFormDataInstances($pFormData);

		if ($pFormData->getMissingFields() === [] && !$this->checkCaptcha($pConfig)) {
			$pFormData->setStatus(self::MESSAGE_RECAPTCHA_SPAM);
			return;
		} elseif ($pFormData->getMissingFields() !== []) {
			$pFormData->setStatus(self::MESSAGE_REQUIRED_FIELDS_MISSING);
			return;
		}

		try {
			$this->analyseFormContentByPrefix($pFormData);
			$pFormData->setStatus(self::MESSAGE_SUCCESS);
		} catch (Exception $pException) {
			$pFormData->setStatus(self::MESSAGE_ERROR);
			$this->_pFormPostConfiguration->getLogger()->logError($pException);
		}
	}


	/**
	 *
	 * @param DataFormConfiguration $pConfig
	 * @return bool
	 *
	 */

	private function checkCaptcha(DataFormConfiguration $pConfig): bool
	{
		$pWPOptionsWrapper = $this->_pFormPostConfiguration->getWPOptionsWrapper();
		$isCaptchaSetup = $pWPOptionsWrapper->getOption('onoffice-settings-captcha-sitekey', '') !== '';

		if ($pConfig->getCaptcha() && $isCaptchaSetup) {
			$token = $this->_pFormPostConfiguration->getPostvarCaptchaToken();
			$secret = $pWPOptionsWrapper->getOption('onoffice-settings-captcha-secretkey', '');
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
	 * @throws UnknownFormException
	 *
	 */

	public function getFormDataInstance(string $prefix, $formNo): FormData
	{
		$pInstance = $this->_formDataInstances[$prefix][$formNo] ?? null;

		if ($pInstance !== null) {
			return $pInstance;
		}

		throw new UnknownFormException;
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

	protected function getFormFieldsConsiderSearchcriteria(
		array $inputFormFields, bool $numberAsRange = true): array
	{
		$fieldList = $this->_pFormPostConfiguration->getFieldnames()
			->getFieldList(onOfficeSDK::MODULE_SEARCHCRITERIA);

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
	 * @return int
	 * @throws ApiClientException
	 *
	 */

	protected function createOrCompleteAddress(
		FormData $pFormData, bool $mergeExisting = false): int
	{
		$requestParams = $this->getAddressDataForApiCall($pFormData);
		$requestParams['checkDuplicate'] = $mergeExisting;
		$pSDKWrapper = $this->_pFormPostConfiguration->getSDKWrapper();

		$pApiClientAction = new APIClientActionGeneric($pSDKWrapper,
			onOfficeSDK::ACTION_ID_CREATE, 'address');
		$pApiClientAction->setParameters($requestParams);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$result = $pApiClientAction->getResultRecords();
		$addressId = (int)$result[0]['id'];

		if ($addressId > 0) {
			return $addressId;
		}

		throw new ApiClientException($pApiClientAction);
	}


	/**
	 *
	 * @param FormData $pFormData
	 * @return array
	 *
	 */

	private function getAddressDataForApiCall(FormData $pFormData): array
	{
		$fieldNameAliases = [
			'Telefon1' => 'phone',
			'Email' => 'email',
			'Telefax1' => 'fax',
		];

		$addressData = [];
		$addressFields = $pFormData->getAddressData();

		foreach ($addressFields as $input => $value) {
			$inputName = $pFormData->getFieldNameOfInput($input);
			$fieldType = $this->_pFormPostConfiguration->getFieldnames()->getType
				($input, onOfficeSDK::MODULE_ADDRESS);

			$fieldNameAliased = $fieldNameAliases[$inputName] ?? $inputName;
			$addressData[$fieldNameAliased] = $value;

			if ($fieldType === FieldTypes::FIELD_TYPE_MULTISELECT && !is_array($value)) {
				$addressData[$fieldNameAliased] = [$value];
			}
		}

		return $addressData;
	}


	/** @param int $absolutCountResults */
	protected function setAbsolutCountResults(int $absolutCountResults)
		{ $this->_absolutCountResults = $absolutCountResults; }


	/** @return int */
	public function getAbsolutCountResults(): int
		{ return $this->_absolutCountResults; }
}
