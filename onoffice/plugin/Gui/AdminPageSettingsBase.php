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

use Exception;
use onOffice\WPlugin\Model;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilder;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelDBAdapterRow;
use onOffice\WPlugin\Renderer\InputModelRenderer;
use stdClass;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class AdminPageSettingsBase
	extends AdminPageAjax
{
	/** caution: also needs to be set in Javascript */
	const POST_RECORD_ID = 'record_id';

	/** GET param for the view ID */
	const GET_PARAM_VIEWID = 'id';

	/** */
	const FORM_RECORD_NAME = 'recordname';

	/** */
	const FORM_VIEW_SORTABLE_FIELDS_CONFIG = 'viewSortableFieldsConfig';

	/** */
	const VIEW_SAVE_SUCCESSFUL_MESSAGE = 'view_save_success_message';

	/** */
	const VIEW_SAVE_FAIL_MESSAGE = 'view_save_fail_message';

	/** @var string */
	private $_pageTitle = null;

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
		$this->_listViewId = filter_input(INPUT_GET, self::GET_PARAM_VIEWID);
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
	 * @throws Exception
	 *
	 */

	abstract protected function validate();


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

	public function renderContent()
	{
		do_action('add_meta_boxes', get_current_screen()->id, null);
		$this->generateMetaBoxes();

		$pFormViewName = $this->getFormModelByGroupSlug(self::FORM_RECORD_NAME);
		$pInputRendererViewName = new InputModelRenderer($pFormViewName);

		$pFormViewSortableFields = $this->getFormModelByGroupSlug(self::FORM_VIEW_SORTABLE_FIELDS_CONFIG);
		$pRendererSortablefields = new InputModelRenderer($pFormViewSortableFields);

		wp_nonce_field( $this->getPageSlug() );

		$this->generatePageMainTitle($this->_pageTitle);
		echo '<div id="onoffice-ajax">';
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		echo '<div id="poststuff">';
		echo '<div id="post-body" class="metabox-holder columns-'
			.(1 == get_current_screen()->get_columns() ? '1' : '2').'">';
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
		echo '<div class="clear"></div>';
		do_action('add_meta_boxes', get_current_screen()->id, null);
		echo '<div style="float:left;">';
		$this->generateAccordionBoxes();
		echo '</div>';
		echo '<div id="listSettings" style="float:left;" class="postbox">';
		do_accordion_sections(get_current_screen()->id, 'side', null);
		echo '</div>';
		echo '<div class="fieldsSortable postbox">';
		echo '<h2 class="hndle ui-sortable-handle"><span>'.__('Fields', 'onoffice').'</span></h2>';
		$pRendererSortablefields->buildForAjax();
		echo '</div>';
		echo '<div class="clear"></div>';
		echo '</div>';
		echo '</div>';
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
		$this->validate($recordId);

		if (!wp_verify_nonce($nonce, $action)) {
			wp_die();
		}

		$values = json_decode(filter_input(INPUT_POST, 'values'));
		$this->prepareValues($values);
		$pInputModelDBAdapterRow = new InputModelDBAdapterRow();

		foreach ($this->getFormModels() as $pFormModel) {
			foreach ($pFormModel->getInputModel() as $pInputModel) {
				if ($pInputModel instanceof InputModelDB) {
					$identifier = $pInputModel->getIdentifier();
					$value = isset($values->$identifier) ? $values->$identifier : null;
					$pInputModel->setValue($value);
					$pInputModelDBAdapterRow->addInputModelDB($pInputModel);
				}
			}
		}
		$row = $pInputModelDBAdapterRow->createUpdateValuesByTable();
		$row = $this->setFixedValues($row);


		$pResultObject = new stdClass();
		$this->updateValues($row, $pResultObject, $recordId);
		echo json_encode($pResultObject);

		wp_die();
	}


	/**
	 *
	 * @param object $values
	 *
	 */

	protected function prepareValues($values) {}


	/**
	 *
	 * @param string $module
	 * @param FormModelBuilder $pFormModelBuilder
	 * @param array $fieldNames
	 * @param bool $addModule
	 *
	 */

	protected function addFieldsConfiguration($module, FormModelBuilder $pFormModelBuilder,
		array $fieldNames, $addModule = false)
	{
		foreach ($fieldNames as $category => $fields) {
			$pInputModelFieldsConfig = $pFormModelBuilder->createInputModelFieldsConfigByCategory
				($category, $fields);
			if ($addModule) {
				$pInputModelFieldsConfig->setModule($module);
			}
			$pFormModelFieldsConfig = new Model\FormModel();
			$pFormModelFieldsConfig->setPageSlug($this->getPageSlug());
			$pFormModelFieldsConfig->setGroupSlug($category);
			$pFormModelFieldsConfig->setLabel($category);
			$pFormModelFieldsConfig->addInputModel($pInputModelFieldsConfig);
			$this->addFormModel($pFormModelFieldsConfig);
		}
	}


	/**
	 *
	 * @param array $modules
	 * @param FormModelBuilder $pFormModelBuilder
	 * @param string $htmlType
	 *
	 */

	protected function addSortableFieldsList(array $modules, FormModelBuilder $pFormModelBuilder,
		$htmlType = InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST)
	{
		$pInputModelSortableFields = $pFormModelBuilder->createSortableFieldList($modules, $htmlType);
		$pFormModelSortableFields = new Model\FormModel();
		$pFormModelSortableFields->setPageSlug($this->getPageSlug());
		$pFormModelSortableFields->setGroupSlug
			(AdminPageEstateListSettings::FORM_VIEW_SORTABLE_FIELDS_CONFIG);
		$pFormModelSortableFields->setLabel(__('Fields Configuration', 'onoffice'));
		$pFormModelSortableFields->addInputModel($pInputModelSortableFields);
		$this->addFormModel($pFormModelSortableFields);

		$pFormHidden = new Model\FormModel();
		$pFormHidden->setIsInvisibleForm(true);

		foreach ($pInputModelSortableFields->getReferencedInputModels() as $pReference) {
			$pFormHidden->addInputModel($pReference);
		}

		$this->addFormModel($pFormHidden);
	}


	/**
	 *
	 * @param array $row
	 * @param stdClass $pResult having the properties `result` and `record_id`
	 * @param int $recordId
	 *
	 */

	abstract protected function updateValues(array $row, stdClass $pResult, $recordId = null);


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


	/**
	 *
	 * @param array $row
	 * @return array
	 *
	 */

	protected function setFixedValues(array $row) {
		return $row;
	}


	/**
	 *
	 */

	protected function generateMetaBoxes()
		{}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
		{}


	/** @return string */
	protected function getPageTitle()
		{ return $this->_pageTitle; }

	/** @param string $pageTitle */
	protected function setPageTitle($pageTitle)
		{ $this->_pageTitle = $pageTitle; }

		/** @return string */
	public function getListViewId()
		{ return $this->_listViewId; }
}
