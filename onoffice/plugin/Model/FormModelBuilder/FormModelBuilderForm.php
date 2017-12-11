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

use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;

/**
 *
 */

class FormModelBuilderForm
	extends FormModelBuilder
{
	/** @var InputModelDBFactory */
	private $_pInputModelDBFactory = null;

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
	}



	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModelDB
	 *
	 */

	public function createInputModelFieldsConfig()
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$fieldNames = $this->readFieldnames();
		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_CHECKBOX_LIST);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$fields = $this->getValue(DataFormConfiguration::FIELDS);

		if (null == $fields)
		{
			$fields = array();
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}


	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @return \onOffice\WPlugin\Model\InputModelDB
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

		if (null == $fields)
		{
			$fields = array();
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createSortableFieldList()
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST);

		$fieldNames = $this->readFieldnames(\onOffice\SDK\onOfficeSDK::MODULE_ESTATE);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);

		$fields = $this->getValue(DataFormConfiguration::FIELDS);

		if (null == $fields)
		{
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
		if ($formId !== null)
		{
			$pRecordReadManager = new RecordManagerReadForm();
			$values = $pRecordReadManager->getRowById($formId);
			$this->setValues($values);
		}

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('Form', 'onoffice'));
		$pFormModel->setGroupSlug('onoffice-form-settings');
		$pFormModel->setPageSlug($this->getPageSlug());

		return $pFormModel;
	}


	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModelDB
	 *
	 */

	public function createInputModelName()
	{
		$labelName = __('Form Name', 'onoffice');

		$pInputModelName = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_LISTNAME, null);
		$pInputModelName->setPlaceholder($labelName);
		$pInputModelName->setHtmlType(InputModelOption::HTML_TYPE_TEXT);
		$pInputModelName->setValue($this->getValue($pInputModelName->getField()));

		return $pInputModelName;
	}


	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModelDB
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
	 * @return InputModelDBFactory
	 *
	 */

	protected function getInputModelDBFactory()
		{ return $this->_pInputModelDBFactory; }
}
