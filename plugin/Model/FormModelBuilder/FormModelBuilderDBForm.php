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

use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientCredentialsException;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\Field\Collection\FieldLoaderGeneric;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilder;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderCustomLabel;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderDefaultValue;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelLabel;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Translation\FormTranslation;
use onOffice\WPlugin\Translation\ModuleTranslation;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\WP\InstalledLanguageReader;
use function __;
use function get_locale;
use function get_option;
use const ONOFFICE_DI_CONFIG_PATH;
use onOffice\WPlugin\Field\Collection\FieldsCollectionConfiguratorForm;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\Field\Collection\FieldLoaderSupervisorValues;

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

	/** @var Container */
	private $_pContainer;

	/** @var array */
	private $_actionKind = [];

	/** @var array */
	private $_actionType = [];

	/** @var array */
	private $_characteristic = [];

	/** @var array */
	private $_originContact = [];

	/** @var array */
	private $_taskType = [];

	/**
	 * @param Container $pContainer
	 */
	public function __construct(Container $pContainer)
	{
		$pConfigForm = new InputModelDBFactoryConfigForm();
		$pInputModelDBFactory = new InputModelDBFactory($pConfigForm);
		$this->_pContainer = $pContainer;
		$this->setInputModelDBFactory($pInputModelDBFactory);
	}

	/**
	 * @param string|array $module
	 * @param string $htmlType
	 * @return InputModelDB
	 * @throws DependencyException
	 * @throws NotFoundException
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

		$fieldNamesArray = [];
		$pFieldsCollectionUsedFields = new FieldsCollection;

		foreach ($fieldNames as $pField) {
			$fieldNamesArray[$pField->getName()] = $pField->getAsRow();
			$fieldNamesArray[$pField->getName()]['page'] = $this->getValue('fieldsPagePerForm')[$pField->getName()] ?? 1;
			$pFieldsCollectionUsedFields->addField($pField);
		}

		$pInputModelFieldsConfig->setValuesAvailable($fieldNamesArray);
		$fields = $this->getValue(DataFormConfiguration::FIELDS) ?? [];
		$pInputModelFieldsConfig->setValue($fields);
		$pInputModelFieldsConfig->setPerPageForm($this->getValue('fieldsPagePerForm'));

		$pModule = $this->getInputModelModule();
		$pReferenceIsRequired = $this->getInputModelIsRequired();
		$pReferenceIsAvailableOptions = $this->getInputModelIsAvailableOptions();
		$pReferenceIsMarkdown = $this->getInputModelIsMarkDown();
		$pReferenceIsHiddenField = $this->getInputModelIsHiddenField();
		$pInputModelPerPageForm = $this->getInputModelPerPageForm();
		$pInputModelFieldsConfig->addReferencedInputModel($pModule);
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelDefaultValue($pFieldsCollectionUsedFields));
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelDefaultValueLanguageSwitch());
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelCustomLabel($pFieldsCollectionUsedFields));
		$pInputModelFieldsConfig->addReferencedInputModel($pReferenceIsMarkdown);
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelCustomLabelLanguageSwitch());
		$pInputModelFieldsConfig->addReferencedInputModel($pReferenceIsRequired);
		$pInputModelFieldsConfig->addReferencedInputModel($pInputModelPerPageForm);
		if($this->getFormType() === Form::TYPE_APPLICANT_SEARCH){
			$pInputModelFieldsConfig->addReferencedInputModel($pReferenceIsAvailableOptions);
		} else {
			$pInputModelFieldsConfig->addReferencedInputModel($pReferenceIsHiddenField);
		}
		if($this->getFormType() === Form::TYPE_OWNER){
			$pInputModelFieldsConfig->setIsMultiPage(true);
			$pInputModelFieldsConfig->setTemplate($this->getValue('template'));
		}

		return $pInputModelFieldsConfig;
	}

	/**
	 * @return FieldsCollection
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getFieldsCollection(): FieldsCollection
	{
		$pFieldsCollectionBuilder = $this->_pContainer->get(FieldsCollectionBuilderShort::class);
		$pFieldsCollection = new FieldsCollection();

		$pFieldsCollectionBuilder
			->addFieldsAddressEstate($pFieldsCollection)
			->addFieldsSearchCriteria($pFieldsCollection)
			->addFieldsFormBackend($pFieldsCollection,$this->getFormType());

		if ($this->getFormType() === Form::TYPE_INTEREST || $this->getFormType() === Form::TYPE_APPLICANT_SEARCH) {
			$pFieldsCollectionBuilder->addFieldSupervisorForSearchCriteria($pFieldsCollection);
		}

		$pFieldsCollectionConfiguratorForm = $this->_pContainer->get(FieldsCollectionConfiguratorForm::class);

		return $pFieldsCollectionConfiguratorForm->buildForFormType($pFieldsCollection, $this->getFormType());
	}

	/**
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelDB
	 */
	public function createInputModelFieldsConfigByCategory($category, $fieldNames, $categoryLabel)
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, $category, true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX_BUTTON);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$pInputModelFieldsConfig->setLabel($categoryLabel);
		$fields = $this->getValue(DataFormConfiguration::FIELDS, []);
		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 * @param string $pageSlug
	 * @param int $formId
	 * @return FormModel
	 * @throws Exception
	 */
	public function generate(string $pageSlug, $formId = null): FormModel
	{
		if ($this->_formType === null) {
			throw new Exception('formType must be set!');
		}

		$values = array();
		$values['fieldsRequired'] = array();
		$values['fieldsAvailableOptions'] = array();
		$values['fieldsMarkdown'] = array();
		$values['fieldsHiddenField'] = array();
		$values['fieldsPagePerForm'] = array();
		$values['template'] = array();
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
		$values['fieldsMarkdown'] = $pDataFormConfiguration->getMarkdownFields();
		$values['fieldsHiddenField'] = $pDataFormConfiguration->getHiddenFields();
		$values['writeactivity'] = $pDataFormConfiguration->getWriteActivity();
		$values['actionkind'] = $pDataFormConfiguration->getActionKind();
		$values['actiontype'] = $pDataFormConfiguration->getActionType();
		$values['characteristic'] = $pDataFormConfiguration->getCharacteristic();
		$values['remark'] = $pDataFormConfiguration->getRemark();
		$values['origincontact'] = $pDataFormConfiguration->getOriginContact();
		$values['advisorylevel'] = $pDataFormConfiguration->getAdvisorylevel();
		$values['fieldsPagePerForm'] = $pDataFormConfiguration->getPagePerForm();
		$values['template'] = $pDataFormConfiguration->getTemplate();
		$values['enable_create_task'] = $pDataFormConfiguration->getEnableCreateTask();
		$values['taskResponsibility'] = $pDataFormConfiguration->getTaskResponsibility();
		$values['taskProcessor'] = $pDataFormConfiguration->getTaskProcessor();
		$values['taskType'] = $pDataFormConfiguration->getTaskType();
		$values['taskPriority'] = $pDataFormConfiguration->getTaskPriority();
		$values['taskSubject'] = $pDataFormConfiguration->getTaskSubject();
		$values['taskDescription'] = $pDataFormConfiguration->getTaskDescription();
		$values['taskStatus'] = $pDataFormConfiguration->getTaskStatus();

		$this->setValues($values);
		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('Form', 'onoffice-for-wp-websites'));
		$pFormModel->setGroupSlug('onoffice-form-settings');
		$pFormModel->setPageSlug($pageSlug);

		$this->fetchDataTypesOfActionAndCharacteristics();

		return $pFormModel;
	}

	/**
	 * @return InputModelDB
	 */
	public function createInputModelName()
	{
		$labelName = __('Form Name', 'onoffice-for-wp-websites');

		$pInputModelName = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_NAME, null);
		$pInputModelName->setPlaceholder($labelName);
		$pInputModelName->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelName->setValue($this->getValue($pInputModelName->getField()));

		return $pInputModelName;
	}

	/**
	 * @return InputModelLabel
	 */
	public function createInputModelFormType()
	{
		$formType = $this->getFormType();
		$pFormTranslation = new FormTranslation();
		$translation = $pFormTranslation->getPluralTranslationForForm($formType, 1);
		$pInputModeLabel = new InputModelLabel(__('Type: ', 'onoffice-for-wp-websites'), $translation);

		$pInputModeLabel->setHtmlType(InputModelBase::HTML_TYPE_LABEL);

		return $pInputModeLabel;
	}

	/**
	 * @return InputModelLabel
	 */
	public function createInputModelEmbedCode()
	{
		$pConfig = new InputModelDBFactoryConfigForm();
		$config = $pConfig->getConfig();
		$name = $config[InputModelDBFactoryConfigForm::INPUT_FORM_NAME]
			[InputModelDBFactoryConfigForm::KEY_FIELD];
		$formName = $this->getValue($name);

		$code = '[oo_form form="'.$formName.'"]';
		$pInputModeLabel = new InputModelLabel(__(', shortcode: ', 'onoffice-for-wp-websites'), $code);
		$pInputModeLabel->setHtmlType(InputModelBase::HTML_TYPE_LABEL);
		$pInputModeLabel->setValueEnclosure(InputModelLabel::VALUE_ENCLOSURE_CODE);

		return $pInputModeLabel;
	}

	/**
	 * @return InputModelLabel
	 */
	public function createInputModelButton()
	{
		$pConfig  = new InputModelDBFactoryConfigForm();
		$config   = $pConfig->getConfig();
		$name     = $config[ InputModelDBFactoryConfigForm::INPUT_FORM_NAME ]
		[ InputModelDBFactoryConfigForm::KEY_FIELD ];
		$formName = $this->getValue( $name );

		$code            = '[oo_form form="' . $formName . '"]';
		$pInputModeLabel = new InputModelLabel( '', $code );
		$pInputModeLabel->setHtmlType( InputModelBase::HTML_TYPE_BUTTON );

		return $pInputModeLabel;
	}

	/**
	 * @return InputModelDB
	 * @throws Exception
	 */
	public function createInputModelDefaultRecipient(): InputModelDB
	{
		$addition = '';
		$isDefaultEmailMissing = false;
		$italicLabel = '';
		if (get_option('onoffice-settings-default-email', '') !== '') {
			$addition = '('.get_option('onoffice-settings-default-email', '').')';
		} else {
			$italicLabel = __('missing', 'onoffice-for-wp-websites');
			$isDefaultEmailMissing = true;
		}

		$selectedValue = $this->getValue('default_recipient', true);
		if (!$isDefaultEmailMissing) {
			$labelDefaultData = sprintf(__('Use default email address %s', 'onoffice-for-wp-websites'), $addition);
			$pInputModelFormDefaultData = $this->generateGenericCheckbox($labelDefaultData,
				InputModelDBFactoryConfigForm::INPUT_FORM_DEFAULT_RECIPIENT, $selectedValue);
		} else {
			$labelDefaultData = __('Use default email address ', 'onoffice-for-wp-websites');
			$pInputModelFormDefaultData = $this->generateItalicLabelCheckbox($labelDefaultData,
				InputModelDBFactoryConfigForm::INPUT_FORM_DEFAULT_RECIPIENT, $selectedValue, $italicLabel);
		}

		return $pInputModelFormDefaultData;
	}

	/**
	 * @return InputModelDB
	 */
	public function createInputModelRecipient()
	{
		$labelRecipient = __('Override email address', 'onoffice-for-wp-websites');
		$selectedRecipient = $this->getValue('recipient');

		$pInputModelFormRecipient = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_RECIPIENT, $labelRecipient);
		$pInputModelFormRecipient->setHtmlType(InputModelOption::HTML_TYPE_EMAIL);
		$pInputModelFormRecipient->setValue($selectedRecipient);
		$pInputModelFormRecipient->setDeactivate(true);

		return $pInputModelFormRecipient;
	}


	/**
	 * @return InputModelDB
	 */
	public function createInputModelRecipientContactForm()
	{
		$labelRecipient = __('Override email address', 'onoffice-for-wp-websites');
		$selectedRecipient = $this->getValue('recipient');

		$pInputModelFormRecipient = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_RECIPIENT, $labelRecipient);
		$pInputModelFormRecipient->setHtmlType(InputModelOption::HTML_TYPE_EMAIL);
		$pInputModelFormRecipient->setValue($selectedRecipient);
		$pInputModelFormRecipient->setDeactivate(true);
		$pInputModelFormRecipient->setHintHtml(__('Note that if the contact form is on an estate detail page and the estate has a contact person, the email will be sent to their email address. Otherwise this email address will receive the email.', 'onoffice-for-wp-websites'));

		return $pInputModelFormRecipient;
	}

	/**
	 * @return InputModelDB
	 * @throws Exception
	 */
	public function createInputModelCaptchaRequired(): InputModelDB
	{
		$addition = '';

		if (get_option('onoffice-settings-captcha-sitekey', '') === '') {
			$addition = __('(won\'t work until set up globally)', 'onoffice-for-wp-websites');
		}

		/* translators: %s will be replaced with the translation of
			'(won't work until set up globally)', if captcha hasn't been set up appropriately yet,
			or blank otherwise. */
		$labelRequiresCaptcha = sprintf(__('Requires Captcha %s', 'onoffice-for-wp-websites'), $addition);
		$selectedValue = $this->getValue('captcha', false);
		$pInputModelFormRequiresCaptcha = $this->generateGenericCheckbox($labelRequiresCaptcha,
			InputModelDBFactoryConfigForm::INPUT_FORM_REQUIRES_CAPTCHA, $selectedValue);

		return $pInputModelFormRequiresCaptcha;
	}

	/**
	 * @return InputModelDB
	 */
	public function createInputModelResultLimit()
	{
		$labelResultLimit = __('Result Limit', 'onoffice-for-wp-websites');
		$pInputModelFormLimitResult = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_LIMIT_RESULTS, $labelResultLimit);
		$field = $pInputModelFormLimitResult->getField();
		$selectedValue = $this->getValue($field);
		$pInputModelFormLimitResult->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelFormLimitResult->setValue($selectedValue);

		return $pInputModelFormLimitResult;
	}

    /**
     * @return InputModelDB
     */
    public function createInputModelContactType()
    {
        $labelContactType = __('Contact Type of New Address', 'onoffice-for-wp-websites');
        $pInputModelFormContactType = $this->getInputModelDBFactory()->create
        (InputModelDBFactoryConfigForm::INPUT_FORM_CONTACT_TYPE, $labelContactType);
        $field = $pInputModelFormContactType->getField();
        $pInputModelFormContactType->setHtmlType(InputModelOption::HTML_TYPE_SELECT_TWO);
        $availableContactType = $this->getDataContactType(onOfficeSDK::MODULE_ADDRESS);
        $pInputModelFormContactType->setValuesAvailable($availableContactType);
        $pInputModelFormContactType->setIsMulti(true);
        $selectedValue = $this->getValue($field);
        if (is_null($selectedValue)) {
            $selectedValue = [];
        }
        $pInputModelFormContactType->setValue($selectedValue);

        return $pInputModelFormContactType;
    }

    public function getDataContactType($module)
    {
        try {
            $pFieldLoader = $this->_pContainer->get(FieldLoaderGeneric::class);
            $pFieldCollectionAddressEstate = $this->_pContainer->get(FieldsCollectionBuilder::class)
                ->buildFieldsCollection($pFieldLoader);
            $fields = $pFieldCollectionAddressEstate->getFieldsByModule($module);
            $result = [];
            if (!empty($fields['ArtDaten']->getPermittedvalues())) {
                foreach ($fields['ArtDaten']->getPermittedvalues() as $field => $type) {
                    $result[$field] = !empty($type) ? $type: $field;
                }
            }

            return $result;
        } catch (APIClientCredentialsException $pCredentialsException) {
            return [];
        }

    }

	/**
	 * @return InputModelDB
	 */
	public function getInputModelIsRequired()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigForm();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Required', 'onoffice-for-wp-websites');
		$type = InputModelDBFactoryConfigForm::INPUT_FORM_REQUIRED;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsRequired'));

		return $pInputModel;
	}

	/**
	 * @return InputModelDB
	 */
	public function getInputModelIsAvailableOptions()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigForm();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Hide empty values from onOffice enterprise', 'onoffice-for-wp-websites');
		$type = InputModelDBFactoryConfigForm::INPUT_FORM_AVAILABLE_OPTIONS;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsAvailableOptions'));

		return $pInputModel;
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @return InputModelDB
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getInputModelDefaultValue(FieldsCollection $pFieldsCollection): InputModelDB
	{
		$pInputModelBuilder = $this->_pContainer->get(InputModelBuilderDefaultValue::class);
		return $pInputModelBuilder->createInputModelDefaultValue($pFieldsCollection, $this->getValue('defaultvalue', []));
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @return InputModelDB
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getInputModelCustomLabel(FieldsCollection $pFieldsCollection): InputModelDB
	{
		$pInputModelBuilder = $this->_pContainer->get(InputModelBuilderCustomLabel::class);
		return $pInputModelBuilder->createInputModelCustomLabel($pFieldsCollection, $this->getValue('customlabel', []));
	}


	/**
	 * @return InputModelDB
	 */

	public function getInputModelIsMarkDown()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigForm();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$text = __( 'Markdown', 'onoffice-for-wp-websites' );
		$linkMarkdown = sprintf( '<a href="' . __( 'https://wp-plugin.onoffice.com/en/advanced-features/markdown-labels',
				'onoffice-for-wp-websites' ) . '">%s</a>', $text );
		$label = sprintf( __( 'Label uses %s', 'onoffice-for-wp-websites' ), $linkMarkdown );
		$type = InputModelDBFactoryConfigForm::INPUT_FORM_MARK_DOWN;
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsMarkDown'));

		return $pInputModel;
	}

	/**
	 * @return InputModelDB
	 */
	public function getInputModelDefaultValueLanguageSwitch(): InputModelDB
	{
		$pInputModel = new InputModelDB('defaultvalue_newlang', __('Add language', 'onoffice-for-wp-websites'));
		$pInputModel->setTable('language');
		$pInputModel->setField('language');

		$pLanguageReader = new InstalledLanguageReader;
		$languages = ['' => __('Choose Language', 'onoffice-for-wp-websites')]
			+ $pLanguageReader->readAvailableLanguageNamesUsingNativeName();
		$pInputModel->setValuesAvailable(array_diff_key($languages, [get_locale() => []]));
		$pInputModel->setValueCallback(function(InputModelDB $pInputModel, string $key, string $type = null) {
			$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_HIDDEN);
			$pInputModel->setLabel('');

			if (FieldTypes::isStringType($type ?? '')) {
				$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
				$pInputModel->setLabel(__('Add language', 'onoffice-for-wp-websites'));
			}
		});

		return $pInputModel;
	}

	/**
	 * @return InputModelDB
	 */
	public function getInputModelCustomLabelLanguageSwitch(): InputModelDB
	{
		$pInputModel = new InputModelDB('customlabel_newlang',
			__('Add custom label language', 'onoffice-for-wp-websites'));
		$pInputModel->setTable('language-custom-label');
		$pInputModel->setField('language');

		$pLanguageReader = new InstalledLanguageReader;
		$languages = ['' => __('Choose Language', 'onoffice-for-wp-websites')]
			+ $pLanguageReader->readAvailableLanguageNamesUsingNativeName();
		$pInputModel->setValuesAvailable(array_diff_key($languages, [get_locale() => []]));
		$pInputModel->setValueCallback(function (InputModelDB $pInputModel) {
			$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
			$pInputModel->setLabel(__('Add custom label language', 'onoffice-for-wp-websites'));
		});

		return $pInputModel;
	}

	/**
	 * @param string $label
	 * @param string $type
	 * @param bool $checked
	 * @return InputModelDB
	 * @throws Exception
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
	 * @param string $label
	 * @param string $type
	 * @param bool $checked
	 * @param string $italicLabel
	 * @return InputModelDB
	 * @throws Exception
	 */
	private function generateItalicLabelCheckbox(string $label, string $type, bool $checked, string $italicLabel):
		InputModelDB
	{
		$pInputModel = $this->getInputModelDBFactory()->create($type, $label);
		if ($pInputModel === null) {
			throw new Exception('Unknown input model type');
		}

		$pInputModel->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModel->setItalicLabel($italicLabel);
		$pInputModel->setValue((int)$checked);
		$pInputModel->setValuesAvailable(1);
		return $pInputModel;
	}

	/**
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 */
	public function callbackValueInputModelIsRequired(InputModelBase $pInputModel, $key)
	{
		$fieldsRequired = $this->getValue('fieldsRequired');
		$value = in_array($key, $fieldsRequired);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}

	/**
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 */
	public function callbackValueInputModelIsAvailableOptions(InputModelBase $pInputModel, string $key)
	{
		$fieldsAvOpt = $this->getValue('fieldsAvailableOptions');
		$value = in_array($key, $fieldsAvOpt);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}

	/**
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 */
	public function callbackValueInputModelIsMarkDown(InputModelBase $pInputModel, $key)
	{
		$fieldsMarkDown = $this->getValue('fieldsMarkdown');
		$value = in_array($key, $fieldsMarkDown);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}

	/**
	 * @return InputModelDB
	 */
	public function getInputModelModule()
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigForm();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		$label = __('Module', 'onoffice-for-wp-websites');
		$type = InputModelDBFactoryConfigForm::INPUT_FORM_MODULE;
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_HIDDEN);
		$pInputModel->setValueCallback(function(InputModelBase $pInputModel, string $key) {
			$this->callbackValueInputModelModule($pInputModel, $key);
		});

		return $pInputModel;
	}

	/**
	 * @param InputModelBase $pInputModel
	 * @param string $key
	 * @throws DependencyException
	 * @throws NotFoundException
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
		$moduleTranslated = __(ModuleTranslation::getLabelSingular($module ?? ''), 'onoffice-for-wp-websites');
		$label = sprintf(__('Module: %s', 'onoffice-for-wp-websites'), $moduleTranslated);
		$pInputModel->setLabel($label);
		$pInputModel->setValue($module);
	}

	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelDB
	 *
	 */

	public function createButtonModelFieldsConfigByCategory($category, $fieldNames, $categoryLabel)
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, $category, true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_BUTTON_FIELD);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$pInputModelFieldsConfig->setLabel($categoryLabel);
		$fields = $this->getValue(DataFormConfiguration::FIELDS, []);
		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 * @return InputModelDB|null
	 */
	public function getInputModelIsHiddenField(): InputModelDB
	{
		$pInputModelFieldsConfig = new InputModelDBFactoryConfigForm();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFieldsConfig);
		$label = __('Hidden Field', 'onoffice-for-wp-websites');
		$type = InputModelDBFactoryConfigForm::INPUT_FORM_HIDDEN_FIELD;
		$pInputModel = $pInputModelFactory->create($type, $label, true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsHiddenField'));

		return $pInputModel;
	}

	/**
	 * @param InputModelBase $pInputModel
	 * @param string $key
	 */
	public function callbackValueInputModelIsHiddenField(InputModelBase $pInputModel, string $key)
	{
		$fieldsHiddenField = $this->getValue('fieldsHiddenField');
		$value = in_array($key, $fieldsHiddenField);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}

	/**
	 * @return InputModelDB|null
	 */
	public function getInputModelPerPageForm(): InputModelDB
	{
		$pInputModelFieldsConfig = new InputModelDBFactoryConfigForm();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFieldsConfig);
		$type = InputModelDBFactoryConfigForm::INPUT_FORM_PAGE_PER_FORM;
		$pInputModel = $pInputModelFactory->create($type, '', true);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_HIDDEN);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelFieldsPagePerForm'));

		return $pInputModel;
	}

	/**
	 * @param InputModelBase $pInputModel
	 * @param string $key
	 */
	public function callbackValueInputModelFieldsPagePerForm(InputModelBase $pInputModel, string $key)
	{
		$fieldsPagePerForm = $this->getValue('fieldsPagePerForm');
		$pInputModel->setValue($fieldsPagePerForm[$key] ?? 1);
		$pInputModel->setValuesAvailable($key);
	}

	/**
	 *
	 * @param $module
	 * @param string $htmlType
	 * @return InputModelDB
	 *
	 */

	public function createSearchFieldForFieldLists($module, string $htmlType)
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$pInputModelFieldsConfig->setHtmlType($htmlType);
		$pFieldsCollection = $this->getFieldsCollection();
		$fieldNames = [];

		if (is_array($module)) {
			foreach ($module as $submodule) {
				$newFields = $pFieldsCollection->getFieldsByModule($submodule ?? '');
				$fieldNames = array_merge($fieldNames, $newFields);
			}
		} else {
			$fieldNames = $pFieldsCollection->getFieldsByModule($module);
		}

		$fieldNamesArray = [];
		$pFieldsCollectionUsedFields = new FieldsCollection;

		foreach ($fieldNames as $pField) {
			$fieldNamesArray[$pField->getName()] = $pField->getAsRow();
			$fieldNamesArray[$pField->getName()]['page'] = $this->getValue('fieldsPagePerForm')[$pField->getName()] ?? 1;
			$pFieldsCollectionUsedFields->addField($pField);
		}

		$pInputModelFieldsConfig->setValuesAvailable($this->groupByContent($fieldNamesArray));
		$pInputModelFieldsConfig->setValue($this->getValue(DataFormConfiguration::FIELDS) ?? []);

		return $pInputModelFieldsConfig;
	}

	/**
	 * @return InputModelDB
	 */
	public function createInputModelWriteActivity(): InputModelDB
	{
		$labelWriteActivity = __('Write activity', 'onoffice-for-wp-websites');

		$pInputModelWriteActivity = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_WRITE_ACTIVITY, $labelWriteActivity);
		$pInputModelWriteActivity->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModelWriteActivity->setValue($this->getValue('writeactivity'));
		$pInputModelWriteActivity->setValuesAvailable(1);

		return $pInputModelWriteActivity;
	}

	/**
	 * @return InputModelDB
	 */

	public function createInputModelActionKind(): InputModelDB
	{
		$labelActionKind = __('Type of action', 'onoffice-for-wp-websites');

		$pInputModelActionKind = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_ACTION_KIND, $labelActionKind);
		$pInputModelActionKind->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$pInputModelActionKind->setValue($this->getValue('actionkind') ?? '');
		$actionKind = ['' => __('Please choose', 'onoffice-for-wp-websites')]
			+ $this->_actionKind;
		$pInputModelActionKind->setValuesAvailable($actionKind);

		return $pInputModelActionKind;
	}

	/**
	 * @return InputModelDB
	 */

	public function createInputModelActionType(): InputModelDB
	{
		$labelActionType = __('Kind of action', 'onoffice-for-wp-websites');

		$pInputModelActionType = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_ACTION_TYPE, $labelActionType);
		$pInputModelActionType->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$pInputModelActionType->setValue($this->getValue('actiontype') ?? '');
		$ationType = $this->_actionType[$this->getValue('actionkind')] ?? [];
		$pInputModelActionType->setValuesAvailable($ationType);

		return $pInputModelActionType;
	}

	/**
	 * @return void
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws ApiClientException
	 */

	private function fetchDataTypesOfActionAndCharacteristics()
	{
		$language = Language::getDefault();
		$pSDKWrapper = $this->_pContainer->get(SDKWrapper::class);
		$pApiClientAction = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'actionkindtypes');
		$pApiClientAction->setParameters(['lang'=> $language]);
		$pApiClientAction->addRequestToQueue();

		$parameters = [
			'labels' => true,
			'language' => $language,
			'fieldList' => ['merkmal', 'HerkunftKontakt'],
			'modules' => ['agentsLog', 'address']
		];

		$pApiClientActionGetCharacteristic = new APIClientActionGeneric
			($pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'fields');
		$pApiClientActionGetCharacteristic->setParameters($parameters);
		$pApiClientActionGetCharacteristic->addRequestToQueue()->sendRequests();

		$resultAction = $pApiClientAction->getResultRecords();
		$resultCharacteristic = $pApiClientActionGetCharacteristic->getResultRecords();

		if (!empty($resultAction)) {
			foreach ($resultAction as $record) {
				$this->_actionKind[$record['elements']['key']] = $record['elements']['label'];
				$this->_actionType[$record['elements']['key']] = $record['elements']['types'];

				if ($record['elements']['key'] === 'Aufgabe') {
					foreach ($record['elements']['typesByDatabaseId'] as $key => $value) {
						if (isset($record['elements']['types'][$value])) {
							$this->_taskType[$key] = $record['elements']['types'][$value];
						}
						if ($value === 'Rückruf') {
							$this->_taskType[$key] = __('Callback', 'onoffice-for-wp-websites');
						}
					}
				}
			}
		}

		if (!empty($resultCharacteristic)) {
			foreach (array_column($resultCharacteristic, 'elements') as $value) {
				if (isset($value['merkmal']))
				$this->_characteristic = $value['merkmal'];
				if (isset($value['HerkunftKontakt']))
				$this->_originContact = $value['HerkunftKontakt'];
			}
		}
	}

	/**
	 * @return InputModelDB
	 */

	public function createInputModelCharacteristic(): InputModelDB
	{
		$labelCharacteristic = __('Characteristic', 'onoffice-for-wp-websites');

		$pInputModelCharacteristic = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_CHARACTERISTIC, $labelCharacteristic);
		$pInputModelCharacteristic->setHtmlType(InputModelBase::HTML_TYPE_SELECT_TWO);
		$characteristicArray = explode(',', $this->getValue('characteristic')) ?? [];
		$pInputModelCharacteristic->setIsMulti(true);
		$pInputModelCharacteristic->setValue($characteristicArray);
		$pInputModelCharacteristic->setValuesAvailable($this->_characteristic['permittedvalues'] ?? []);

		return $pInputModelCharacteristic;
	}

	/**
	 * @return InputModelDB
	 */

	public function createInputModelOriginContact(): InputModelDB
	{
		$labelOriginContact = __('Origin Contact', 'onoffice-for-wp-websites');

		$pInputModelOriginContact = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_ORIGIN_CONTACT, $labelOriginContact);
		$pInputModelOriginContact->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$pInputModelOriginContact->setValue($this->getValue('origincontact') ?? '');
		$defaultOriginContact = ['' => __('Please choose', 'onoffice-for-wp-websites')]
			+ $this->_originContact['permittedvalues'];
		$pInputModelOriginContact->setValuesAvailable($defaultOriginContact ?? []);

		return $pInputModelOriginContact;
	}


	/**
	 * @return array
	 */

	private function getDefaultAdvisoryLevel() {
		return [
			'A' => __('A Rental/purchase contract signed', 'onoffice-for-wp-websites'),
			'B' => __('B Written rental / purchase commitment', 'onoffice-for-wp-websites'),
			'C' => __('C Intense communication', 'onoffice-for-wp-websites'),
			'D' => __('D Interested, but still reviewing', 'onoffice-for-wp-websites'),
			'E' => __('E Documentation received', 'onoffice-for-wp-websites'),
			'F' => __('F Documentation ordered', 'onoffice-for-wp-websites'),
			'G' => __('G Cancellation', 'onoffice-for-wp-websites'),
		];
	}

	/**
	 * @return InputModelDB
	 */

	public function createInputModelAdvisoryLevel(): InputModelDB
	{
		$labelAdvisoryLevel= __('Advisory Level', 'onoffice-for-wp-websites');

		$pInputModelAdvisoryLevel = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_ADVISORY_LEVEL, $labelAdvisoryLevel);
		$pInputModelAdvisoryLevel->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$pInputModelAdvisoryLevel->setValue($this->getValue('advisorylevel') ?? '');
		$defaultAdvisoryLevel = ['' => __('Please choose', 'onoffice-for-wp-websites')]
			+ $this->getDefaultAdvisoryLevel();
		$pInputModelAdvisoryLevel->setValuesAvailable($defaultAdvisoryLevel);

		return $pInputModelAdvisoryLevel;
	}

	/**
	 * @return InputModelDB
	 */

	public function createInputModelRemark(): InputModelDB
	{
		$labelRemark = __('Comment', 'onoffice-for-wp-websites');

		$pInputModelRemark = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_REMARK, $labelRemark);
		$pInputModelRemark->setHtmlType(InputModelBase::HTML_TYPE_TEXTAREA);
		$pInputModelRemark->setValue($this->getValue('remark') ?? '');

		return $pInputModelRemark;
	}

	/**
	 * @return InputModelDB
	 */
	public function createInputModelShowFormAsModal()
	{
		$labelShowFormAsModal = __('Show form as modal', 'onoffice-for-wp-websites');
		$pInputModelFormShowFormAsModal = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_SHOW_FORM_AS_MODAL, $labelShowFormAsModal);
		$pInputModelFormShowFormAsModal->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModelFormShowFormAsModal->setValue($this->getValue($pInputModelFormShowFormAsModal->getField(), true));
		$pInputModelFormShowFormAsModal->setValuesAvailable(1);

		return $pInputModelFormShowFormAsModal;
	}

	/**
	 * @return InputModelDB
	 */
	public function createInputModelSubject(): InputModelDB
	{
		$labelSubject = __('Subject (optional)', 'onoffice-for-wp-websites');

		$pInputModelFormSubject = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_SUBJECT, $labelSubject);
		$pInputModelFormSubject->setHtmlType(InputModelBase::HTML_TYPE_EMAIL_SUBJECT);
		$pInputModelFormSubject->setValue($this->getValue('subject'));
		$pInputModelFormSubject->setHintHtml(__('We recommend a maximum number of characters between 40 and 60 or up to 10 words.', 'onoffice-for-wp-websites'));

		return $pInputModelFormSubject;
	}

	/**
	 * @return InputModelDB
	 */
	public function createInputModelEnableCreateTask(): InputModelDB
	{
		$labelEnableCreateTask = __('Create tasks in onOffice enterprise', 'onoffice-for-wp-websites');

		$pInputModelEnableCreateTask = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_ENABLE_CREATE_TASK, $labelEnableCreateTask);
		$pInputModelEnableCreateTask->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModelEnableCreateTask->setValue($this->getValue('enable_create_task'));
		$pInputModelEnableCreateTask->setValuesAvailable(1);

		return $pInputModelEnableCreateTask;
	}

	/**
	 * @return InputModelDB
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function createInputModelTaskResponsibility(): InputModelDB
	{
		$labelTaskResponsibility = __('Responsibility', 'onoffice-for-wp-websites');

		$pInputModelResponsibility = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_TASK_RESPONSIBILITY, $labelTaskResponsibility);
		$pInputModelResponsibility->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$pInputModelResponsibility->setValue($this->getValue('taskResponsibility') ?? '');
		$supervisorData =  ['' => __('Please choose', 'onoffice-for-wp-websites')] + $this->getSupervisorData();
		$pInputModelResponsibility->setValuesAvailable($supervisorData);

		return $pInputModelResponsibility;
	}

	/**
	 * @return InputModelDB
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function createInputModelTaskProcessor(): InputModelDB
	{
		$labelTaskProcessor = __('Processor', 'onoffice-for-wp-websites');

		$pInputModelTaskProcessor = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_TASK_PROCESSOR, $labelTaskProcessor);
		$pInputModelTaskProcessor->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$pInputModelTaskProcessor->setValue($this->getValue('taskProcessor') ?? '');
		$supervisorData =  ['' => __('Please choose', 'onoffice-for-wp-websites')] + $this->getSupervisorData();
		$pInputModelTaskProcessor->setValuesAvailable($supervisorData);

		return $pInputModelTaskProcessor;
	}

	/**
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getSupervisorData(): array
	{
		$pFieldLoader = $this->_pContainer->make(FieldLoaderSupervisorValues::class, ['isReturnValueForUserNameElements' => true]);
		$pFieldCollectionSupervisor = $this->_pContainer->get(FieldsCollectionBuilder::class)->buildFieldsCollection($pFieldLoader);

		$supervisors = [];
		foreach ($pFieldCollectionSupervisor->getAllFields() as $pField) {
			$supervisors = $pField->getPermittedvalues();
		}

		return $supervisors;
	}

	/**
	 * @return InputModelDB
	 */
	public function createInputModelTaskType(): InputModelDB
	{
		$labelTaskType = __('Type', 'onoffice-for-wp-websites');

		$pInputModelTaskType = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_TASK_TYPE, $labelTaskType . '*');
		$pInputModelTaskType->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$pInputModelTaskType->setValue($this->getValue('taskType'));
		$taskType = ['' => __('Please choose', 'onoffice-for-wp-websites')] + $this->_taskType;
		$pInputModelTaskType->setValuesAvailable($taskType);

		return $pInputModelTaskType;
	}

	/**
	 * @return InputModelDB|null
	 */
	public function createInputModelTaskPriority(): InputModelDB
	{
		$labelTaskPriority = __('Priority', 'onoffice-for-wp-websites');
		$pInputModelTaskPriority = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_TASK_PRIORITY, $labelTaskPriority);
		$pInputModelTaskPriority->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$pInputModelTaskPriority->setValue($this->getValue('taskPriority') ?? '');

		$pInputModelTaskPriority->setValuesAvailable($this->getTasksPriority());

		return $pInputModelTaskPriority;
	}

	/**
	 * @return InputModelDB|null
	 */
	public function createInputModelTaskSubject(): InputModelDB
	{
		$labelTaskSubject = __('Subject', 'onoffice-for-wp-websites');
		$pInputModelTaskSubject = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_TASK_SUBJECT, $labelTaskSubject);
		$pInputModelTaskSubject->setHtmlType(InputModelBase::HTML_TYPE_TEXT);
		$pInputModelTaskSubject->setValue($this->getValue('taskSubject') ?? '');

		return $pInputModelTaskSubject;
	}

	/**
	 * @return InputModelDB
	 */
	public function createInputModelTaskDescription(): InputModelDB
	{
		$labelTaskDescription = __('Task description', 'onoffice-for-wp-websites');
		$pInputModelTaskDescription = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_TASK_DESCRIPTION, $labelTaskDescription);
		$pInputModelTaskDescription->setHtmlType(InputModelBase::HTML_TYPE_TEXTAREA);
		$pInputModelTaskDescription->setValue($this->getValue('taskDescription') ?? '');

		return $pInputModelTaskDescription;
	}

	/**
	 * @return InputModelDB
	 */
	public function createInputModelTaskStatus(): InputModelDB
	{
		$labelTaskStatus = __('Status', 'onoffice-for-wp-websites');
		$pInputModelTaskStatus = $this->getInputModelDBFactory()->create
		(InputModelDBFactoryConfigForm::INPUT_FORM_TASK_STATUS, $labelTaskStatus);
		$pInputModelTaskStatus->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$pInputModelTaskStatus->setValue($this->getValue('taskStatus') ?? 0);
		$pInputModelTaskStatus->setValuesAvailable($this->getTasksStatus());

		return $pInputModelTaskStatus;
	}

	/**
	 * @return array
	 */
	private function getTasksPriority(): array
	{
		return [
			InputModelDBFactoryConfigForm::TASK_NORMAL_PRIORITY => __('standard', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_HIGHEST_PRIORITY => __('highest', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_HIGH_PRIORITY => __('high', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_LOW_PRIORITY => __('low', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_LOWEST_PRIORITY => __('lowest', 'onoffice-for-wp-websites'),
		];
	}

	/**
	 * @return array
	 */
	private function getTasksStatus(): array
	{
		return [
			InputModelDBFactoryConfigForm::TASK_STATUS_NOT_START => __('Not started', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_STATUS_IN_PROCESS => __('In process', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_STATUS_COMPLETED => __('Completed', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_STATUS_DEFERRED => __('Deferred', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_STATUS_CANCELLED => __('Cancelled', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_STATUS_MISCELLANEOUS => __('Miscellaneous', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_STATUS_CHECKED => __('Checked', 'onoffice-for-wp-websites'),
			InputModelDBFactoryConfigForm::TASK_STATUS_NEED_CLARIFICATION => __('Need clarification', 'onoffice-for-wp-websites'),
		];
	}

	/** @return string */
	public function getFormType()
		{ return $this->_formType; }

	/** @param string $formType */
	public function setFormType($formType)
		{ $this->_formType = $formType; }
}
