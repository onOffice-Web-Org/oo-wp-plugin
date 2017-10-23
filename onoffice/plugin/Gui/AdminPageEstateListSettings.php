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
use onOffice\WPlugin\Model\InputModel\ListView\InputModelDBFactory;
use onOffice\WPlugin\Record\RecordManagerReadListView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateListSettings
	extends AdminPageAjax
{
	/** @var int */
	private $_listViewId = null;

	/** @var array */
	private $_dbValues = array();

	/** @var InputModelDBFactory */
	private $_pInputModelDBFactory = null;


	/**
	 *
	 * @return bool
	 *
	 */

	public function buildForms()
	{
		if (!is_admin()) {
			return false;
		}

		$listViewId = filter_input(INPUT_GET, 'listViewId');
		$this->_pInputModelDBFactory = new InputModelDBFactory();

		if ($listViewId !== null)
		{
			$this->_listViewId = $listViewId;

			$pRecordReadManager = new \onOffice\WPlugin\Record\RecordManagerReadListView();
			$this->_dbValues = $pRecordReadManager->getRowById($this->_listViewId);
		}

		$labelFieldConfiguration = __('field configuration', 'onoffice');
		$pInputModelName = $this->createInputModelName();
		$pInputModelFiltername =  $this->createInputModelFilter();
		$pInputModelSortBy = $this->createInputModelSortBy();
		$pInputModelSortOrder = $this->createInputModelSortOrder();
		$pInputModelRecordsPerPage = $this->createInputModelRecordsPerPage();
		$pInputModelShowStatus = $this->createInputModelShowStatus();
		$pInputModelIsReference = $this->createInputModelIsReference();
		$pInputModelTemplate = $this->createInputModelTemplate();
		$pInputModelExpose = $this->createInputModelExpose();
		$pInputModelPictureTypes = $this->createInputModelPictureTypes();

		$pFormModel = new Model\FormModel();
		$pFormModel->setLabel(__('list view', 'onoffice'));
		$pFormModel->addInputModel($pInputModelName);
		$pFormModel->addInputModel($pInputModelFiltername);
		$pFormModel->addInputModel($pInputModelSortBy);
		$pFormModel->addInputModel($pInputModelSortOrder);
		$pFormModel->addInputModel($pInputModelRecordsPerPage);
		$pFormModel->addInputModel($pInputModelShowStatus);
		$pFormModel->addInputModel($pInputModelIsReference);
		$pFormModel->addInputModel($pInputModelTemplate);
		$pFormModel->addInputModel($pInputModelExpose);
		$pFormModel->addInputModel($pInputModelPictureTypes);
		$pFormModel->setGroupSlug('onoffice-listview-settings');
		$pFormModel->setPageSlug($this->getPageSlug());

		$this->readFieldnames();

		$this->addFormModel($pFormModel);
	}


	/**
	 *
	 */

	public function renderContent()
	{
		$this->generatePageMainTitle(__('Edit list view', 'onoffice'));

		echo '<div id="onoffice-ajax">';

		foreach ($this->getFormModels() as $pFormModel)
		{
			$pFormBuilder = new FormBuilder($pFormModel);
			$pFormBuilder->buildForm();
		}

		do_settings_sections( $this->getPageSlug() );
		submit_button(null, 'primary', 'send_ajax');

		echo '</div>';
		echo '<script>onOffice.ajaxSaver = new onOffice.ajaxSaver("onoffice-ajax");';
		echo 'onOffice.ajaxSaver.register();';
		echo '</script>';
	}


	/** @return string */
	public function getListViewId()
		{ return $this->_listViewId; }



	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	private function createInputModelFilter()
	{
		$labelFiltername = __('filter name', 'onoffice');
		$pInputModelFiltername = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_FILTERID, $labelFiltername);
		$pInputModelFiltername->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);

		$availableFilters = $this->readFilters();

		$pInputModelFiltername->setValuesAvailable($availableFilters);
		$pInputModelFiltername->setValue($this->getValue($pInputModelFiltername->getField()));

		return $pInputModelFiltername;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	private function createInputModelIsReference()
	{
		$labelIsReference = __('detail view for reference estates', 'onoffice');
		$pInputModelIsReference = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_IS_REFERENCE, $labelIsReference);
		$pInputModelIsReference->setHtmlType(Model\InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelIsReference->setValue(array($this->_dbValues['is_reference']));
		$pInputModelIsReference->setValuesAvailable(1);

		return $pInputModelIsReference;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	private function createInputModelSortBy()
	{
		$labelSortBy = __('sort by', 'onoffice');

		$pInputModelSortBy = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_SORTBY, $labelSortBy);
		$pInputModelSortBy->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);
		$pInputModelSortBy->setValuesAvailable(array());

		return $pInputModelSortBy;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	private function createInputModelRecordsPerPage()
	{
		$labelRecordsPerPage = __('records per page', 'onoffice');
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

	private function createInputModelSortOrder()
	{
		$labelSortOrder = __('sort order', 'onoffice');
		$pInputModelSortOrder = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_SORTORDER, $labelSortOrder);
		$pInputModelSortOrder->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);
		$pInputModelSortOrder->setValuesAvailable(array('asc' => __('ascending', 'onoffice'), 'desc' => __('descending', 'onoffice')));
		$pInputModelSortOrder->setValue($this->getValue('sortorder'));

		return $pInputModelSortOrder;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	private function createInputModelShowStatus()
	{
		$labelShowStatus = __('show estate status', 'onoffice');

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

	private function createInputModelName()
	{
		$labelName = __('view name', 'onoffice');

		$pInputModelName = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_LISTNAME, $labelName);
		$pInputModelName->setHtmlType(Model\InputModelOption::HTML_TYPE_TEXT);
		$pInputModelName->setValue($this->getValue($pInputModelName->getField()));

		return $pInputModelName;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	private function createInputModelPictureTypes()
	{
		$allPictureTypes = \onOffice\WPlugin\ImageType::getAllImageTypes();
		$labelPictureTypes = __('picture types', 'onoffice');

		$pInputModelPictureTypes = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_PICTURE_TYPE, $labelPictureTypes, true);
		$pInputModelPictureTypes->setHtmlType(Model\InputModelOption::HTML_TYPE_CHECKBOX);
		$pInputModelPictureTypes->setValuesAvailable($allPictureTypes);
		$pictureTypes = $this->getValue(RecordManagerReadListView::PICTURES);
		$pInputModelPictureTypes->setValue($pictureTypes);

		return $pInputModelPictureTypes;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	private function createInputModelExpose()
	{
		$labelExpose = __('PDF-Expose', 'onoffice');

		$pInputModelPictureTypes = $this->_pInputModelDBFactory->create
			(InputModelDBFactory::INPUT_EXPOSE, $labelExpose);
		$pInputModelPictureTypes->setHtmlType(Model\InputModelOption::HTML_TYPE_SELECT);
		$pInputModelPictureTypes->setValuesAvailable(array());
		$pInputModelPictureTypes->setValue(0);

		return $pInputModelPictureTypes;
	}


	/**
	 *
	 * @return Model\InputModelDB
	 *
	 */

	private function createInputModelTemplate()
	{
		$labelTemplate = __('template', 'onoffice');
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
		$pFilterCall = new FilterCall(\onOffice\SDK\onOfficeSDK::MODULE_ESTATE);
		return $pFilterCall->getFilters();
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
	 */

	public function ajax_action()
	{
		$this->buildForms();
		$action = filter_input(INPUT_POST, 'action');
		$nonce = filter_input(INPUT_POST, 'nonce');

		if (!wp_verify_nonce($nonce, $action)) {
			wp_die();
		}

		$values = json_decode(filter_input(INPUT_POST, 'values'));

		$pInputModelDBAdapterRow = new Model\InputModelDBAdapterRow();

		foreach ($this->getFormModels() as $pFormModel) {
			foreach ($pFormModel->getInputModel() as $pInputModel) {
				if ($pInputModel instanceof Model\InputModelDB) {
					$identifier = $pInputModel->getIdentifier();
					$value = isset($values->$identifier) ? $values->$identifier : null;
					$pInputModel->setValue($value);
					$pInputModelDBAdapterRow->addInputModelDB($pInputModel);
				}
			}
			$row = $pInputModelDBAdapterRow->createUpdateValuesByTable();
		}

		var_dump($row);
		wp_die();
	}
}
