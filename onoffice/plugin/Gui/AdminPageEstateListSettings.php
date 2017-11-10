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
use onOffice\WPlugin\Renderer\InputModelRenderer;
use onOffice\WPlugin\Form\FormModelBuilderEstateListSettings;
use onOffice\WPlugin\Record\RecordManagerUpdateListView;
use onOffice\WPlugin\Record\RecordManagerInsertListView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateListSettings
	extends AdminPageAjax
{
	/** caution: also needs to be set in Javascript */
	const POST_RECORD_ID = 'record_id';

	/** */
	const VIEW_SAVE_SUCCESSFUL_MESSAGE = 'view_save_success_message';

	/** */
	const VIEW_SAVE_FAIL_MESSAGE = 'view_save_fail_message';

	/** */
	const FORM_VIEW_NAME = 'viewname';

	/** */
	const FORM_VIEW_RECORDS_FILTER = 'viewrecordsfilter';

	/** */
	const FORM_VIEW_LAYOUT_DESIGN = 'viewlayoutdesign';

	/** */
	const FORM_VIEW_PICTURE_TYPES = 'viewpicturetypes';

	/** */
	const FORM_VIEW_DOCUMENT_TYPES = 'viewdocumenttypes';

	/** */
	const FORM_VIEW_FIELDS_CONFIG = 'viewfieldsconfig';

	/** @var int */
	private $_listViewId = null;


	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$this->_listViewId = filter_input(INPUT_GET, 'listViewId');
	}

	/**
	 *
	 * @return bool
	 *
	 */

	protected function buildForms()
	{

		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
		$pFormModelBuilder = new FormModelBuilderEstateListSettings($this->getPageSlug());
		$pFormModel = $pFormModelBuilder->generate($this->_listViewId);
		$this->addFormModel($pFormModel);

		$pInputModelName = $pFormModelBuilder->createInputModelName();
		$pFormModelName = new Model\FormModel();
		$pFormModelName->setPageSlug($this->getPageSlug());
		$pFormModelName->setGroupSlug(self::FORM_VIEW_NAME);
		$pFormModelName->setLabel(__('choose name', 'onoffice'));
		$pFormModelName->addInputModel($pInputModelName);
		$this->addFormModel($pFormModelName);

		$pInputModelFilter = $pFormModelBuilder->createInputModelFilter();
		$pInputModelRecordsPerPage = $pFormModelBuilder->createInputModelRecordsPerPage();
		$pInputModelSortBy = $pFormModelBuilder->createInputModelSortBy();
		$pInputModelSortOrder = $pFormModelBuilder->createInputModelSortOrder();
		$pInputModelListType = $pFormModelBuilder->createInputModelListType();
		$pInputModelShowStatus = $pFormModelBuilder->createInputModelShowStatus();
		$pFormModelRecordsFilter = new Model\FormModel();
		$pFormModelRecordsFilter->setPageSlug($this->getPageSlug());
		$pFormModelRecordsFilter->setGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$pFormModelRecordsFilter->setLabel(__('Filters & Records', 'onoffice'));
		$pFormModelRecordsFilter->addInputModel($pInputModelFilter);
		$pFormModelRecordsFilter->addInputModel($pInputModelRecordsPerPage);
		$pFormModelRecordsFilter->addInputModel($pInputModelSortBy);
		$pFormModelRecordsFilter->addInputModel($pInputModelSortOrder);
		$pFormModelRecordsFilter->addInputModel($pInputModelListType);
		$pFormModelRecordsFilter->addInputModel($pInputModelShowStatus);
		$this->addFormModel($pFormModelRecordsFilter);

		$pInputModelTemplate = $pFormModelBuilder->createInputModelTemplate();
		$pFormModelLayoutDesign = new Model\FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$this->addFormModel($pFormModelLayoutDesign);

		$pInputModelPictureTypes = $pFormModelBuilder->createInputModelPictureTypes();
		$pFormModelPictureTypes = new Model\FormModel();
		$pFormModelPictureTypes->setPageSlug($this->getPageSlug());
		$pFormModelPictureTypes->setGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$pFormModelPictureTypes->setLabel(__('Photo Types', 'onoffice'));
		$pFormModelPictureTypes->addInputModel($pInputModelPictureTypes);
		$this->addFormModel($pFormModelPictureTypes);

		$pInputModelDocumentTypes = $pFormModelBuilder->createInputModelExpose();
		$pFormModelDocumentTypes = new Model\FormModel();
		$pFormModelDocumentTypes->setPageSlug($this->getPageSlug());
		$pFormModelDocumentTypes->setGroupSlug(self::FORM_VIEW_DOCUMENT_TYPES);
		$pFormModelDocumentTypes->setLabel(__('Downloadable Documents', 'onoffice'));
		$pFormModelDocumentTypes->addInputModel($pInputModelDocumentTypes);
		$this->addFormModel($pFormModelDocumentTypes);

		$pInputModelFieldsConfig = $pFormModelBuilder->createInputModelFieldsConfig();
		$pFormModelFieldsConfig = new Model\FormModel();
		$pFormModelFieldsConfig->setPageSlug($this->getPageSlug());
		$pFormModelFieldsConfig->setGroupSlug(self::FORM_VIEW_FIELDS_CONFIG);
		$pFormModelFieldsConfig->setLabel(__('Fields Configuration', 'onoffice'));
		$pFormModelFieldsConfig->addInputModel($pInputModelFieldsConfig);
		$this->addFormModel($pFormModelFieldsConfig);
	}


	/**
	 *
	 */

	private function generateMetaBoxes()
	{
		$pFormRecordsFilter = $this->getFormModelByGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$this->createMetaBoxByForm($pFormRecordsFilter, 'normal');

		$pFormPictureTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$this->createMetaBoxByForm($pFormPictureTypes, 'side');

		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'side');

		$pFormDocumentTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_DOCUMENT_TYPES);
		$this->createMetaBoxByForm($pFormDocumentTypes, 'normal');

		$pFormFieldsConfig = $this->getFormModelByGroupSlug(self::FORM_VIEW_FIELDS_CONFIG);
		$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
	}

	/**
	 *
	 */

	public function renderContent()
	{
		do_action('add_meta_boxes', get_current_screen()->id, null);
		$this->generateMetaBoxes();

		$pFormViewName = $this->getFormModelByGroupSlug(self::FORM_VIEW_NAME);
		$pInputRendererViewName = new InputModelRenderer($pFormViewName);

		wp_nonce_field( $this->getPageSlug() );

		$this->generatePageMainTitle(__('Edit List View', 'onoffice'));
		echo '<div id="onoffice-ajax">';
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		echo '<div id="poststuff">';
		echo '<div id="post-body" class="metabox-holder columns-'.(1 == get_current_screen()->get_columns() ? '1' : '2').'">';
		echo '<div id="post-body-content">';
		$pInputRendererViewName->buildForAjax();
		echo '</div>';
		echo '<div class="postbox-container" id="postbox-container-1">';
		do_meta_boxes(get_current_screen()->id, 'normal', null );
		echo '</div>';
		echo '<div class="postbox-container" id="postbox-container-2">';
		do_meta_boxes(get_current_screen()->id, 'side', null );
		do_meta_boxes(get_current_screen()->id, 'advanced', null );
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div class="clear"></div>';

		do_settings_sections( $this->getPageSlug() );
		submit_button(null, 'primary', 'send_ajax');

		echo '<script>onOffice.ajaxSaver = new onOffice.ajaxSaver("onoffice-ajax");';
		echo 'onOffice.ajaxSaver.register();';
		echo '$(document).ready(function(){'
			.'postboxes.add_postbox_toggles(pagenow);'
			.'});';
		echo '</script>';
	}


	/**
	 *
	 */

	public function ajax_action()
	{
		$this->buildForms();
		$action = filter_input(INPUT_POST, 'action');
		$nonce = filter_input(INPUT_POST, 'nonce');
		$recordId = filter_input(INPUT_POST, self::POST_RECORD_ID);
		$result = false;

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

		if ($recordId != null)
		{
			$pUpdate = new RecordManagerUpdateListView($recordId);
			$result = $pUpdate->updateByRow($row);
		}
		else
		{
			$pInsert = new RecordManagerInsertListView();
			$recordId = $pInsert->insertByRow($row);

			if ($recordId != null)
			{
				$result = true;
			}
		}

		$resultObject = new \stdClass();
		$resultObject->result = $result;
		$resultObject->record_id = $recordId;

		echo json_encode($resultObject);

		wp_die();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEnqueueData()
	{
		return array(
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The view has been saved.', 'onoffice'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the view.', 'onoffice'),
			self::ENQUEUE_DATA_MERGE => array(self::POST_RECORD_ID),
			self::POST_RECORD_ID => $this->_listViewId,
		);
	}


	/**
	 *
	 */

	public function handleAdminNotices()
	{
		add_action('admin_notices', array($this, 'addAdminNoticeWrapper'));
	}


	/**
	 *
	 * rest will be added via js
	 *
	 */

	public function addAdminNoticeWrapper()
	{
		echo '<div id="onoffice-notice-wrapper"></div>';
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		wp_register_script('admin-js', plugin_dir_url(ONOFFICE_PLUGIN_DIR.'/index.php').'/js/admin.js',
			array('jquery'), '', true);

		wp_enqueue_script('postbox');
		wp_enqueue_script('admin-js');
	}

	/** @return string */
	public function getListViewId()
		{ return $this->_listViewId; }
}
