<?php

/**
 *
 *    Copyright (C) 2016-2019 onOffice GmbH
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

namespace onOffice\WPlugin;

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionConfiguratorForm;
use onOffice\WPlugin\Field\CompoundFieldsFilter;
use onOffice\WPlugin\Field\SearchcriteriaFields;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Form\CaptchaHandler;
use onOffice\WPlugin\Form\FormFieldValidator;
use onOffice\WPlugin\Form\FormPostConfiguration;
use onOffice\WPlugin\Types\FieldsCollection;
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

	/** @var int */
	private static $_formNo = 0;

	/** @var array */
	private $_formDataInstances = [];

	/** @var FormPostConfiguration */
	private $_pFormPostConfiguration = null;

	/** @var int */
	private $_absolutCountResults = 0;

	/** @var SearchcriteriaFields */
	private $_pSearchcriteriaFields = null;

	/** @var FieldsCollection */
	private $_pFieldsCollection = null;

	/** @var CompoundFieldsFilter */
	private $_pCompoundFields = null;

	/** @var FieldsCollectionConfiguratorForm */
	private $_pFieldsCollectionConfiguratorForm;

	/**
	 *
	 * @param FormPostConfiguration $pFormPostConfiguration
	 * @param SearchcriteriaFields $pSearchcriteriaFields
	 * @param FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm
	 */

	public function __construct(
		FormPostConfiguration $pFormPostConfiguration,
		SearchcriteriaFields $pSearchcriteriaFields,
		FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm)
	{
		$this->_pFormPostConfiguration = $pFormPostConfiguration;
		$this->_pSearchcriteriaFields = $pSearchcriteriaFields;
		$this->_pFieldsCollectionConfiguratorForm = $pFieldsCollectionConfiguratorForm;
	}

	/**
	 *
	 * @param DataFormConfiguration $pConfig
	 * @param int $formNo
	 * @return void
	 * @throws DependencyException
	 * @throws Field\UnknownFieldException
	 * @throws NotFoundException
	 */

	public function initialCheck(DataFormConfiguration $pConfig, int $formNo)
	{
		$this->updatePostHoneypot();
		$pFormData = $this->buildFormData($pConfig, $formNo);
		$pFormData->setFormSent(true);
		$this->setFormDataInstances($pFormData);

		if ($this->_pFormPostConfiguration->getPostMessage() !== "") {
			$pFormData->setStatus(self::MESSAGE_SUCCESS);
			return;
		} elseif ($pFormData->getMissingFields() === [] && !$this->checkCaptcha($pConfig)) {
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
	 * @return array
	 */
	protected function expandFieldsCollection()
	{
		return [];
	}

	/**
	 * @param DataFormConfiguration $pFormConfig
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	protected function setFieldsCollection(DataFormConfiguration $pFormConfig)
	{
		$pFieldsCollection = new FieldsCollection();
		$pFieldBuilderShort = $this->_pFormPostConfiguration->getFieldsCollectionBuilderShort();
		$pFieldBuilderShort
			->addFieldsAddressEstate($pFieldsCollection)
			->addFieldsSearchCriteria($pFieldsCollection)
			->addFieldsFormFrontend($pFieldsCollection);

		$this->_pFieldsCollection = $this->_pFieldsCollectionConfiguratorForm
			->buildForFormType($pFieldsCollection, $pFormConfig->getFormType());

		foreach ($this->expandFieldsCollection() as $pField) {
			if (!$this->_pFieldsCollection->containsFieldByModule($pField->getModule(), $pField->getName())) {
				$this->_pFieldsCollection->addField($pField);
			}
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
	 * @throws Field\UnknownFieldException
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	private function buildFormData(DataFormConfiguration $pFormConfig, $formNo): FormData
	{
		$pFormFieldValidator = new FormFieldValidator(new RequestVariablesSanitizer, $this->_pSearchcriteriaFields);

		$this->setFieldsCollection($pFormConfig);
		$this->_pCompoundFields = $this->_pFormPostConfiguration->getCompoundFields();
		$requiredFields = $this->_pCompoundFields->mergeFields($this->_pFieldsCollection, $pFormConfig->getRequiredFields());
		$inputs = $this->_pCompoundFields->mergeAssocFields($this->_pFieldsCollection, $pFormConfig->getInputs());
		$pFormConfig->setInputs($inputs);
		if (!empty($pFormConfig->getHiddenFields())) {
			$this->updateValueForHiddenFields($pFormConfig->getHiddenFields());
		}

		$formFields = $this->getAllowedPostVars($pFormConfig);
		$formData = $pFormFieldValidator->getValidatedValues($formFields, $this->_pFieldsCollection);
		$requiredFields = $this->getAllowedRequiredFields($requiredFields);
		$requiredFieldsAreAllowed = $this->getAllowedRequiredFieldsIsRangeField($requiredFields);
		$pFormData = new FormData($pFormConfig, $formNo);
		$pFormData->setRequiredFields($requiredFieldsAreAllowed);
		$pFormData->setFormtype($pFormConfig->getFormType());
		$pFormData->setValues($formData);

		return $pFormData;
	}

	/**
	 * @param array $hiddenFields
	 *
	 * @return void
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */

	private function updateValueForHiddenFields(array $hiddenFields)
	{
		$fields = [];
		foreach ($hiddenFields as $field) {
			$fields[] = $this->_pFieldsCollection->getFieldByKeyUnsafe($field);
		}

		foreach ($fields as $field) {
			if ($field->getType() === FieldTypes::FIELD_TYPE_MULTISELECT && isset($_POST[$field->getName()]) && !empty($_POST[$field->getName()])
				&& is_string($_POST[$field->getName()])) {
				$_POST[$field->getName()] = explode(', ', $_POST[$field->getName()]);
			}
		}
	}

	/**
	 * @return FieldsCollection
	 */
	protected function getFieldsCollection(): FieldsCollection
	{
		return $this->_pFieldsCollection;
	}

	/**
	 *
	 * @param string $name
	 * @return string
	 */

	private function getModule(string $name): string
	{
		try {
			return $this->_pFieldsCollection->getFieldByKeyUnsafe($name)->getModule();
		} catch (UnknownFieldException $pEx) {
			return '';
		}
	}
 
	/**
	 *
	 * @param $requiredFields
	 * @return string[]
	 *
	 */

	protected function getAllowedRequiredFields($requiredFields): array
	{
		$activeRequiredFields = [];

		foreach ($requiredFields as $name) {
			$module = $this->getModule($name);
			if ($this->_pFieldsCollection->containsFieldByModule($module, $name)) {
				$activeRequiredFields[] = $name;
			}
		}
		return $activeRequiredFields;
	}
 
	/**
	 *
	 * @param array $requiredFields
	 * @return string[]
	 *
	 */

	private function getAllowedRequiredFieldsIsRangeField(array $requiredFields): array
	{
		foreach ($requiredFields as $key => $value) {
			$isRangeField = $this->_pFieldsCollection->getFieldByKeyUnsafe($value)->getIsRangeField();
			if ($isRangeField) {
				array_push($requiredFields, $value.'__von');
				array_push($requiredFields, $value.'__bis');
				unset($requiredFields[$key]);
			}
		}

		return $requiredFields;
	}


	/**
	 *
	 * @param DataFormConfiguration $pFormConfig
	 * @return string[]
	 *
	 */

	protected function getAllowedPostVars(DataFormConfiguration $pFormConfig): array
	{
		$inputs = $pFormConfig->getInputs();
		$fields = $this->_pCompoundFields->mergeAssocFields($this->_pFieldsCollection, $inputs);
		$activeFields = [];

		foreach ($fields as $name => $module) {
			if ($this->_pFieldsCollection->containsFieldByModule($module, $name)) {
				$activeFields[$name] = $module;
			}
		}
		return $activeFields;
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
	 * Updates the honeypot field in the $_POST array.
	 */
	private function updatePostHoneypot()
	{
		if ( ! get_option( 'onoffice-settings-honeypot' ) ) {
			return;
		}
		$honeypotValue = $this->_pFormPostConfiguration->getPostHoneypot();
		if ( $honeypotValue !== '' ) {
			if ( ! empty( $this->_pFormPostConfiguration->getPostMessage() ) ) {
				$_POST['message'] = $_POST['tmpField'];
			} else {
				unset( $_POST['message'] );
			}
			$_POST['tmpField'] = $honeypotValue;
		} else {
			if ( ! empty( $this->_pFormPostConfiguration->getPostMessage() ) ) {
				$_POST['message'] = $_POST['tmpField'];
				unset( $_POST['tmpField'] );
			}
		}
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


	/** @param int $absolutCountResults */
	protected function setAbsolutCountResults(int $absolutCountResults)
		{ $this->_absolutCountResults = $absolutCountResults; }

	/** @return int */
	public function getAbsolutCountResults(): int
		{ return $this->_absolutCountResults; }
}
