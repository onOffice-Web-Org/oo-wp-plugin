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
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Model\InputModelOptionAdapterArray;
use onOffice\WPlugin\Form\FormModelBuilderEstateDetailSettings;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateDetail
	extends AdminPageAjax
{
	/** */
	const VIEW_SAVE_SUCCESSFUL_MESSAGE = 'view_save_success_message';

	/** */
	const VIEW_SAVE_FAIL_MESSAGE = 'view_save_fail_message';

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


	/**
	 *
	 */

	public function renderContent()
	{
		$pDataView = DataDetailViewHandler::getDetailView();
		do_action('add_meta_boxes', get_current_screen()->id, null);
		$this->generateMetaBoxes();

		wp_nonce_field( $this->getPageSlug() );

		$this->generatePageMainTitle(__('Edit List View', 'onoffice'));
		echo '<div id="onoffice-ajax">';
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		echo '<div id="poststuff">';
		$pageId = $pDataView->getPageId();

		echo '<span class="viewusage">';
		if ($pageId != null) {
			esc_attr_e('Detail view in use on page ', 'onoffice');
			echo '<span class="italic">'.esc_html(get_the_title($pageId)).'</span>';
			edit_post_link(__('Edit'), ' ', '', $pageId);
		} else {
			esc_attr_e('Detail view is not in use yet. '
				.'Insert this code on a page to get the detail view there:', 'onoffice');
			echo ' <code>[oo_estate view="'.$pDataView->getName().'"]</code>';
		}
		echo '</span>';

		echo '<div id="post-body" class="metabox-holder columns-'.(1 == get_current_screen()->get_columns() ? '1' : '2').'">';
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
	 * @param string $subTitle
	 *
	 */

	public function generatePageMainTitle($subTitle)
	{
		echo '<h1 class="wp-heading-inline">'.esc_html__('onOffice', 'onoffice');
		echo ' › '.esc_html__($subTitle, 'onoffice');
		echo ' › '.esc_html__('Detail View', 'onoffice');
		echo '</h1>';
		echo '<hr class="wp-header-end">';
	}


	/**
	 *
	 */

	private function generateMetaBoxes()
	{
		$pFormPictureTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$this->createMetaBoxByForm($pFormPictureTypes, 'normal');

		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'normal');

		$pFormDocumentTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_DOCUMENT_TYPES);
		$this->createMetaBoxByForm($pFormDocumentTypes, 'normal');

		$pFormFieldsConfig = $this->getFormModelByGroupSlug(self::FORM_VIEW_FIELDS_CONFIG);
		$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
	}

	/**
	 *
	 */

	protected function buildForms()
	{
		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
		$pFormModelBuilder = new FormModelBuilderEstateDetailSettings($this->getPageSlug());
		$pFormModel = $pFormModelBuilder->generate();
		$this->addFormModel($pFormModel);

		$pInputModelTemplate = $pFormModelBuilder->createInputModelTemplate();
		$pFormModelLayoutDesign = new FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$this->addFormModel($pFormModelLayoutDesign);

		$pInputModelPictureTypes = $pFormModelBuilder->createInputModelPictureTypes();
		$pFormModelPictureTypes = new FormModel();
		$pFormModelPictureTypes->setPageSlug($this->getPageSlug());
		$pFormModelPictureTypes->setGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$pFormModelPictureTypes->setLabel(__('Photo Types', 'onoffice'));
		$pFormModelPictureTypes->addInputModel($pInputModelPictureTypes);
		$this->addFormModel($pFormModelPictureTypes);

		$pInputModelDocumentTypes = $pFormModelBuilder->createInputModelExpose();
		$pFormModelDocumentTypes = new FormModel();
		$pFormModelDocumentTypes->setPageSlug($this->getPageSlug());
		$pFormModelDocumentTypes->setGroupSlug(self::FORM_VIEW_DOCUMENT_TYPES);
		$pFormModelDocumentTypes->setLabel(__('Downloadable Documents', 'onoffice'));
		$pFormModelDocumentTypes->addInputModel($pInputModelDocumentTypes);
		$this->addFormModel($pFormModelDocumentTypes);

		$pInputModelFieldsConfig = $pFormModelBuilder->createInputModelFieldsConfig();
		$pFormModelFieldsConfig = new FormModel();
		$pFormModelFieldsConfig->setPageSlug($this->getPageSlug());
		$pFormModelFieldsConfig->setGroupSlug(self::FORM_VIEW_FIELDS_CONFIG);
		$pFormModelFieldsConfig->setLabel(__('Fields Configuration', 'onoffice'));
		$pFormModelFieldsConfig->addInputModel($pInputModelFieldsConfig);
		$this->addFormModel($pFormModelFieldsConfig);
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

		$pInputModelDBAdapterArray = new InputModelOptionAdapterArray();
		$valuesPrefixless = array();

		foreach ($this->getFormModels() as $pFormModel) {
			foreach ($pFormModel->getInputModel() as $pInputModel) {
				if ($pInputModel instanceof InputModelOption) {
					$identifier = $pInputModel->getIdentifier();

					$value = isset($values->$identifier) ? $values->$identifier : null;
					$pInputModel->setValue($value);
					$pInputModelDBAdapterArray->addInputModelOption($pInputModel);
				}
			}
			$valuesPrefixless += $pInputModelDBAdapterArray->generateValuesArray();
		}

		$pDataDetailView = DataDetailViewHandler::createDetailViewByValues($valuesPrefixless);

		$result = DataDetailViewHandler::saveDetailView($pDataDetailView);

		$resultObject = new \stdClass();
		$resultObject->result = $result;
		$resultObject->record_id = null;

		echo json_encode($resultObject);

		wp_die();
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		wp_register_script('admin-js', plugin_dir_url(ONOFFICE_PLUGIN_DIR.'/index.php').'/js/admin.js',
			array('jquery'), '', true);

		wp_enqueue_script('admin-js');
		wp_enqueue_script('postbox');
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
	 * @return array
	 *
	 */

	public function getEnqueueData()
	{
		return array(
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The detail view has been saved.', 'onoffice'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the detail view.', 'onoffice'),
			AdminPageEstate::PARAM_TAB => AdminPageEstate::PAGE_ESTATE_DETAIL,
			self::ENQUEUE_DATA_MERGE => array(AdminPageEstate::PARAM_TAB),
		);
	}
}
