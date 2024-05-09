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

use function __;
use DI\NotFoundException;
use DI\DependencyException;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Fieldnames;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\Utility\__String;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\DataView\DataAddressDetailView;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Controller\Exception\UnknownModuleException;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactoryAddressDetailView;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2020, onOffice(R) GmbH
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

	/** @var Fieldnames */
	private $_pFieldnames = null;

	/**
	 * @param Fieldnames $pFieldnames
	 */

	public function __construct(Fieldnames $pFieldnames = null)
	{
		$this->_pFieldnames = $pFieldnames ?? new Fieldnames(new FieldsCollection());

		$pFieldsCollection = new FieldsCollection();
		$pFieldnames = $pFieldnames ?? new Fieldnames($pFieldsCollection);
		$pFieldnames->loadLanguage();
		$this->setFieldnames($pFieldnames);
	}

	/**
	 * @param string $pageSlug
	 * @return FormModel
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
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
	 * @param string $module
	 * @param string $htmlType
	 * @return InputModelOption
	 * @throws DependencyException
	 * @throws ExceptionInputModelMissingField
	 * @throws NotFoundException
	 * @throws UnknownModuleException
	 */

	public function createSortableFieldList($module, string $htmlType)
	{
		if ($module == onOfficeSDK::MODULE_ADDRESS) {
			$pInputModelFieldsConfig = $this->_pInputModelAddressDetailFactory->create
			(InputModelOptionFactoryAddressDetailView::INPUT_FIELD_CONFIG, null, true);
		} else {
			throw new UnknownModuleException();
		}

		$fields = $this->_pDataAddressDetail->getFields();
		$fieldNames = $this->getFieldnames()->getFieldList($module);

		$pInputModelFieldsConfig->setValue($fields);
		$pInputModelFieldsConfig->setHtmlType($htmlType);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
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
		$fields = $this->getValue(DataListView::FIELDS);

		if (null == $fields)
		{
			$fields = array_merge(
				$this->_pDataAddressDetail->getFields()
			);
		}

		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}

	/**
	 *
	 * @return InputModelOption
	 *
	 */

	public function createInputModelPictureTypes()
	{
		$allPictureTypes = ImageTypes::getImageTypesForAddress();

		$pInputModelPictureTypes = $this->_pInputModelAddressDetailFactory->create
			(InputModelOptionFactoryAddressDetailView::INPUT_PICTURE_TYPE, null, true);
		$pInputModelPictureTypes->setHtmlType(InputModelBase::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable($allPictureTypes);
		$pictureTypes = $this->_pDataAddressDetail->getPictureTypes();

		if (null == $pictureTypes)
		{
			$pictureTypes = array(
			);
		}

		$pInputModelPictureTypes->setValue($pictureTypes);

		return $pInputModelPictureTypes;
	}

	/**
	 *
	 * @param $module
	 * @param string $htmlType
	 * @return InputModelOption
	 *
	 */

	public function createSearchFieldForFieldLists($module, string $htmlType)
	{
		$fields = [];

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

	protected function readNameShortCodeForm()
	{
		$recordManagerReadForm = new RecordManagerReadForm();
		$allRecordsForm = $recordManagerReadForm->getAllRecords();
		$shortCodeForm = array();

		foreach ($allRecordsForm as $value) {
			$form_name = __String::getNew($value->name);
			$shortCodeForm[$value->name] = '[oo_form form=&quot;'
				. esc_html($form_name) . '&quot;]';
		}
		return $shortCodeForm;
	}

	/**
	 *
	 * @return InputModelOption
	 *
	 * @throws UnknownFormException
	 * @throws ExceptionInputModelMissingField
	 */

	public function createInputModelShortCodeEstate()
	{
		$labelShortCodeEstate = __('Select Property Lists', 'onoffice-for-wp-websites');
		$pInputModelShortCodeEstate = $this->_pInputModelAddressDetailFactory->create
		(InputModelOptionFactoryAddressDetailView::INPUT_SHORT_CODE_ESTATE, $labelShortCodeEstate);
		$pInputModelShortCodeEstate->setHtmlType(InputModelBase::HTML_TYPE_SELECT);
		$nameShortCodeEstates = array('' => __('No Property Lists', 'onoffice-for-wp-websites')) + $this->readNameShortCodeEstate();
		$pInputModelShortCodeEstate->setValuesAvailable($nameShortCodeEstates);

		$pInputModelShortCodeEstate->setValue($this->_pDataAddressDetail->getShortCodeEstate());

		return $pInputModelShortCodeEstate;
	}

	/**
	 *
	 * @return array
	 *
	 * @throws UnknownFormException
	 */

	protected function readNameShortCodeEstate()
	{
		$recordManagerReadEstate = new RecordManagerReadListViewEstate();
		$allRecordsEstate = $recordManagerReadEstate->getNameAllRecords();
		$shortCodeEstate = array();

		foreach ($allRecordsEstate as $value) {
			$estate_name = __String::getNew($value->name);
			$shortCodeEstate[$value->name] = '[oo_estate view=&quot;'
				. esc_html($estate_name) . '&quot;]';
		}
		return $shortCodeEstate;
	}
}
