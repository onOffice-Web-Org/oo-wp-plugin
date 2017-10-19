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

namespace onOffice\WPlugin\Gui;

use onOffice\WPlugin\Model;
use onOffice\WPlugin\FilterCall;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */
class AdminPageEstateListSettings
	extends AdminPage
{
	/** @var int */
	private $_listViewId = null;

	/** @var array */
	private $_dbValues = null;

	/**
	 *
	 * @param string $pageSlug
	 * @param string $listviewId
	 *
	 */

	public function __construct($pageSlug, $listviewId)
	{
		$this->_listViewId = $listviewId;

		$pRecordReadManager = new \onOffice\WPlugin\Record\RecordManagerReadListView();
		$this->_dbValues = $pRecordReadManager->getRowById($this->_listViewId);

		$labelExpose = __('pdf-expose', 'onoffice');
		$labelFieldConfiguration = __('field configuration', 'onoffice');

		$pInputModelName = $this->createInputModelName();

		$pInputModelFiltername =  $this->createInputModelFilter();

		$pInputModelSortBy = $this->createInputModelSortBy();

		$pInputModelSortOrder = $this->createInputModelSortOrder();

		$pInputModelRecordsPerPage = $this->createInputModelRecordsPerPage();

		$pInputModelShowStatus = $this->createInputModelShowStatus();

		$pInputModelIsReference = $this->createInputModelIsReference();

		$pInputModelTemplate = $this->createInputModelTemplate();

		$pInputModelExpose = new Model\InputModel(
				'onoffice-estateListSettings', 'expose', $labelExpose, 'string');
		$pInputModelExpose->setHtmlType(Model\InputModel::HTML_TYPE_SELECT);

		$pInputModelPictureTypes = $this->createInputModelPictureTypes();

		$pFormModel = new Model\FormModel();
		$pFormModel->setLabel(__('list view', 'onoffice'));
		$pFormModel->addInputModel($pInputModelName);
		//$pFormModel->addInputModel($pInputModelFiltername);
		$pFormModel->addInputModel($pInputModelSortBy);
		$pFormModel->addInputModel($pInputModelSortOrder);
		$pFormModel->addInputModel($pInputModelRecordsPerPage);
		$pFormModel->addInputModel($pInputModelShowStatus);
		$pFormModel->addInputModel($pInputModelIsReference);
		$pFormModel->addInputModel($pInputModelTemplate);
		//$pFormModel->addInputModel($pInputModelExpose);
		$pFormModel->addInputModel($pInputModelPictureTypes);
		$pFormModel->setGroupSlug('onoffice-listview-settings');
		$pFormModel->setPageSlug($pageSlug);

		$this->readFieldnames();

		$this->addFormModel($pFormModel);

		parent::__construct($pageSlug);
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$this->generatePageMainTitle(__('listview settings', 'onoffice'));

		echo '<form method="post" action="">';

		foreach ($this->getFormModels() as $pFormModel)
		{
			$pFormBuilder = new FormBuilder($pFormModel);
			$pFormBuilder->buildForm();
		}

		do_settings_sections( $this->getPageSlug() );
		echo '</form>';
	}


	/** @return string */
	public function getListViewId()
		{ return $this->_listViewId; }



	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModel
	 *
	 */

	private function createInputModelFilter()
	{
		$labelFiltername = __('filtername', 'onoffice');
		$pInputModelFiltername =  new Model\InputModel(
				'onoffice-estateListSettings', 'filtername', $labelFiltername, 'string');
		$pInputModelFiltername->setHtmlType(Model\InputModel::HTML_TYPE_SELECT);

		$availableFilters = $this->readFilters();

		$pInputModelFiltername->setValue($availableFilters);
		$pInputModelFiltername->setDefault($this->_dbValues['firlerid']);

		return $pInputModelFiltername;
	}


	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModel
	 *
	 */

	private function createInputModelIsReference()
	{
		$labelIsReference = __('detail view for reference estates', 'onoffice');
		$pInputModelIsReference = new Model\InputModel(
				'onoffice-estateListSettings', 'is_reference', $labelIsReference, 'boolean');
		$pInputModelIsReference->setHtmlType(Model\InputModel::HTML_TYPE_CHECKBOX);
		$pInputModelIsReference->setDefault(array($this->_dbValues['is_reference']));
		$pInputModelIsReference->setValue($this->_dbValues['is_reference']);

		return $pInputModelIsReference;
	}

	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModel
	 *
	 */

	private function createInputModelSortBy()
	{
		$labelSortBy = __('sort by', 'onoffice');

		$pInputModelSortBy = new Model\InputModel(
				'onoffice-estateListSettings', 'sortby', $labelSortBy, 'string');
		$pInputModelSortBy->setHtmlType(Model\InputModel::HTML_TYPE_SELECT);
		$pInputModelSortBy->setValue(array());

		return $pInputModelSortBy;
	}

	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModel
	 *
	 */

	private function createInputModelRecordsPerPage()
	{
		$labelRecordsPerPage = __('records per page', 'onoffice');
		$pInputModelRecordsPerPage = new Model\InputModel(
				'onoffice-estateListSettings', 'recordsPerPage', $labelRecordsPerPage, 'string');
		$pInputModelRecordsPerPage->setHtmlType(Model\InputModel::HTML_TYPE_SELECT);
		$pInputModelRecordsPerPage->setValue(array('5' => '5', '10' => '10', '15' => '15'));
		$pInputModelRecordsPerPage->setDefault($this->_dbValues['recordsPerPage']);

		return $pInputModelRecordsPerPage;
	}


	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModel
	 *
	 */

	private function createInputModelSortOrder()
	{
		$labelSortOrder = __('sort order', 'onoffice');
		$pInputModelSortOrder = new Model\InputModel(
				'onoffice-estateListSettings', 'sortorder', $labelSortOrder, 'string');
		$pInputModelSortOrder->setHtmlType(Model\InputModel::HTML_TYPE_SELECT);
		$pInputModelSortOrder->setValue(array('asc' => __('ascending', 'onoffice'), 'desc' => __('descending', 'onoffice')));
		$pInputModelSortOrder->setDefault($this->_dbValues['sortorder']);

		return $pInputModelSortOrder;
	}


	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModel
	 *
	 */

	private function createInputModelShowStatus()
	{
		$labelShowStatus = __('show estate status', 'onoffice');

		$pInputModelShowStatus = new Model\InputModel(
				'onoffice-estateListSettings', 'show_status', $labelShowStatus, 'boolean');
		$pInputModelShowStatus->setHtmlType(Model\InputModel::HTML_TYPE_CHECKBOX);
		$pInputModelShowStatus->setValue($this->_dbValues['show_status']);
		$pInputModelShowStatus->setDefault(array($this->_dbValues['show_status']));

		return $pInputModelShowStatus;
	}

	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModel
	 *
	 */
	private function createInputModelName()
	{
		$labelName = __('view name', 'onoffice');

		$pInputModelName = new Model\InputModel(
				'onoffice-estateListSettings', 'name', $labelName, 'string');
		$pInputModelName->setHtmlType(Model\InputModel::HTML_TYPE_TEXT);
		$pInputModelName->setValue($this->_dbValues['name']);

		return $pInputModelName;
	}


	/**
	 *
	 * @return Model\InputModel
	 *
	 */
	private function createInputModelPictureTypes()
	{
		$pictureTypes = $this->_dbValues[\onOffice\WPlugin\Record\RecordManagerReadListView::PICTURES];

		$allPictureTypes = \onOffice\WPlugin\ImageType::getAllImageTypes();
		$labelPictureTypes = __('picture types', 'onoffice');

		$pInputModelPictureTypes = new Model\InputModel(
				'onoffice-estateListSettings', 'pictureTypes[]', $labelPictureTypes, 'string');
		$pInputModelPictureTypes->setHtmlType(Model\InputModel::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValue($allPictureTypes);
		$pInputModelPictureTypes->setDefault($pictureTypes);

		return $pInputModelPictureTypes;
	}



	/**
	 *
	 * @return \onOffice\WPlugin\Model\InputModel
	 *
	 */

	private function createInputModelTemplate()
	{
		$labelTemplate = __('template', 'onoffice');
		$selectedTemplate = $this->_dbValues['template'];

		$pInputModelTemplate = new Model\InputModel(
				'onoffice-estateListSettings', 'onOfficeTemplate', $labelTemplate, 'string');
		$pInputModelTemplate->setHtmlType(Model\InputModel::HTML_TYPE_SELECT);

		$pInputModelTemplate->setValue($this->readTemplates());
		$pInputModelTemplate->setDefault($selectedTemplate);

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



	private function readFieldnames()
	{
		$language = \onOffice\WPlugin\EstateList::getLanguage();
		$pFieldnames = \onOffice\WPlugin\Fieldnames::getInstance();
		$pFieldnames->loadLanguage($language);

		$fieldnames = $pFieldnames->getFieldList();

		return $fieldnames;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function readFilters()
	{
		$pFilterCall = new \onOffice\WPlugin\FilterCall('objekte');
		return $pFilterCall->getFilters();
	}
}
