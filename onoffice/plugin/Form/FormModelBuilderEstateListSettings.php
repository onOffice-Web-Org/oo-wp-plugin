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

namespace onOffice\WPlugin\Form;

use onOffice\WPlugin\Model;
use onOffice\WPlugin\Language;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\FilterCall;
use onOffice\WPlugin\TemplateCall;
use onOffice\WPlugin\Model\InputModel\ListView\InputModelDBFactory;
use onOffice\WPlugin\DataView\DataListView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class FormModelBuilderEstateListSettings
	extends FormModelBuilder
{
	/** @var array */
	private $_dbValues = array();

	/** @var InputModelDBFactory */
	private $_pInputModelDBFactory = null;


	/**
	 *
	 * @param int $listViewId
	 * @return \onOffice\WPlugin\Model\FormModel
	 *
	 */

	public function generate($listViewId = null)
	{
		$this->_pInputModelDBFactory = new InputModelDBFactory();

		if ($listViewId !== null)
		{
			$pRecordReadManager = new \onOffice\WPlugin\Record\RecordManagerReadListView();
			$this->_dbValues = $pRecordReadManager->getRowById($listViewId);
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
		$pInputModelFiltername = $this->_pInputModelDBFactory->create
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

	public function createInputModelListType()
	{
		$labelListType = __('Type of List', 'onoffice');
		$pInputModelListType = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_LIST_TYPE, $labelListType);
		$pInputModelListType->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);
		$pInputModelListType->setValue($this->getValue($pInputModelListType->getField()));

		// enum values from DB
		$pInputModelListType->setValuesAvailable(array(
			'default' => __('Default', 'onoffice'),
			'reference' => __('Reference Estates', 'onoffice'),
			'favorites' => __('Favorites List', 'onoffice'),
		));

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

		$pInputModelSortBy = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_SORTBY, $labelSortBy);
		$pInputModelSortBy->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);

		$fieldnames = $this->readFieldnames();
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
		$pInputModelRecordsPerPage = $this->_pInputModelDBFactory->create
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
		$pInputModelSortOrder = $this->_pInputModelDBFactory->create
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

		$pInputModelShowStatus = $this->_pInputModelDBFactory->create
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

		$pInputModelName = $this->_pInputModelDBFactory->create
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

		$pInputModelPictureTypes = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_PICTURE_TYPE, null, true);
		$pInputModelPictureTypes->setHtmlType(Model\InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable($allPictureTypes);
		$pictureTypes = $this->getValue(DataListView::PICTURES);
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

		$pInputModelExpose = $this->_pInputModelDBFactory->create
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

		$pInputModelTemplate = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_TEMPLATE, $labelTemplate);
		$pInputModelTemplate->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);

		$pInputModelTemplate->setValuesAvailable($this->readTemplates());
		$pInputModelTemplate->setValue($selectedTemplate);

		return $pInputModelTemplate;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function readTemplates()
	{
		$templateGlobFiles = glob(plugin_dir_path(ONOFFICE_PLUGIN_DIR).'onoffice/templates.dist/estate/*.php');
		$templateLocalFiles = glob(plugin_dir_path(ONOFFICE_PLUGIN_DIR).'onoffice-personalized/templates/estate/*.php');
		$templatesAll = array_merge($templateGlobFiles, $templateLocalFiles);
		$templates = array();

		foreach ($templatesAll as $value)
		{
			$value = str_replace(plugin_dir_path(ONOFFICE_PLUGIN_DIR), '', $value);
			$templates[$value] = $value;
		}

		return $templates;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function readFilters()
	{
		$pFilterCall = new FilterCall(\onOffice\SDK\onOfficeSDK::MODULE_ESTATE);
		return $pFilterCall->getFilters();
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function readFieldnames()
	{
		$language = Language::getDefault();
		$pFieldnames = \onOffice\WPlugin\Fieldnames::getInstance();
		$pFieldnames->loadLanguageIfNotCached($language);

		$fieldnames = $pFieldnames->getFieldList(onOfficeSDK::MODULE_ESTATE, $language);
		$result = array();

		foreach ($fieldnames as $key => $properties)
		{
			$result[$key] = $properties['label'];
		}

		return $result;
	}


	/**
	 *
	 * @param string $key
	 * @return mixed
	 *
	 */

	private function getValue($key)
	{
		if (array_key_exists($key, $this->_dbValues))
		{
			return $this->_dbValues[$key];
		}

		return null;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function readExposes()
	{
		$pTemplateCall = new \onOffice\WPlugin\TemplateCall(TemplateCall::TEMPLATE_TYPE_EXPOSE);
		return $pTemplateCall->getTemplates();
	}
}
