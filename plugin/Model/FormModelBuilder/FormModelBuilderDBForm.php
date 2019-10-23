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

namespace onOffice\WPlugin\Model\FormModelBuilder;

use DI\ContainerBuilder;
use Exception;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelLabel;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Translation\FormTranslation;
use onOffice\WPlugin\Translation\ModuleTranslation;
use onOffice\WPlugin\Types\Field;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use const ABSPATH;
use const ONOFFICE_DI_CONFIG_PATH;
use function __;
use function get_available_languages;
use function get_locale;
use function get_option;
use function wp_get_available_translations;

/**
 *
 */

class FormModelBuilderDBForm
	extends FormModelBuilderDB
{
	/** @var string */
	private $_formType = null;

	/** @var array */
	private $_formModules = [];


	/**
	 *
	 */

	public function __construct()
	{
		$pConfigForm = new InputModelDBFactoryConfigForm();
		$pInputModelDBFactory = new InputModelDBFactory($pConfigForm);
		$this->setInputModelDBFactory($pInputModelDBFactory);
	}


	/**
	 *
	 * @param string $module
	 * @param string $htmlType
	 * @return InputModelDB
	 *
	 */

	public function createSortableFieldList($module, $htmlType)
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$pInputModelFieldsConfig->setHtmlType($htmlType);
		$pFieldsCollection = $this->getFieldsCollection();
		$fieldNames = [];

		if (is_array($module)) {
			$this->_formModules = $module;
			foreach ($module as $submodule) {
				$newFields = $pFieldsCollection->getFieldsByModule($submodule ?? '');
				$fieldNames = array_merge($fieldNames, $newFields);
			}
		} else {
			$this->_formModules = [$module];
			$fieldNames = $pFieldsCollection->getFieldsByModule($module);
		}

		$fieldnamesArray = [];

		foreach ($fieldNames as $pField) {
			$fieldnamesArray[$pField->getName()] = $pField->getAsRow();
		}

		$pInputModelFieldsConfig->setValuesAvailable($fieldnamesArray);
		$fields = $this->getValue(DataFormConfiguration::FIELDS) ?? [];
		$pInputModelFieldsConfig->setValue($fields);

		$pModule = $this->getInputModelModule();
		$pReferenceIsRequired = $this->getInputModelIsRequired();
		$pReferenceIsAvailableOptions = $this->getInputModelIsAvailableOptions();
		$pInputModelFieldsConfig->addReferencedInputModel($pModule);
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelDefaultValue($fieldNames));
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelDefaultValueLanguageSwitch());
		$pInputModelFieldsConfig->addReferencedInputModel($pReferenceIsRequired);
		$pInputModelFieldsConfig->addReferencedInputModel($pReferenceIsAvailableOptions);

		return $pInputModelFieldsConfig;
	}


	/**
	 *
	 * @return FieldsCollection
	 *
	 */

	private function getFieldsCollection(): FieldsCollection
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();

		$pFieldsCollectionBuilder = $pContainer->get(FieldsCollectionBuilderShort::class);
		$pFieldsCollection = new FieldsCollection();

		$pFieldsCollectionBuilder
			->addFieldsAddressEstate($pFieldsCollection)
			->addFieldsSearchCriteria($pFieldsCollection)
			->addFieldsFormBackend($pFieldsCollection);
		return $pFieldsCollection;
	}


	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelDB
	 *
	 */

	public function createInputModelFieldsConfigByCategory($category, $fieldNames, $categoryLabel)
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, $category, true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX_BUTTON);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$pInputModelFieldsConfig->setLabel($categoryLabel);
		$fields = $this->getValue(DataFormConfiguration::FIELDS);

		if (null == $fields) {
			$fields = array();
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}


	/**
	 *
	 * @param string $pageSlug
	 * @param int $formId
	 * @return FormModel
	 * @throws Exception
	 *
	 */

	public function generate(string $pageSlug, $formId = null): FormModel
	{
		if ($this->_formType === null) {
			throw new Exception('formType must be set!');
		}

		$values = array();
		$values['fieldsRequired'] = array();
		$values['fieldsAvailableOptions'] = array();
		$pFactory = new DataFormConfigurationFactory($this->_formType);

		if ($formId !== null) {
			$pRecordReadManager = new RecordManagerReadForm();
			$pFactory->setIsAdminInterface(true);
			$values = $pRecordReadManager->getRowById($formId);
			$pDataFormConfiguration = $pFactory->loadByFormId($formId);
		} else {
			$pDataFormConfiguration = $pFactory->createEmpty();
		}

		$values[DataFormConfiguration::FIELDS] = array_keys($pDataFormConfiguration->getInputs());
		$values['fieldsRequired'] = $pDataFormConfiguration->getRequiredFields();
		$values['fieldsAvailableOptions'] = $pDataFormConfiguration->getAvailableOptionsFields();

		$this->setValues($values);
		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('Form', 'onoffice'));
		$pFormModel->setGroupSlug('onoffice-form-settings');
		$pFormModel->setPageSlug($pageSlug);

		return $pFormModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelName()
	{
		$labelName = __('Form Name', 'onoffice');

		$pInputModelName = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_NAME, null);
		$pInputModelName->setPlaceholder($labelName);
		$pInputModelName->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelName->setValue($this->getValue($pInputModelName->getField()));

		return $pInputModelName;
	}


	/**
	 *
	 * @return InputModelLabel
	 *
	 */

	public function createInputModelFormType()
	{
		$formType = $this->getFormType();
		$pFormTranslation = new FormTranslation();
		$translation = $pFormTranslation->getPluralTranslationForForm($formType, 1);
		$pInputModeLabel = new InputModelLabel(__('Type of Form: ', 'onoffice'), $translation);

		$pInputModeLabel->setHtmlType(InputModelBase::HTML_TYPE_LABEL);

		return $pInputModeLabel;
	}


	/**
	 *
	 * @return InputModelLabel
	 *
	 */

	public function createInputModelEmbedCode()
	{
		$pConfig = new InputModelDBFactoryConfigForm();
		$config = $pConfig->getConfig();
		$name = $config[InputModelDBFactoryConfigForm::INPUT_FORM_NAME]
			[InputModelDBFactoryConfigForm::KEY_FIELD];
		$formName = $this->getValue($name);

		$code = '[oo_form form="'.$formName.'"]';
		$pInputModeLabel = new InputModelLabel(__(', Embed Code: ', 'onoffice'), $code);
		$pInputModeLabel->setHtmlType(InputModelBase::HTML_TYPE_LABEL);
		$pInputModeLabel->setValueEnclosure(InputModelLabel::VALUE_ENCLOSURE_CODE);

		return $pInputModeLabel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelRecipient()
	{
		$labelRecipient = __('Recipient\'s E-Mail Address', 'onoffice');
		$selectedRecipient = $this->getValue('recipient');

		$pInputModelFormRecipient = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_RECIPIENT, $labelRecipient);
		$pInputModelFormRecipient->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelFormRecipient->setValue($selectedRecipient);
		$pInputModelFormRecipient->setPlaceholder(__('john.doe@example.com', 'onoffice'));

		return $pInputModelFormRecipient;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelCaptchaRequired(): InputModelDB
	{
		$addition = '';

		if (get_option('onoffice-settings-captcha-sitekey', '') === '') {
			$addition = __('(won\'t work until set up globally)', 'onoffice');
		}

		/* translators: %s will be replaced with the translation of
			'(won't work until set up globally)', if captcha hasn't been set up appropriately yet,
			or blank otherwise. */
		$labelRequiresCaptcha = sprintf(__('Requires Captcha %s', 'onoffice'), $addition);
		$selectedValue = $this->getValue('captcha', false);
		$pInputModelFormRequiresCaptcha = $this->generateGenericCheckbox($labelRequiresCaptcha,
			InputModelDBFactoryConfigForm::INPUT_FORM_REQUIRES_CAPTCHA, $selectedValue);

		return $pInputModelFormRequiresCaptcha;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelResultLimit()
	{
		$labelResultLimit = __('Result Limit', 'onoffice');
		$pInputModelFormLimitResult = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_LIMIT_RESULTS, $labelResultLimit);
		$field = $pInputModelFormLimitResult->getField();
		$selectedValue = $this->getValue($field);
		$pInputModelFormLimitResult->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelFormLimitResult->setValue($selectedValue);

		return $pInputModelFormLimitResult;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelIsRequired()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigForm();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Required', 'onoffice');
		$type = InputModelDBFactoryConfigForm::INPUT_FORM_REQUIRED;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsRequired'));

		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelIsAvailableOptions()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigForm();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Available Options', 'onoffice');
		$type = InputModelDBFactoryConfigForm::INPUT_FORM_AVAILABLE_OPTIONS;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsAvailableOptions'));

		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelDefaultValue(array $fieldnames): InputModelDB
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigForm();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Default Value', 'onoffice');
		$type = InputModelDBFactoryConfigForm::INPUT_FORM_DEFAULT_VALUE;

		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_TEXT);
		$pInputModel->setValueCallback(function(InputModelBase $pInputModel, string $key) use ($fieldnames) {
			$this->callbackValueInputModelDefaultValue($pInputModel, $key, $fieldnames);
		});
		return $pInputModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelDefaultValueLanguageSwitch(): InputModelDB
	{
		$pInputModel = new InputModelDB('defaultvalue_newlang', __('Add language', 'onoffice'));
		$pInputModel->setTable('language');
		$pInputModel->setField('language');
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$languagesKeys = array_merge(['', 'en_US'], get_available_languages());
		$languagesKeysRelevant = array_diff($languagesKeys, [get_locale()]);
		require_once(ABSPATH.'wp-admin/includes/translation-install.php');
		$translations = wp_get_available_translations() + ['en_US' => ['native_name' => 'English (United States)']];
		$languages = array_combine($languagesKeysRelevant, array_map(function(string $key) use ($translations): string {
			return $translations[$key]['native_name'] ?? '';
		}, $languagesKeysRelevant));
		natcasesort($languages);
		$pInputModel->setValuesAvailable(array_diff($languages, [get_locale()]));
		$pFieldsCollection = $this->getFieldsCollection();
		$pInputModel->setValueCallback(function(InputModelDB $pInputModel, string $key, string $module) use ($pFieldsCollection) {
			try {
				/** @var Field $pField */
				$pField = $pFieldsCollection->getFieldByModuleAndName($module, $key);
				$isTextType = $pField->getType() === FieldTypes::FIELD_TYPE_TEXT || $pField->getType() === FieldTypes::FIELD_TYPE_VARCHAR;
			} catch (UnknownFieldException $ex) {
				$isTextType = false;
			}

			if ($isTextType) {
				$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_HIDDEN);
				$pInputModel->setLabel('');
			}
		});

		return $pInputModel;
	}


	/**
	 *
	 * @param string $label
	 * @param string $type
	 * @param bool $checked
	 * @return InputModelDB
	 * @throws Exception
	 *
	 */

	private function generateGenericCheckbox(string $label, string $type, bool $checked):
		InputModelDB
	{
		$pInputModel = $this->getInputModelDBFactory()->create($type, $label);
		if ($pInputModel === null) {
			throw new Exception('Unknown input model type');
		}

		$pInputModel->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModel->setValue((int)$checked);
		$pInputModel->setValuesAvailable(1);
		return $pInputModel;
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 *
	 */

	public function callbackValueInputModelIsRequired(InputModelBase $pInputModel, $key)
	{
		$fieldsRequired = $this->getValue('fieldsRequired');
		$value = in_array($key, $fieldsRequired);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 *
	 */

	public function callbackValueInputModelIsAvailableOptions(InputModelBase $pInputModel, string $key)
	{
		$fieldsAvOpt = $this->getValue('fieldsAvailableOptions');
		$value = in_array($key, $fieldsAvOpt);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key
	 * @param array $fieldnames
	 *
	 */

	public function callbackValueInputModelDefaultValue(
		InputModelBase $pInputModel,
		string $key,
		array $fieldnames)
	{
		$fieldsDefaultValue = $this->getValue('defaultvalue')[$key] ?? [];
		$value = in_array($key, $fieldsDefaultValue);
		$pInputModel->setValue($value);

		$mapping = [
			FieldTypes::FIELD_TYPE_TEXT => InputModelOption::HTML_TYPE_TEXT,
			FieldTypes::FIELD_TYPE_VARCHAR=> InputModelOption::HTML_TYPE_TEXT,
			FieldTypes::FIELD_TYPE_DATE=> InputModelOption::HTML_TYPE_TEXT,
			FieldTypes::FIELD_TYPE_DATETIME=> InputModelOption::HTML_TYPE_TEXT,
			FieldTypes::FIELD_TYPE_FLOAT=> InputModelOption::HTML_TYPE_TEXT,
			FieldTypes::FIELD_TYPE_INTEGER=> InputModelOption::HTML_TYPE_TEXT,
			FieldTypes::FIELD_TYPE_MULTISELECT=> InputModelOption::HTML_TYPE_SELECT,
			FieldTypes::FIELD_TYPE_SINGLESELECT=> InputModelOption::HTML_TYPE_SELECT,
			FieldTypes::FIELD_TYPE_BOOLEAN=> InputModelOption::HTML_TYPE_CHECKBOX,
		];
		$pField = $fieldnames[$key] ?? null;
		$type = InputModelOption::HTML_TYPE_TEXT;
		$pInputModel->setValuesAvailable([]);
		/* @var $pField Field */
		if ($pField !== null) {
			$type = $mapping[$pField->getType()] ?? InputModelOption::HTML_TYPE_TEXT;
			$pInputModel->setValuesAvailable(['' => ''] + $pField->getPermittedvalues());
		}

		$pInputModel->setHtmlType($type);
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getInputModelModule()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigForm();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Module', 'onoffice');
		$type = InputModelDBFactoryConfigForm::INPUT_FORM_MODULE;
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_HIDDEN);
		$pInputModel->setValueCallback(function(InputModelBase $pInputModel, string $key) {
			$this->callbackValueInputModelModule($pInputModel, $key);
		});

		return $pInputModel;
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key
	 *
	 */

	private function callbackValueInputModelModule(InputModelBase $pInputModel, string $key)
	{
		$module = '';
		$pFieldsCollection = $this->getFieldsCollection();
		foreach ($this->_formModules as $formModule) {
			if ($pFieldsCollection->containsFieldByModule($formModule ?? '', $key)) {
				$module = $formModule;
				break;
			}
		}
		$moduleTranslated = __(ModuleTranslation::getLabelSingular($module ?? ''), 'onoffice');
		$label = sprintf(__('Module: %s', 'onoffice'), $moduleTranslated);
		$pInputModel->setLabel($label);
		$pInputModel->setValue($module);
	}


	/** @return string */
	public function getFormType()
		{ return $this->_formType; }

	/** @param string $formType */
	public function setFormType($formType)
		{ $this->_formType = $formType; }
}
