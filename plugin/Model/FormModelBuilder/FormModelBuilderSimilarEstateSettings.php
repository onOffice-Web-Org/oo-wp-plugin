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
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\Exception\UnknownModuleException;
use onOffice\WPlugin\DataView\DataSimilarView;
use onOffice\WPlugin\DataView\DataSimilarEstatesSettingsHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionBackend;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorInternalAnnotations;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactorySimilarView;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Types\FieldsCollection;
use function __;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
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


	/**
	 *
	 */

	public function __construct()
	{
		$pFieldCollection = new FieldModuleCollectionDecoratorInternalAnnotations
			(new FieldModuleCollectionDecoratorReadAddress
				(new FieldModuleCollectionDecoratorGeoPositionBackend(new FieldsCollection())));
		$pFieldnames = new Fieldnames($pFieldCollection);
		$pFieldnames->loadLanguage();
		$this->setFieldnames($pFieldnames);
	}


	/**
	 *
	 * @return FormModel
	 *
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
		$pFormModel->setLabel(__('Similar Estates View', 'onoffice'));
		$pFormModel->setGroupSlug('onoffice-similar-view-settings-main');
		$pFormModel->setPageSlug($pageSlug);

		return $pFormModel;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function getCheckboxEnableSimilarEstates()
	{
		$labelExpose = __('Show Similar Estates', 'onoffice');
		$pInputModelActivate = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_ENABLE_SIMILAR_ESTATES, $labelExpose);
		$pInputModelActivate->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelActivate->setValuesAvailable(1);
		$pInputModelActivate->setValue($this->_pDataSimilarView->getDataSimilarViewActive());

		return $pInputModelActivate;
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
	 *
	 * @param $module
	 * @param $htmlType
	 * @return InputModelOption
	 * @throws UnknownModuleException
	 * @throws ExceptionInputModelMissingField
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
		$fieldNames = $this->getFieldnames()->getFieldList($module);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setValue($fields);
		return $pInputModelFieldsConfig;
	}

	/**
	 * @param string $field
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelTemplate(string $field = InputModelOptionFactorySimilarView::INPUT_TEMPLATE)
	{
		$labelTemplate = __('Template', 'onoffice');
		$pInputModelTemplate = $this->_pInputModelSimilarViewFactory->create($field, $labelTemplate);
		$pInputModelTemplate->setHtmlType(InputModelOption::HTML_TYPE_SELECT);
		$pInputModelTemplate->setValuesAvailable($this->readTemplatePaths('estate'));
		$pInputModelTemplate->setValue($this->getTemplateValueByField($field));

		return $pInputModelTemplate;
	}


	/**
	 *
	 * @param string $field
	 * @return string
	 *
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
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSimilarEstateKind()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

		$labelSameKind = __('Same Kind of Estate', 'onoffice');

		$pInputModelSimilarEstateKind = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_SAME_KIND, $labelSameKind);
		$pInputModelSimilarEstateKind->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);

		$pInputModelSimilarEstateKind->setValuesAvailable(1);
		$pInputModelSimilarEstateKind->setValue($pDataViewSimilarEstates->getSameEstateKind());

		return $pInputModelSimilarEstateKind;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSimilarEstateMarketingMethod()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

		$labelSameMarketingMethod = __('Same Marketing Method', 'onoffice');

		$pInputModelSameMarketingMethod = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_SAME_MARKETING_METHOD, $labelSameMarketingMethod);
		$pInputModelSameMarketingMethod->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);

		$pInputModelSameMarketingMethod->setValuesAvailable(1);
		$pInputModelSameMarketingMethod->setValue($pDataViewSimilarEstates->getSameMarketingMethod());

		return $pInputModelSameMarketingMethod;
	}

    /**
     *
     * @return InputModelDB
     *
     */

    public function createInputModelDontShowArchived()
    {
        $pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

        $labelDontShowArchived= __('Don&#8217;t show archived estates', 'onoffice');

        $pInputModelDontShowArchived = $this->_pInputModelSimilarViewFactory->create
        (InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_DONT_SHOW_ARCHIVED, $labelDontShowArchived);
        $pInputModelDontShowArchived->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);

        $pInputModelDontShowArchived->setValuesAvailable(1);
        $pInputModelDontShowArchived->setValue($pDataViewSimilarEstates->getDontShowArchived());

        return $pInputModelDontShowArchived;
    }

    /**
     *
     * @return InputModelDB
     *
     */

    public function createInputModelDontShowReference()
    {
        $pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

        $labelDontShowReference= __('Don&#8217;t show reference estates', 'onoffice');

        $pInputModelDontShowReference = $this->_pInputModelSimilarViewFactory->create
        (InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_DONT_SHOW_REFERENCE, $labelDontShowReference);
        $pInputModelDontShowReference->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);

        $pInputModelDontShowReference->setValuesAvailable(1);
        $pInputModelDontShowReference->setValue($pDataViewSimilarEstates->getDontShowReference());

        return $pInputModelDontShowReference;
    }


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSameEstatePostalCode()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

		$labelSamePostalCode = __('Same Postal Code', 'onoffice');

		$pInputModelSamePostalCode = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_SAME_POSTAL_CODE, $labelSamePostalCode);
		$pInputModelSamePostalCode->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);

		$pInputModelSamePostalCode->setValuesAvailable(1);
		$pInputModelSamePostalCode->setValue($pDataViewSimilarEstates->getSamePostalCode());

		return $pInputModelSamePostalCode;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSameEstateRadius()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

		$labelRadius = __('Radius', 'onoffice');

		$pInputModelRadius = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_RADIUS, $labelRadius);
		$pInputModelRadius->setHtmlType(InputModelOption::HTML_TYPE_TEXT);

		$pInputModelRadius->setValuesAvailable(1);
		$pInputModelRadius->setValue($pDataViewSimilarEstates->getRadius());

		return $pInputModelRadius;
	}


	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelSameEstateAmount()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();

		$labelAmount = __('Amount of Estates', 'onoffice');

		$pInputModelAmount = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_AMOUNT, $labelAmount);
		$pInputModelAmount->setHtmlType(InputModelOption::HTML_TYPE_TEXT);

		$pInputModelAmount->setValuesAvailable(1);
		$pInputModelAmount->setValue($pDataViewSimilarEstates->getRecordsPerPage());

		return $pInputModelAmount;
	}
}
