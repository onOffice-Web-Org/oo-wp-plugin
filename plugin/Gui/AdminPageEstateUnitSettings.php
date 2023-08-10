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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Model;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateUnitListSettings;
use onOffice\WPlugin\Record\RecordManager;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;


/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateUnitSettings
	extends AdminPageEstateListSettingsBase
{
	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$this->setPageTitle(__('Edit Units View', 'onoffice-for-wp-websites'));
	}


	/**
	 *
	 */

	protected function buildForms()
	{
		$pFormModelBuilder = new FormModelBuilderDBEstateUnitListSettings();
		$pFormModel = $pFormModelBuilder->generate($this->getPageSlug(), $this->getListViewId());
		$this->addFormModel($pFormModel);

		$pInputModelName = $pFormModelBuilder->createInputModelName();
		$pFormModelName = new Model\FormModel();
		$pFormModelName->setPageSlug($this->getPageSlug());
		$pFormModelName->setGroupSlug(self::FORM_RECORD_NAME);
		$pFormModelName->setLabel(__('choose name', 'onoffice-for-wp-websites'));
		$pFormModelName->addInputModel($pInputModelName);
		$this->addFormModel($pFormModelName);

		if ($this->getListViewId() !== null) {
			$pInputModelEmbedCode = $pFormModelBuilder->createInputModelEmbedCode($this->getListViewId());
			$pFormModelName->addInputModel($pInputModelEmbedCode);
			$pInputModelButton = $pFormModelBuilder->createInputModelButton();
			$pFormModelName->addInputModel($pInputModelButton);
		}

		$pInputModelRecords = $pFormModelBuilder->createInputModelRecordsPerPage();
		$pFormModelRecords = new Model\FormModel();
		$pFormModelRecords->setPageSlug($this->getPageSlug());
		$pFormModelRecords->setGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$pFormModelRecords->setLabel(__('Records', 'onoffice-for-wp-websites'));
		$pFormModelRecords->addInputModel($pInputModelRecords);
		$this->addFormModel($pFormModelRecords);

		$pInputModelTemplate = $pFormModelBuilder->createInputModelTemplate('estate');
		$pFormModelLayoutDesign = new Model\FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice-for-wp-websites'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$this->addFormModel($pFormModelLayoutDesign);

		$pInputModelPictureTypes = $pFormModelBuilder->createInputModelPictureTypes();
		$pFormModelPictureTypes = new Model\FormModel();
		$pFormModelPictureTypes->setPageSlug($this->getPageSlug());
		$pFormModelPictureTypes->setGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$pFormModelPictureTypes->setLabel(__('Photo Types', 'onoffice-for-wp-websites'));
		$pFormModelPictureTypes->addInputModel($pInputModelPictureTypes);
		$this->addFormModel($pFormModelPictureTypes);

		$pInputModelDocumentTypes = $pFormModelBuilder->createInputModelExpose();
		$pFormModelDocumentTypes = new Model\FormModel();
		$pFormModelDocumentTypes->setPageSlug($this->getPageSlug());
		$pFormModelDocumentTypes->setGroupSlug(self::FORM_VIEW_DOCUMENT_TYPES);
		$pFormModelDocumentTypes->setLabel(__('Downloadable Documents', 'onoffice-for-wp-websites'));
		$pFormModelDocumentTypes->addInputModel($pInputModelDocumentTypes);
		$this->addFormModel($pFormModelDocumentTypes);

		$fieldNames = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ESTATE);

		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ESTATE, $pFormModelBuilder, $fieldNames);
		$this->addSortableFieldsList(array(onOfficeSDK::MODULE_ESTATE), $pFormModelBuilder);
	}


	/**
	 *
	 */

	protected function generateMetaBoxes()
	{
		$pFormPictureTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$this->createMetaBoxByForm($pFormPictureTypes, 'side');

		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'side');

		$pFormDocumentTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_DOCUMENT_TYPES);
		$this->createMetaBoxByForm($pFormDocumentTypes, 'normal');

		$pFormRecords = $this->getFormModelByGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$this->createMetaBoxByForm($pFormRecords, 'side');
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		$this->cleanPreviousBoxes();
		$module = onOfficeSDK::MODULE_ESTATE;
		$fieldNames = array_keys($this->readFieldnamesByContent($module));

		foreach ($fieldNames as $category) {
			$slug = $this->generateGroupSlugByModuleCategory($module, $category);
			$pFormFieldsConfig = $this->getFormModelByGroupSlug($slug);
			$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
		}
	}

	/**
	 *
	 * @param int $recordId
	 * @throws UnknownViewException
	 *
	 */

	protected function validate($recordId = null)
	{
		if ($recordId == null) {
			return;
		}

		$pRecordReadManager = new RecordManagerReadListViewEstate();
		$values = $pRecordReadManager->getRowById($recordId);
		$pFactory = new DataListViewFactory();
		$pDataListView = $pFactory->createListViewByRow($values);

		if ($pDataListView->getListType() !== 'units') {
			throw new UnknownViewException;
		}
	}


	/**
	 *
	 * @param array $row
	 * @return array
	 *
	 */

	protected function setFixedValues(array $row)
	{
		$rowCleanRecordsPerPage = $this->setRecordsPerPage($row, RecordManager::TABLENAME_LIST_VIEW);
		$rowCleanRecordsPerPage[RecordManager::TABLENAME_LIST_VIEW]['list_type'] = 'units';
		return $rowCleanRecordsPerPage;
	}

	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		parent::doExtraEnqueues();
		wp_enqueue_script('oo-copy-shortcode');
		wp_enqueue_script('onoffice-custom-form-label-js');
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		wp_register_script('onoffice-multiselect', plugins_url('/js/onoffice-multiselect.js', $pluginPath));
		wp_register_style('onoffice-multiselect', plugins_url('/css/onoffice-multiselect.css', $pluginPath));
		wp_enqueue_script('onoffice-multiselect');
		wp_enqueue_style('onoffice-multiselect');
	}
}
