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
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
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
		$pInputModelFieldsConfig = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_FIELD_CONFIG, null, true);

		$fields = $this->_pDataAddressDetail->getFields();
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
		$pInputModelFieldsConfig = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_FIELD_CONFIG, null, true);
		$fields = $this->_pDataAddressDetail->getFields();

		$fieldNames = $this->getFieldnames()->getFieldList($module);

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
	 *
	 * @return InputModelOption
	 *
	 * @throws UnknownFormException
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelShortCodeForm()
	{
		$labelShortCodeForm = __('Select Contact Form', 'onoffice-for-wp-websites');
		$pInputModelShortCodeForm = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_SHORT_CODE_FORM, $labelShortCodeForm);
		$pInputModelShortCodeForm->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$nameShortCodeForms = array('' => __('No Contact Form', 'onoffice-for-wp-websites')) + $this->readNameShortCodeForm();
		$pInputModelShortCodeForm->setValuesAvailable($nameShortCodeForms);

		$pInputModelShortCodeForm->setValue($this->_pDataAddressDetail->getShortCodeForm());

		return $pInputModelShortCodeForm;
	}

	/**
	 *
	 * @return array
	 *
	 * @throws UnknownFormException
	 */

	private function readNameShortCodeForm(): array
	{
		$recordManagerReadForm = $this->_pContainer->get(RecordManagerReadForm::class);
		$allRecordsForm = $recordManagerReadForm->getAllRecords();
		$shortCodeForm = array();

		foreach ($allRecordsForm as $value) {
			$form_name = __String::getNew($value->name);
			$shortCodeForm[$value->name] = '[oo_form form=&quot;' . esc_html($form_name) . '&quot;]';
		}

		return $shortCodeForm;
	}

	/**
	 * @return array
	 */
	static public function getListViewReferenceEstates(): array
	{
		return array(
			DataListView::HIDE_REFERENCE_ESTATE => __('Hide reference estates', 'onoffice-for-wp-websites'),
			DataListView::SHOW_REFERENCE_ESTATE => __('Show reference estates (alongside others)', 'onoffice-for-wp-websites'),
			DataListView::SHOW_ONLY_REFERENCE_ESTATE => __('Show only reference estates (filter out all others)', 'onoffice-for-wp-websites'),
		);
	}

	/**
	 *
	 * @return InputModelOption
	 *
	 * @throws UnknownFormException
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelShortCodeActiveEstate(): InputModelOption
	{
		$labelShortCodeActiveEstate = __('Active Estate', 'onoffice-for-wp-websites');
		$pInputModelShortCodeActiveEstate = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_SHORT_CODE_ACTIVE_ESTATE, $labelShortCodeActiveEstate);
		$pInputModelShortCodeActiveEstate->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$nameShortCodeActiveEstate = array('' => __('No Estate', 'onoffice-for-wp-websites')) + $this->readNameShortCodeEstate();
		$pInputModelShortCodeActiveEstate->setValuesAvailable($nameShortCodeActiveEstate);

		$pInputModelShortCodeActiveEstate->setValue($this->_pDataAddressDetail->getShortCodeActiveEstate());

		return $pInputModelShortCodeActiveEstate;
	}

	/**
	 *
	 * @return InputModelOption
	 *
	 * @throws UnknownFormException
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelShortCodeReferenceEstate(): InputModelOption
	{
		$labelShortCodeReferenceEstate = __('Reference Estate', 'onoffice-for-wp-websites');
		$pInputModelShortCodeReferenceEstate = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_SHORT_CODE_REFERENCE_ESTATE, $labelShortCodeReferenceEstate);
		$pInputModelShortCodeReferenceEstate->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$nameShortCodeForms = array('' => __('No Estate', 'onoffice-for-wp-websites')) + $this->readNameShortCodeEstate();
		$pInputModelShortCodeReferenceEstate->setValuesAvailable($nameShortCodeForms);

		$pInputModelShortCodeReferenceEstate->setValue($this->_pDataAddressDetail->getShortCodeReferenceEstate());

		return $pInputModelShortCodeReferenceEstate;
	}
 
	/**
	 *
	 * @return array
	 *
	 * @throws UnknownFormException
	 */

	private function readNameShortCodeEstate(): array
	{
		$recordManagerReadEstate = $this->_pContainer->get(RecordManagerReadListViewEstate::class);
		$recordManagerReadEstate->addColumn('name');
		$recordManagerReadEstate->setLimit(100);
		$allRecordsEstate = $recordManagerReadEstate->getRecords();
		$shortCodeEstate = array();

		foreach ($allRecordsEstate as $value) {
			$estate_name = __String::getNew($value->name);
			$shortCodeEstate[$value->name] = '[oo_estate view=&quot;' . esc_html($estate_name) . '&quot;]';
		}

		return $shortCodeEstate;
	}
}
