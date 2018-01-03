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

use onOffice\WPlugin\Model;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactoryDetailView;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;
use onOffice\WPlugin\DataView\DataDetailViewHandler;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModelBuilderEstateDetailSettings
	extends FormModelBuilderEstate
{
	/** @var InputModelOptionFactoryDetailView */
	private $_pInputModelDetailViewFactory = null;

	/** @var \onOffice\WPlugin\DataView\DataDetailView */
	private $_pDataDetailView = null;


	/**
	 *
	 * @return \onOffice\WPlugin\Model\FormModel
	 *
	 */

	public function generate()
	{
		$this->_pInputModelDetailViewFactory = new InputModelOptionFactoryDetailView($this->getPageSlug());
		$this->_pDataDetailView = DataDetailViewHandler::getDetailView();

		$pFormModel = new Model\FormModel();
		$pFormModel->setLabel(__('Detail View', 'onoffice'));
		$pFormModel->setGroupSlug('onoffice-detailview-settings-main');
		$pFormModel->setPageSlug($this->getPageSlug());

		return $pFormModel;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelPictureTypes()
	{
		$allPictureTypes = \onOffice\WPlugin\ImageType::getAllImageTypes();

		$pInputModelPictureTypes = $this->_pInputModelDetailViewFactory->create
			(InputModelOptionFactoryDetailView::INPUT_PICTURE_TYPE, null, true);
		$pInputModelPictureTypes->setHtmlType(Model\InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable($allPictureTypes);
		$pictureTypes = $this->_pDataDetailView->getPictureTypes();

		if (null == $pictureTypes)
		{
			$pictureTypes = array();
		}

		$pInputModelPictureTypes->setValue($pictureTypes);

		return $pInputModelPictureTypes;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelExpose()
	{
		$labelExpose = __('PDF-Expose', 'onoffice');

		$pInputModelExpose = $this->_pInputModelDetailViewFactory->create
			(InputModelOptionFactoryDetailView::INPUT_EXPOSE, $labelExpose);
		$pInputModelExpose->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);
		$exposes = array('' => '') + $this->readExposes();
		$pInputModelExpose->setValuesAvailable($exposes);
		$pInputModelExpose->setValue($this->_pDataDetailView->getExpose());

		return $pInputModelExpose;
	}


	/**
	 *
	 * @param string $category
	 * @param array $fieldNames
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelFieldsConfigByCategory($category, $fieldNames)
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, $category, true);

		$pInputModelFieldsConfig->setHtmlType(Model\InputModelBase::HTML_TYPE_CHECKBOX_BUTTON);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$pInputModelFieldsConfig->setId($category);
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
	 * @return Model\InputModelDB
	 *
	 */

	public function createSortableFieldList($module, $htmlType)
	{
		$pInputModelFieldsConfig = $this->_pInputModelDetailViewFactory->create(
			InputModelOptionFactoryDetailView::INPUT_FIELD_CONFIG, null, true);

		$pInputModelFieldsConfig->setHtmlType($htmlType);

		$pFieldnames = new \onOffice\WPlugin\Fieldnames();
		$pFieldnames->loadLanguage();

		$fieldNames = $pFieldnames->getFieldList($module, true, true);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);

		$fields = $this->_pDataDetailView->getFields();

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

	public function createInputModelAddressFieldsConfig()
	{
		$pInputModelFieldsConfig = $this->_pInputModelDetailViewFactory->create(
			InputModelOptionFactoryDetailView::INPUT_FIELD_CONTACTDATA, null, true);

		$fieldNames = $this->readFieldnames(onOfficeSDK::MODULE_ADDRESS);
		$pInputModelFieldsConfig->setHtmlType(Model\InputModelOption::HTML_TYPE_COMPLEX_SORTABLE_CHECKBOX_LIST);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
		$fields = $this->_pDataDetailView->getAddressFields();
		$pInputModelFieldsConfig->setValue($fields);

		return $pInputModelFieldsConfig;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelTemplate()
	{
		$labelTemplate = __('Template', 'onoffice');

		$pInputModelTemplate = $this->_pInputModelDetailViewFactory->create
			(InputModelOptionFactoryDetailView::INPUT_TEMPLATE, $labelTemplate);
		$pInputModelTemplate->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);

		$pInputModelTemplate->setValuesAvailable($this->readTemplatePaths('estate'));
		$pInputModelTemplate->setValue($this->_pDataDetailView->getTemplate());

		return $pInputModelTemplate;
	}
}
