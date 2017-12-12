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
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactory;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModelBuilderEstateListSettings
	extends FormModelBuilderEstate
{
	/**
	 *
	 * @param int $listViewId
	 * @return \onOffice\WPlugin\Model\FormModel
	 *
	 */

	public function generate($listViewId = null)
	{
		if ($listViewId !== null)
		{
			$pRecordReadManager = new \onOffice\WPlugin\Record\RecordManagerReadListView();
			$values = $pRecordReadManager->getRowById($listViewId);
			$this->setValues($values);
		}

		$pFormModel = new Model\FormModel();
		$pFormModel->setLabel(__('List View', 'onoffice'));
		$pFormModel->setGroupSlug('onoffice-listview-settings-main');
		$pFormModel->setPageSlug($this->getPageSlug());

		return $pFormModel;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelFilter()
	{
		$labelFiltername = __('Filter', 'onoffice');
		$pInputModelFiltername = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_FILTERID, $labelFiltername);
		$pInputModelFiltername->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);

		$availableFilters = array(0 => '') + $this->readFilters();

		$pInputModelFiltername->setValuesAvailable($availableFilters);
		$filteridSelected = $this->getValue($pInputModelFiltername->getField());
		$pInputModelFiltername->setValue($filteridSelected);

		return $pInputModelFiltername;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelFieldsConfig()
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$fieldNames = $this->readFieldnames(\onOffice\SDK\onOfficeSDK::MODULE_ESTATE);
		$pInputModelFieldsConfig->setHtmlType(Model\InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_CHECKBOX_LIST);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);
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
		$pInputModelFieldsConfig->setLabel($category);
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

	public function createSortableFieldList()
	{
		$pInputModelFieldsConfig = $this->getInputModelDBFactory()->create(
			InputModelDBFactory::INPUT_FIELD_CONFIG, null, true);

		$pInputModelFieldsConfig->setHtmlType(Model\InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST);

		$pFieldnames = new \onOffice\WPlugin\Fieldnames();
		$pFieldnames->loadLanguage();

		$fieldNames = $pFieldnames->getFieldList(\onOffice\SDK\onOfficeSDK::MODULE_ESTATE, true, true);
		$pInputModelFieldsConfig->setValuesAvailable($fieldNames);

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

	public function createInputModelListType()
	{
		$labelListType = __('Type of List', 'onoffice');
		$pInputModelListType = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_LIST_TYPE, $labelListType);
		$pInputModelListType->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);
		$pInputModelListType->setValue($this->getValue($pInputModelListType->getField()));
		$pInputModelListType->setValuesAvailable(self::getListViewLabels());

		return $pInputModelListType;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelSortBy()
	{
		$labelSortBy = __('Sort by', 'onoffice');

		$pInputModelSortBy = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SORTBY, $labelSortBy);
		$pInputModelSortBy->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);

		$fieldnames = $this->readFieldnames(\onOffice\SDK\onOfficeSDK::MODULE_ESTATE);
		natcasesort($fieldnames);
		$pInputModelSortBy->setValuesAvailable($fieldnames);
		$pInputModelSortBy->setValue($this->getValue($pInputModelSortBy->getField()));

		return $pInputModelSortBy;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelRecordsPerPage()
	{
		$labelRecordsPerPage = __('Records per Page', 'onoffice');
		$pInputModelRecordsPerPage = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_RECORDS_PER_PAGE, $labelRecordsPerPage);
		$pInputModelRecordsPerPage->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);
		$pInputModelRecordsPerPage->setValuesAvailable(array('5' => '5', '10' => '10', '15' => '15'));
		$pInputModelRecordsPerPage->setValue($this->getValue('recordsPerPage'));

		return $pInputModelRecordsPerPage;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelSortOrder()
	{
		$labelSortOrder = __('Sort order', 'onoffice');
		$pInputModelSortOrder = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SORTORDER, $labelSortOrder);
		$pInputModelSortOrder->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);
		$pInputModelSortOrder->setValuesAvailable(array(
			'ASC' => __('Ascending', 'onoffice'),
			'DESC' => __('Descending', 'onoffice'),
		));
		$pInputModelSortOrder->setValue($this->getValue($pInputModelSortOrder->getField()));

		return $pInputModelSortOrder;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelShowStatus()
	{
		$labelShowStatus = __('Show Estate Status', 'onoffice');

		$pInputModelShowStatus = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_SHOW_STATUS, $labelShowStatus);
		$pInputModelShowStatus->setHtmlType(Model\InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelShowStatus->setValue($this->getValue('show_status'));
		$pInputModelShowStatus->setValuesAvailable(1);

		return $pInputModelShowStatus;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelName()
	{
		$labelName = __('View Name', 'onoffice');

		$pInputModelName = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_LISTNAME, null);
		$pInputModelName->setPlaceholder($labelName);
		$pInputModelName->setHtmlType(Model\InputModelOption::HTML_TYPE_TEXT);
		$pInputModelName->setValue($this->getValue($pInputModelName->getField()));

		return $pInputModelName;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelPictureTypes()
	{
		$allPictureTypes = \onOffice\WPlugin\ImageType::getAllImageTypes();

		$pInputModelPictureTypes = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_PICTURE_TYPE, null, true);
		$pInputModelPictureTypes->setHtmlType(Model\InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable($allPictureTypes);
		$pictureTypes = $this->getValue(DataListView::PICTURES);

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

		$pInputModelExpose = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_EXPOSE, $labelExpose);
		$pInputModelExpose->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);
		$exposes = array('' => '') + $this->readExposes();
		$pInputModelExpose->setValuesAvailable($exposes);
		$pInputModelExpose->setValue($this->getValue($pInputModelExpose->getField()));

		return $pInputModelExpose;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	public function createInputModelTemplate()
	{
		$labelTemplate = __('Template', 'onoffice');
		$selectedTemplate = $this->getValue('template');

		$pInputModelTemplate = $this->getInputModelDBFactory()->create
			(InputModelDBFactory::INPUT_TEMPLATE, $labelTemplate);
		$pInputModelTemplate->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);

		$pInputModelTemplate->setValuesAvailable($this->readTemplatePaths('estate'));
		$pInputModelTemplate->setValue($selectedTemplate);

		return $pInputModelTemplate;
	}


	/**
	 *
	 * @return array enum values from DB
	 *
	 */

	static public function getListViewLabels()
	{
		return array(
			DataListView::LISTVIEW_TYPE_DEFAULT => __('Default', 'onoffice'),
			DataListView::LISTVIEW_TYPE_REFERENCE => __('Reference Estates', 'onoffice'),
			DataListView::LISTVIEW_TYPE_FAVORITES => __('Favorites List', 'onoffice'),
		);
	}
}
