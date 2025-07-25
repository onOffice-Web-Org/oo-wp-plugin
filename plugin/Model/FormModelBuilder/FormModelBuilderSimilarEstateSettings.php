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
use onOffice\WPlugin\DataView\DataDetailViewHandler;
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
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Types\ImageTypes;
use function __;
use onOffice\WPlugin\WP\InstalledLanguageReader;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderCustomLabel;
use DI\Container;

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

	/** @var Container */
	private $_pContainer = null;

	/**
	 * @param Container $pContainer
	 */

	public function __construct(Container $pContainer = null)
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainer ?? $pContainerBuilder->build();
	}

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
		$pFieldsCollectionBuilder = $this->_pContainer->get(FieldsCollectionBuilderShort::class);
		$pFieldsCollection = new FieldsCollection();

		$pFieldsCollectionBuilder
			->addFieldsAddressEstate($pFieldsCollection)
			->addFieldsEstateDecoratorReadAddressBackend($pFieldsCollection)
			->addFieldsEstateGeoPosisionBackend($pFieldsCollection);
		return $pFieldsCollection;
	}

	/**
	 * @param string $module
	 * @param string $htmlType
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
				$newFields = $pFieldsCollection->getFieldsByModule($submodule);
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
		if($module == onOfficeSDK::MODULE_ESTATE) {
			$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelIsHighlight());
		}
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
		$pInputModelRadius->setHtmlType(InputModelBase::HTML_TYPE_NUMBER);

		$pInputModelRadius->setValuesAvailable(1);
		$pInputModelRadius->setValue($pDataViewSimilarEstates->getRadius());
		$pInputModelRadius->setMinValueHtml(0);

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
		$pInputModelAmount->setHtmlType(InputModelBase::HTML_TYPE_NUMBER);

		$pInputModelAmount->setValuesAvailable(1);
		$pInputModelAmount->setValue($pDataViewSimilarEstates->getRecordsPerPage());
		$pInputModelAmount->setMinValueHtml(0);

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

	/**
	 * @return InputModelDB
	 */
	public function getInputModelIsHighlight(): InputModelDB
	{
		$pInputModelFactoryConfig = new InputModelDBFactoryConfigEstate();
		$pInputModelFactory = new InputModelDBFactory($pInputModelFactoryConfig);
		/* @var $pInputModel InputModelDB */
		$pInputModel = $pInputModelFactory->create(
			InputModelDBFactoryConfigEstate::INPUT_FIELD_HIGHLIGHTED,
			__('Feld besonders hervorheben', 'onoffice-for-wp-website'),
			true
		);
		$pInputModel->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModel->setValueCallback(array($this, 'callbackValueInputModelIsHighlight'));

		return $pInputModel;
	}

	/**
	 * @param InputModelBase $pInputModel
	 * @param string $key Name of input
	 */
	public function callbackValueInputModelIsHighlight(InputModelBase $pInputModel, $key)
	{
		$valueFromConf = $this->_pDataSimilarView->getHighlightedFields();
		$filterableFields = is_array($valueFromConf) ? $valueFromConf : array();
		$value = in_array($key, $filterableFields);
		$pInputModel->setValue($value);
		$pInputModel->setValuesAvailable($key);
	}

	/**
	 *
	 * @return InputModelOption
	 *
	 */

	public function createInputModelPictureTypes(): InputModelOption
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();
		$allPictureTypes = ImageTypes::getAllImageTypesTranslated();

		$pInputModelPictureTypes = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_PICTURE_TYPE, null, true);
		$pInputModelPictureTypes->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable($allPictureTypes);
		$pictureTypes = $pDataViewSimilarEstates->getPictureTypes();

		$pInputModelPictureTypes->setValue($pictureTypes);

		return $pInputModelPictureTypes;
	}

	/**
	 *
	 * @return InputModelDB
	 *
	 */

	public function createInputModelShowPriceOnRequest()
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();
		$labelShowPriceOnRequest = __('Show price on request', 'onoffice-for-wp-websites');

		$pInputModelShowPriceOnRequest = $this->_pInputModelSimilarViewFactory->create
		(InputModelOptionFactorySimilarView::INPUT_SHOW_PRICE_ON_REQUEST, $labelShowPriceOnRequest);
		$pInputModelShowPriceOnRequest->setHtmlType(InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowPriceOnRequest->setValue($pDataViewSimilarEstates->getShowPriceOnRequest());
		$pInputModelShowPriceOnRequest->setValuesAvailable(1);

		return $pInputModelShowPriceOnRequest;
	}

	/**
	 *
	 * @param $module
	 * @param string $htmlType
	 * @return InputModelOption
	 * @throws UnknownModuleException
	 * @throws ExceptionInputModelMissingField
	 *
	 */

	public function createSearchFieldForFieldLists($module, string $htmlType)
	{
		$fields = [];

		if ($module == onOfficeSDK::MODULE_ESTATE) {
			$pInputModelFieldsConfig = $this->_pInputModelSimilarViewFactory->create
			(InputModelOptionFactorySimilarView::INPUT_FIELD_CONFIG, null, true);
			$fields = $this->_pDataSimilarView->getFields();
		} else {
			throw new UnknownModuleException();
		}

		$pFieldsCollection = $this->getFieldsCollection();
		$fieldNames = $pFieldsCollection->getFieldsByModule($module);

		$fieldNamesArray = [];
		$pFieldsCollectionUsedFields = new FieldsCollection;

		foreach ($fieldNames as $pField) {
			$fieldNamesArray[$pField->getName()] = $pField->getAsRow();
			$pFieldsCollectionUsedFields->addField($pField);
		}

		$pInputModelFieldsConfig->setHtmlType($htmlType);
		$pInputModelFieldsConfig->setValuesAvailable($this->groupByContent($fieldNamesArray));
		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 * @return InputModelOption
	 */
	public function createInputModelShowReferenceEstates(): InputModelOption
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();
		$labelShowReferenceEstate = __('Reference estates', 'onoffice-for-wp-websites');

		$pInputModelShowReferenceEstate = $this->_pInputModelSimilarViewFactory->create
		(InputModelOptionFactorySimilarView::INPUT_SHOW_REFERENCE_ESTATE, $labelShowReferenceEstate);
		$pInputModelShowReferenceEstate->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$pInputModelShowReferenceEstate->setValue($pDataViewSimilarEstates->getShowReferenceEstate());
		$pInputModelShowReferenceEstate->setValuesAvailable(self::getListViewReferenceEstates());
		$pDataDetailViewHandler = $this->_pContainer->get(DataDetailViewHandler::class);
		$pDataDetailView = $pDataDetailViewHandler->getDetailView();
		$restrictAccessControl = $pDataDetailView->getViewRestrict();
		if ($restrictAccessControl) {
			$restrictedPageDetail = '<a href="' . esc_attr(admin_url('admin.php?page=onoffice-estates&tab=detail')) . '" target="_blank">' . __('restricted',
				'onoffice-for-wp-websites') . '</a>';
			$pInputModelShowReferenceEstate->setHintHtml(sprintf(__('Reference estates will not link to their detail page, because the access is %s.',
				'onoffice-for-wp-websites'), $restrictedPageDetail));
		} else {
			$restrictedPageDetail = '<a href="' . esc_attr(admin_url('admin.php?page=onoffice-estates&tab=detail')) . '" target="_blank">' . __('not restricted',
				'onoffice-for-wp-websites') . '</a>';
			$pInputModelShowReferenceEstate->setHintHtml(sprintf(__('Reference estates will link to their detail page, because the access is %s.',
				'onoffice-for-wp-websites'), $restrictedPageDetail));
		}

		return $pInputModelShowReferenceEstate;
	}

	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelFilter(): InputModelOption
	{
		$pDataViewSimilarEstates = $this->_pDataSimilarView->getDataViewSimilarEstates();
		$labelFilterName = __('Filter', 'onoffice-for-wp-websites');

		$pInputModelFilterName = $this->_pInputModelSimilarViewFactory->create
		(InputModelOptionFactorySimilarView::INPUT_FILTERID, $labelFilterName);
		$pInputModelFilterName->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$availableFilters = array(0 => '') + $this->readFilters(onOfficeSDK::MODULE_ESTATE);
		$pInputModelFilterName->setValuesAvailable($availableFilters);
		$pInputModelFilterName->setValue($pDataViewSimilarEstates->getFilterId());
		$linkUrl = __("https://de.enterprisehilfe.onoffice.com/help_entries/property-filter/?lang=en", "onoffice-for-wp-websites");
		$linkLabel = '<a href="' . $linkUrl . '" target="_blank">' . __('Learn more.', 'onoffice-for-wp-websites') . '</a>';
		$pInputModelFilterName->setHintHtml(sprintf(__('Choose an estate filter from onOffice enterprise. %s',
			'onoffice-for-wp-websites'), $linkLabel));

		return $pInputModelFilterName;
	}

	/**
	 * @return array
	 */
	private function getListViewReferenceEstates(): array
	{
		return [
			DataListView::HIDE_REFERENCE_ESTATE => __('Hide reference estates', 'onoffice-for-wp-websites'),
			DataListView::SHOW_REFERENCE_ESTATE => __('Show reference estates (alongside others)', 'onoffice-for-wp-websites'),
			DataListView::SHOW_ONLY_REFERENCE_ESTATE => __('Show only reference estates (filter out all others)', 'onoffice-for-wp-websites'),
		];
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function isHightlightedField(string $key): bool
	{
		return in_array($key, $this->_pDataSimilarView->getHighlightedFields() ?? []);
	}
}
