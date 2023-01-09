<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\Exception\UnknownModuleException;
use onOffice\WPlugin\DataView\DataSimilarView;
use onOffice\WPlugin\DataView\DataSimilarEstatesSettingsHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactorySimilarView;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use function __;
use onOffice\WPlugin\WP\InstalledLanguageReader;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderCustomLabel;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2020, onOffice(R) GmbH
 *
 * This class must not use InputModelDB!
 *
 */

class FormModelBuilderSimilarEstateSettings
	extends FormModelBuilder
{
	/** @var InputModelOptionFactorySimilarView */
	private $_pInputModelSimilarViewFactory = null;

	/** @var DataSimilarView */
	private $_pDataSimilarView = null;

	/** @var array */
	private $_formModules = [];

	/**
	 * @param string $pageSlug
	 * @return FormModel
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */

	public function generate(string $pageSlug): FormModel
	{
		$this->_pInputModelSimilarViewFactory = new InputModelOptionFactorySimilarView($pageSlug);

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pContainerBuilder->build();
		$pDataSimilarEstatesSettingsHandler = $pContainer->get(DataSimilarEstatesSettingsHandler::class);
		$this->_pDataSimilarView = $pDataSimilarEstatesSettingsHandler->getDataSimilarEstatesSettings();

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('Similar Estates View', 'onoffice-for-wp-websites'));
		$pFormModel->setGroupSlug('onoffice-similar-view-settings-main');
		$pFormModel->setPageSlug($pageSlug);

		return $pFormModel;
	}


	/**
	 * @return InputModelDB
	 * @throws ExceptionInputModelMissingField
	 */

	public function getCheckboxEnableSimilarEstates()
	{
		$labelExpose = __('Show Similar Estates', 'onoffice-for-wp-websites');
		$pInputModelActivate = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_ENABLE_SIMILAR_ESTATES, $labelExpose);
		$pInputModelActivate->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelActivate->setValuesAvailable(1);
		$pInputModelActivate->setValue($this->_pDataSimilarView->getDataSimilarViewActive());

		return $pInputModelActivate;
	}


	/**
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelDB
	 */

	public function createInputModelFieldsConfigByCategory($category, $fieldNames, $categoryLabel)
	{
		$pInputModelFieldsConfig = new InputModelOption
			(null, $category, null, InputModelDBFactory::INPUT_FIELD_CONFIG);
		$pInputModelFieldsConfig->setIsMulti(true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX_BUTTON);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$pInputModelFieldsConfig->setLabel($categoryLabel);
		$fields = $this->getValue(DataListView::FIELDS);

		if (null == $fields)
		{
			$fields = array();
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 * @return FieldsCollection
	 * @throws DependencyException
	 * @throws NotFoundException
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
			->addFieldsAddressEstateWithRegionValues($pFieldsCollection)
			->addFieldsEstateDecoratorReadAddressBackend($pFieldsCollection)
			->addFieldsEstateGeoPosisionBackend($pFieldsCollection);
		return $pFieldsCollection;
	}

	/**
	 * @param $module
	 * @param $htmlType
	 * @return InputModelOption
	 * @throws DependencyException
	 * @throws ExceptionInputModelMissingField
	 * @throws NotFoundException
	 * @throws UnknownModuleException
	 */

	public function createSortableFieldList($module, $htmlType)
	{
		$fields = [];

		if ($module == onOfficeSDK::MODULE_ESTATE) {
			$pInputModelFieldsConfig = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_CONFIG, null, true);
			$fields = $this->_pDataSimilarView->getFields();

		} else {
			throw new UnknownModuleException();
		}

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
			$pFieldsCollectionUsedFields->addField($pField);
		}

		$pInputModelFieldsConfig->setValuesAvailable($fieldNamesArray);
		$pInputModelFieldsConfig->setValue($fields);
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelCustomLabel($pFieldsCollectionUsedFields));
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelCustomLabelLanguageSwitch());
		return $pInputModelFieldsConfig;
	}

	/**
	 * @param string $field
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelTemplate(string $field = InputModelOptionFactorySimilarView::INPUT_TEMPLATE)
	{
		$labelTemplate = __('Template', 'onoffice-for-wp-websites');
		$pInputModelTemplate = $this->_pInputModelSimilarViewFactory->create($field, $labelTemplate);
		$pInputModelTemplate->setHtmlType(InputModelOption::HTML_TYPE_TEMPLATE_LIST);
		$pInputModelTemplate->setValuesAvailable($this->readTemplatePaths('estate'));
		$pInputModelTemplate->setValue($this->getTemplateValueByField($field));

		return $pInputModelTemplate;
	}


	/**
	 * @param string $field
	 * @return string
	 */

	private function getTemplateValueByField(string $field): string
	{
		switch ($field) {
			case InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_TEMPLATE:
				return $this->_pDataSimilarView->getDataViewSimilarEstates()->getTemplate();
			default:
				return '';
		}
	}


	/**
	 * @return InputModelDB
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelSimilarEstateKind()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

		$labelSameKind = __('Same Kind of Estate', 'onoffice-for-wp-websites');

		$pInputModelSimilarEstateKind = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_SAME_KIND, $labelSameKind);
		$pInputModelSimilarEstateKind->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);

		$pInputModelSimilarEstateKind->setValuesAvailable(1);
		$pInputModelSimilarEstateKind->setValue($pDataViewSimilarEstates->getSameEstateKind());

		return $pInputModelSimilarEstateKind;
	}


	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelSimilarEstateMarketingMethod()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

		$labelSameMarketingMethod = __('Same Marketing Method', 'onoffice-for-wp-websites');

		$pInputModelSameMarketingMethod = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_SAME_MARKETING_METHOD, $labelSameMarketingMethod);
		$pInputModelSameMarketingMethod->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);

		$pInputModelSameMarketingMethod->setValuesAvailable(1);
		$pInputModelSameMarketingMethod->setValue($pDataViewSimilarEstates->getSameMarketingMethod());

		return $pInputModelSameMarketingMethod;
	}


	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelSameEstatePostalCode()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

		$labelSamePostalCode = __('Same Postal Code', 'onoffice-for-wp-websites');

		$pInputModelSamePostalCode = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_SAME_POSTAL_CODE, $labelSamePostalCode);
		$pInputModelSamePostalCode->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);

		$pInputModelSamePostalCode->setValuesAvailable(1);
		$pInputModelSamePostalCode->setValue($pDataViewSimilarEstates->getSamePostalCode());

		return $pInputModelSamePostalCode;
	}


	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelSameEstateRadius()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

		$labelRadius = __('Radius', 'onoffice-for-wp-websites');

		$pInputModelRadius = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_RADIUS, $labelRadius);
		$pInputModelRadius->setHtmlType(InputModelOption::HTML_TYPE_TEXT);

		$pInputModelRadius->setValuesAvailable(1);
		$pInputModelRadius->setValue($pDataViewSimilarEstates->getRadius());

		return $pInputModelRadius;
	}


	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelSameEstateAmount()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

		$labelAmount = __('Amount of Estates', 'onoffice-for-wp-websites');

		$pInputModelAmount = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_AMOUNT, $labelAmount);
		$pInputModelAmount->setHtmlType(InputModelOption::HTML_TYPE_TEXT);

		$pInputModelAmount->setValuesAvailable(1);
		$pInputModelAmount->setValue($pDataViewSimilarEstates->getRecordsPerPage());

		return $pInputModelAmount;
	}

	/**
	 * @param InputModelOptionFactorySimilarView $pInputModelSimilarViewFactory
	 */
	public function setInputModelSimilarViewFactory(InputModelOptionFactorySimilarView $pInputModelSimilarViewFactory)
	{
		$this->_pInputModelSimilarViewFactory = $pInputModelSimilarViewFactory;
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
		$pInputModelFieldsConfig = new InputModelOption
			(null, $category, null, InputModelDBFactory::INPUT_FIELD_CONFIG);
		$pInputModelFieldsConfig->setIsMulti(true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_BUTTON_FIELD);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$pInputModelFieldsConfig->setLabel($categoryLabel);
		$fields = $this->getValue(DataListView::FIELDS);

		if (null == $fields)
		{
			$fields = array_merge(
				$this->_pDataSimilarView->getFields()
			);
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @return InputModelDB
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getInputModelCustomLabel(FieldsCollection $pFieldsCollection): InputModelDB
	{
		$pDIContainerBuilder = new ContainerBuilder();
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		$pInputModelBuilder = $pContainer->get(InputModelBuilderCustomLabel::class);
		return $pInputModelBuilder->createInputModelCustomLabel($pFieldsCollection, $this->getValue('customlabel', []));
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
}
