<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

use function __;
use DI\NotFoundException;
use DI\DependencyException;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\DataView\DataAddressDetailView;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactoryAddressDetailView;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\WP\InstalledLanguageReader;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderCustomLabel;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\Exception\UnknownModuleException;
use onOffice\WPlugin\DataView\DataListView;
use DI\ContainerBuilder;
use DI\Container;
use onOffice\WPlugin\Types\Field;
use Exception;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 * This class must not use InputModelOption!
 *
 */

class FormModelBuilderAddressDetailSettings
	extends FormModelBuilder
{
	/** @var InputModelOptionFactoryAddressDetailView */
	private $_pInputModelAddressDetailFactory = null;

	/** @var DataAddressDetailView */
	private $_pDataAddressDetail = null;

	/** @var Container */
	private $_pContainer;

	/**
	 * @param Container|null $pContainer
	 * @param Fieldnames|null $pFieldnames
	 * @throws Exception
	 */

	public function __construct(Container $pContainer = null, Fieldnames $pFieldnames = null)
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainer ?? $pContainerBuilder->build();

		$pFieldsCollection = new FieldsCollection();
		$pFieldnames = $pFieldnames ?? new Fieldnames($pFieldsCollection);
		$pFieldnames->loadLanguage();
		$this->setFieldnames($pFieldnames);
	}

	/**
	 * @param string $pageSlug
	 * @return FormModel
	 */

	public function generate(string $pageSlug): FormModel
	{
		$this->_pInputModelAddressDetailFactory = new InputModelOptionFactoryAddressDetailView($pageSlug);
		$pDataAddressDetailViewHandler = new DataAddressDetailViewHandler;
		$this->_pDataAddressDetail = $pDataAddressDetailViewHandler->getAddressDetailView();

		$pFormModel = new FormModel();
		$pFormModel->setLabel(__('Address Detail View', 'onoffice-for-wp-websites'));
		$pFormModel->setGroupSlug('onoffice-address-detail-settings-main');
		$pFormModel->setPageSlug($pageSlug);

		return $pFormModel;
	}

	/**
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelOption
	 */

	public function createInputModelFieldsConfigByCategory($category, $fieldNames, $categoryLabel): InputModelOption
	{
		$pInputModelFieldsConfig = new InputModelOption
			(null, $category, null, InputModelDBFactory::INPUT_FIELD_CONFIG);
		$pInputModelFieldsConfig->setIsMulti(true);

		$pInputModelFieldsConfig->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX_BUTTON);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
		$pInputModelFieldsConfig->setLabel($categoryLabel);
		$fields = $this->getValue(DataDetailView::FIELDS);

		if (null == $fields)
		{
			$fields = array();
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 * @param $module
	 * @param string $htmlType
	 * @return InputModelOption
	 * @throws DependencyException
	 * @throws ExceptionInputModelMissingField
	 * @throws NotFoundException
	 */
	public function createSortableFieldList($module, string $htmlType): InputModelOption
	{
		$fields = [];

		if ($module == onOfficeSDK::MODULE_ADDRESS) {
			$pInputModelFieldsConfig = $this->_pInputModelAddressDetailFactory->create
				(InputModelOptionFactoryAddressDetailView::INPUT_FIELD_CONFIG, null, true);
			$fields = $this->_pDataAddressDetail->getFields();
		} elseif ($module == onOfficeSDK::MODULE_ESTATE) {
			$pInputModelFieldsConfig = $this->_pInputModelAddressDetailFactory->create
				(InputModelOptionFactoryAddressDetailView::INPUT_ESTATE_FIELD_CONFIG, null, true);
			$fields = $this->_pDataAddressDetail->getEstateFields();
		} else {
			throw new UnknownModuleException();
		}
	
		$fieldNames = $this->getFieldnames()->getFieldList($module);

		$fieldNamesArray = [];
		$pFieldsCollectionUsedFields = new FieldsCollection;

		foreach ($fieldNames as $name => $pField) {
			$pFields = Field::createByRow($name, $pField);
			$fieldNamesArray[$pFields->getName()] = $pFields->getAsRow();
			$pFieldsCollectionUsedFields->addField($pFields);
		}

		$pInputModelFieldsConfig->setValue($fields);
		$pInputModelFieldsConfig->setHtmlType($htmlType);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNamesArray);
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelCustomLabel($pFieldsCollectionUsedFields));
		$pInputModelFieldsConfig->addReferencedInputModel($this->getInputModelCustomLabelLanguageSwitch());

		return $pInputModelFieldsConfig;
	}

	/**
	 * @param string $field
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelTemplate(string $field = InputModelOptionFactoryAddressDetailView::INPUT_TEMPLATE)
	{
		$labelTemplate = __('Template', 'onoffice-for-wp-websites');
		$pInputModelTemplate = $this->_pInputModelAddressDetailFactory->create($field, $labelTemplate);
		$pInputModelTemplate->setHtmlType(InputModelBase::HTML_TYPE_TEMPLATE_LIST);
		$pInputModelTemplate->setValuesAvailable($this->readTemplatePaths('address'));
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
			case InputModelOptionFactoryAddressDetailView::INPUT_TEMPLATE:
				return $this->_pDataAddressDetail->getTemplate();
			default:
				return '';
		}
	}
		
	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @param string $categoryLabel
	 * @return InputModelOption
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
		$fields = $this->getValue(DataDetailView::FIELDS);

		if ($fields == null) {
			$fields = array_merge($this->_pDataAddressDetail->getFields());
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 *
	 * @return InputModelOption
	 *
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelPictureTypes(): InputModelOption
	{
		$allPictureTypes = ImageTypes::getImageTypesForAddress();

		$pInputModelPictureTypes = $this->_pInputModelAddressDetailFactory->create
			(InputModelOptionFactoryAddressDetailView::INPUT_PICTURE_TYPE, null, true);
		$pInputModelPictureTypes->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable($allPictureTypes);
		$pictureTypes = $this->_pDataAddressDetail->getPictureTypes();

		if ($pictureTypes == null) {
			$pictureTypes = array();
		}

		$pInputModelPictureTypes->setValue($pictureTypes);

		return $pInputModelPictureTypes;
	}

	/**
	 * @param $module
	 * @param string $htmlType
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */

	public function createSearchFieldForFieldLists($module, string $htmlType): InputModelOption
	{
		$fields = [];

		$pInputModelFieldsConfig = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_FIELD_CONFIG, null, true);
		$fields = $this->_pDataAddressDetail->getFields();
		$fieldNames = [];

		if (is_array($module)) {
			foreach ($module as $submodule) {
				$newFields = $this->getFieldnames()->getFieldList($submodule);
				$fieldNames = array_merge($fieldNames, $newFields);
			}
		}

		foreach ($fieldNames as $key => $pField) {
			$action = $pField['module'] === onOfficeSDK::MODULE_ADDRESS ? onOfficeSDK::MODULE_ADDRESS : onOfficeSDK::MODULE_ESTATE;
			$fieldNames[$key]['action'] = $action;
		}

		$fields = array_merge($this->_pDataAddressDetail->getFields(), $this->_pDataAddressDetail->getEstateFields()) ?? [];
		$pInputModelFieldsConfig->setHtmlType($htmlType);
		$pInputModelFieldsConfig->setValuesAvailable($this->groupByContent($fieldNames));
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
		$pInputModelBuilder = $this->_pContainer->get(InputModelBuilderCustomLabel::class);

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
	 * @return array
	 */
	static public function getListViewReferenceEstates(): array
	{
		return array(
			DataListView::HIDE_REFERENCE_ESTATE => __( 'Hide reference estates', 'onoffice-for-wp-websites' ),
			DataListView::SHOW_REFERENCE_ESTATE => __( 'Show reference estates (alongside others)', 'onoffice-for-wp-websites' ),
			DataListView::SHOW_ONLY_REFERENCE_ESTATE => __( 'Show only reference estates (filter out all others)', 'onoffice-for-wp-websites' ),
		);
	}

	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelShowLinkEstates(): InputModelOption
	{
		$labelShowStatus = __('Show Linked Estates', 'onoffice-for-wp-websites');

		$pInputModelShowStatus = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_SHOW_LINK_ESTATES, $labelShowStatus);
		$pInputModelShowStatus->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);

		$showEstateStatus = $this->_pDataAddressDetail->getShowLinkEstates();

		$pInputModelShowStatus->setValue($showEstateStatus);
		$pInputModelShowStatus->setValuesAvailable(1);

		return $pInputModelShowStatus;
	}

	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelShowEstatesStatus(): InputModelOption
	{
		$labelShowStatus = __('Show Estates Status', 'onoffice-for-wp-websites');

		$pInputModelShowStatus = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_SHOW_ESTATES_STATUS, $labelShowStatus);
		$pInputModelShowStatus->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);

		$showEstateStatus = $this->_pDataAddressDetail->getShowEstateStatus();

		$pInputModelShowStatus->setValue($showEstateStatus);
		$pInputModelShowStatus->setValuesAvailable(1);

		return $pInputModelShowStatus;
	}

	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelShowReferenceEstates(): InputModelOption
	{
		$labelShowReferenceEstate = __('Reference estates', 'onoffice-for-wp-websites');

		$pInputModelShowReferenceEstate = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_SHOW_REFERENCE_ESTATE, $labelShowReferenceEstate);
		$pInputModelShowReferenceEstate->setHtmlType(InputModelBase::HTML_TYPE_SELECT);

		$showReferenceEstate = $this->_pDataAddressDetail->getShowReferenceEstate();

		$pInputModelShowReferenceEstate->setValue($showReferenceEstate);
		$pInputModelShowReferenceEstate->setValuesAvailable(self::getListViewReferenceEstates());

		return $pInputModelShowReferenceEstate;
	}

	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelFilter(): InputModelOption
	{
		$labelFilterName = __('Filter', 'onoffice-for-wp-websites');
		$pInputModelFilterName = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_FILTERID, $labelFilterName);
		$pInputModelFilterName->setHtmlType(InputModelBase::HTML_TYPE_SELECT);

		$availableFilters = array(0 => '') + $this->readFilters(onOfficeSDK::MODULE_ESTATE);
		$filterIdSelected = $this->_pDataAddressDetail->getFilter();

		$pInputModelFilterName->setValuesAvailable($availableFilters);
		$pInputModelFilterName->setValue($filterIdSelected);

		return $pInputModelFilterName;
	}

	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelRecordsPerPage(): InputModelOption
	{
		$labelRecordsPerPage = __('Estates per page', 'onoffice-for-wp-websites');
		$pInputModelRecordsPerPage = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_RECORDS_PER_PAGE, $labelRecordsPerPage);
		$pInputModelRecordsPerPage->setHtmlType(InputModelBase::HTML_TYPE_NUMBER);
		$pInputModelRecordsPerPage->setValue($this->_pDataAddressDetail->getRecordsPerPage());
		$pInputModelRecordsPerPage->setMaxValueHtml(500);
		$pInputModelRecordsPerPage->setHintHtml(__('You can show up to 500 per page.', 'onoffice-for-wp-websites'));

		return $pInputModelRecordsPerPage;
	}

	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelShowPriceOnRequest(): InputModelOption
	{
		$labelShowPriceOnRequest = __('Show price on request', 'onoffice-for-wp-websites');

		$pInputModelShowPriceOnRequest = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_SHOW_PRICE_ON_REQUEST, $labelShowPriceOnRequest);
		$pInputModelShowPriceOnRequest->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModelShowPriceOnRequest->setValue($this->_pDataAddressDetail->getShowPriceOnRequest());
		$pInputModelShowPriceOnRequest->setValuesAvailable(1);

		return $pInputModelShowPriceOnRequest;
	}

	/**
	 * @return InputModelOption
	 * @throws ExceptionInputModelMissingField
	 */
	public function createInputModelShowMap(): InputModelOption
	{
		$labelShowMap = __('Show estate map', 'onoffice-for-wp-websites');

		$pInputModelShowMap = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_SHOW_MAP, $labelShowMap);
		$pInputModelShowMap->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModelShowMap->setValue($this->_pDataAddressDetail->getShowEstateMap() ?? true);
		$pInputModelShowMap->setValuesAvailable(1);

		return $pInputModelShowMap;
	}
}
