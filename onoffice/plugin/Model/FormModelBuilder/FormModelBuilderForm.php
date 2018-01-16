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

use Exception;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Utility\ModuleTranslation;

/**
 *
 */

class FormModelBuilderForm
	extends FormModelBuilder
{
	/** @var InputModelDBFactory */
	private $_pInputModelDBFactory = null;

	/** @var string */
	private $_formType = null;

	/** @var Fieldnames */
	private $_pFieldNames = null;


	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$pConfigForm = new InputModelDBFactoryConfigForm();
		$this->_pInputModelDBFactory = new InputModelDBFactory($pConfigForm);
		$this->_pFieldNames = new Fieldnames();
		$this->_pFieldNames->loadLanguage();
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
		$fieldNames = array();

		if (is_array($module)) {
			foreach ($module as $submodule) {
				$newFields = $this->_pFieldNames->getFieldList($submodule, true, true);
				$fieldNames = array_merge($fieldNames, $newFields);
			}
		} else {
			$fieldNames = $this->_pFieldNames->getFieldList($module, true, true);
		}

		$fieldNames = array_merge($fieldNames, $this->getAdditionalFields());

		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$fields = $this->getValue(DataFormConfiguration::FIELDS);

		if (null == $fields)
		{
			$fields = array();
		}

		$pInputModelFieldsConfig->setValue($fields);

		$pModule = $this->getInputModelModule();
		$pReferenceIsRequired = $this->getInputModelIsRequired();
		$pInputModelFieldsConfig->addReferencedInputModel($pModule);
		$pInputModelFieldsConfig->addReferencedInputModel($pReferenceIsRequired);

		return $pInputModelFieldsConfig;
	}


	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @return InputModelDB
	 *
	 */

	public function createInputModelFieldsConfigByCategory($category, $fieldNames)
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, $category, true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX_BUTTON);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$fields = $this->getValue(DataFormConfiguration::FIELDS);

		if (null == $fields) {
			$fields = array();
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}


	/**
	 *
	 * @param int $formId
	 * @return FormModel
	 *
	 */

	public function generate($formId = null)
	{
		if ($this->_formType === null) {
			throw new Exception('formType must be set!');
		}

		$values = array();
		$values['fieldsRequired'] = array();

		if ($formId !== null) {
			$pRecordReadManager = new RecordManagerReadForm();
			$values = $pRecordReadManager->getRowById($formId);
			$pFactory = new DataFormConfigurationFactory($this->_formType);
			$pDataFormConfiguration = $pFactory->loadByFormId($formId);
			$values[DataFormConfiguration::FIELDS] = array_keys($pDataFormConfiguration->getInputs());
			$values['fieldsRequired'] = $pDataFormConfiguration->getRequiredFields();
		}

		$this->setValues($values);
		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('Form', 'onoffice'));
		$pFormModel->setGroupSlug('onoffice-form-settings');
		$pFormModel->setPageSlug($this->getPageSlug());

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
	 * @return InputModelDB
	 *
	 */

	public function createInputModelTemplate()
	{
		$labelTemplate = __('Template', 'onoffice');
		$selectedTemplate = $this->getValue('template');

		$pInputModelTemplate = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_TEMPLATE, $labelTemplate);
		$pInputModelTemplate->setHtmlType(InputModelOption::HTML_TYPE_SELECT);

		$pInputModelTemplate->setValuesAvailable($this->readTemplatePaths('form'));
		$pInputModelTemplate->setValue($selectedTemplate);

		return $pInputModelTemplate;
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

	public function createInputModelSubject()
	{
		$labelSubject = __('Subject (optional)', 'onoffice');
		$selectedSubject = $this->getValue('subject');

		$pInputModelFormSubject = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_SUBJECT, $labelSubject);
		$pInputModelFormSubject->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelFormSubject->setValue($selectedSubject);

		return $pInputModelFormSubject;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelCreateAddress()
	{
		$labelCreateAddress = __('Create Address', 'onoffice');
		$selectedValue = $this->getValue('createaddress');

		$pInputModelFormCreateAddress = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_CREATEADDRESS, $labelCreateAddress);
		$pInputModelFormCreateAddress->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelFormCreateAddress->setValue($selectedValue);
		$pInputModelFormCreateAddress->setValuesAvailable(1);

		return $pInputModelFormCreateAddress;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelCheckDuplicates()
	{
		$labelCheckDuplicates = __('Check for Duplicates', 'onoffice');
		$selectedValue = $this->getValue('checkduplicates');

		$pInputModelFormCheckDuplicates = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_CHECKDUPLICATES, $labelCheckDuplicates);
		$pInputModelFormCheckDuplicates->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelFormCheckDuplicates->setValue($selectedValue);
		$pInputModelFormCheckDuplicates->setValuesAvailable(1);

		return $pInputModelFormCheckDuplicates;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelResultLimit()
	{
		$labelResultLimit = __('Result Limit', 'onoffice');
		$selectedValue = $this->getValue('limitresult');

		$pInputModelFormLimitResult = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_LIMIT_RESULTS, $labelResultLimit);
		$pInputModelFormLimitResult->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelFormLimitResult->setValue($selectedValue);

		return $pInputModelFormLimitResult;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelPages()
	{
		$labelPages = __('Pages', 'onoffice');
		$selectedValue = $this->getValue('pages');

		$pInputModelFormPages = $this->getInputModelDBFactory()->create
			(InputModelDBFactoryConfigForm::INPUT_FORM_PAGES, $labelPages);
		$pInputModelFormPages->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelFormPages->setValue($selectedValue);

		return $pInputModelFormPages;
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
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 * @return bool
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
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelModule'));

		return $pInputModel;
	}


	/**
	 *
	 * @param InputModelBase $pInputModel
	 * @param string $key
	 *
	 */

	public function callbackValueInputModelModule(InputModelBase $pInputModel, $key)
	{
		$module = $this->_pFieldNames->getModuleByField($key);
		$moduleTranslated = __(ModuleTranslation::getLabelSingular($module), 'onoffice');
		$label = sprintf(__('Module: %s', 'onoffice'), $moduleTranslated);
		$pInputModel->setLabel($label);
		$pInputModel->setValue($module);
	}


	/**
	 *
	 * @return InputModelDBFactory
	 *
	 */

	protected function getInputModelDBFactory()
		{ return $this->_pInputModelDBFactory; }


	/** @return string */
	public function getFormType()
		{ return $this->_formType; }

	/** @param string $formType */
	public function setFormType($formType)
		{ $this->_formType = $formType; }
}
