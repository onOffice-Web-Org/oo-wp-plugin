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
use onOffice\WPlugin\DataView\DataSimilarEstatesSettingsHandler;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactorySimilarView;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Model\InputModelOptionAdapterArray;
use stdClass;
use function __;
use function add_action;
use function add_screen_option;
use function do_action;
use function do_meta_boxes;
use function do_settings_sections;
use function esc_html__;
use function get_current_screen;
use function json_decode;
use function json_encode;
use function plugin_dir_url;
use function submit_button;
use function wp_die;
use function wp_enqueue_script;
use function wp_nonce_field;
use function wp_register_script;
use function wp_verify_nonce;
use const ONOFFICE_PLUGIN_DIR;

/**
 *
 */

class AdminPageSimilarEstates
	extends AdminPageAjax
{
	/** */
	const VIEW_SAVE_SUCCESSFUL_MESSAGE = 'view_save_success_message';

	/** */
	const VIEW_SAVE_FAIL_MESSAGE = 'view_save_fail_message';

	/** */
	const FORM_VIEW_SIMILAR_ESTATES = 'viewsimilarestates';

	public function renderContent()
	{
		$pDataSimilarSettingsHandler = new DataSimilarEstatesSettingsHandler();
		$pDataSimilarView = $pDataSimilarSettingsHandler->getDataSimilarEstatesSettings();
		do_action('add_meta_boxes', get_current_screen()->id, null);
		$this->generateMetaBoxes();

		wp_nonce_field( $this->getPageSlug() );

		$this->generatePageMainTitle(__('Edit List View', 'onoffice'));
		echo '<div id="onoffice-ajax">';
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		echo '<div id="poststuff">';
		$pageId = $pDataSimilarView->getPageId();

		echo '<div id="post-body" class="metabox-holder columns-'
			.(1 == get_current_screen()->get_columns() ? '1' : '2').'">';
		echo '<div class="postbox-container" id="postbox-container-2">';
		do_meta_boxes(get_current_screen()->id, 'normal', null );
		echo '</div>';
		echo '<div class="clear"></div>';
		echo '</div>';
		echo '</div>';

		do_settings_sections($this->getPageSlug());
		submit_button(null, 'primary', 'send_ajax');

		echo '<script>'
			.'jQuery(document).ready(function(){'
				.'onOffice.ajaxSaver = new onOffice.ajaxSaver("onoffice-ajax");'
				.'onOffice.ajaxSaver.register();'
			.'});'
		.'</script>';
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
		echo ' › '.esc_html__('Similar Estates', 'onoffice');
		echo '</h1>';
		echo '<hr class="wp-header-end">';
	}

	/**
	 *
	 */

		/**
	 *
	 */

	private function generateMetaBoxes()
	{
		$pFormSimilarEstates = $this->getFormModelByGroupSlug(self::FORM_VIEW_SIMILAR_ESTATES);
		$this->createMetaBoxByForm($pFormSimilarEstates, 'normal');
	}

	/**
	 *
	 */
	protected function buildForms()
	{
		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
		$pFormModelBuilder = new FormModelBuilderSimilarEstateSettings();
		$pFormModel = $pFormModelBuilder->generate($this->getPageSlug());
		$this->addFormModel($pFormModel);

		$pInputModelSimilarEstatesEstateKind = $pFormModelBuilder->createInputModelSimilarEstateKind();
		$pInputModelSimilarEstatesMarketingMethod = $pFormModelBuilder->createInputModelSimilarEstateMarketingMethod();
		$pInputModelSimilarEstatesSamePostalCode = $pFormModelBuilder->createInputModelSameEstatePostalCode();
        $pInputModelSimilarEstatesDontShowArchived = $pFormModelBuilder->createInputModelDontShowArchived();
		$pInputModelSimilarEstatesRadius = $pFormModelBuilder->createInputModelSameEstateRadius();
		$pInputModelSimilarEstatesAmount = $pFormModelBuilder->createInputModelSameEstateAmount();
		$pInputModelSimilarEstatesTemplate = $pFormModelBuilder->createInputModelTemplate
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_TEMPLATE);
		$pInputModelSimilarEstatesActivated = $pFormModelBuilder->getCheckboxEnableSimilarEstates();

		$pFormModelSimilarEstates = new FormModel();
		$pFormModelSimilarEstates->setPageSlug($this->getPageSlug());
		$pFormModelSimilarEstates->setGroupSlug(self::FORM_VIEW_SIMILAR_ESTATES);
		$pFormModelSimilarEstates->setLabel(__('Similar Estates', 'onoffice'));
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesActivated);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesEstateKind);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesMarketingMethod);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesSamePostalCode);
        $pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesDontShowArchived);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesRadius);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesAmount);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesTemplate);
		$this->addFormModel($pFormModelSimilarEstates);

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

		foreach ($this->getFormModels() as $pFormModel) {
			foreach ($pFormModel->getInputModel() as $pInputModel) {
				if ($pInputModel instanceof InputModelOption) {
					$identifier = $pInputModel->getIdentifier();

					$value = isset($values->$identifier) ? $values->$identifier : null;
					$pInputModel->setValue($value);
					$pInputModelDBAdapterArray->addInputModelOption($pInputModel);
				}
			}
		}

		$pDataSimilarSettingsHandler = new DataSimilarEstatesSettingsHandler();
		$valuesPrefixless = $pInputModelDBAdapterArray->generateValuesArray();
		$pDataSimilarView = $pDataSimilarSettingsHandler->createDataSimilarEstatesSettingsByValues($valuesPrefixless);
		$pResultObject = new stdClass();

		try {
			$pDataSimilarSettingsHandler->saveDataSimilarEstatesSettings($pDataSimilarView);
			$pResultObject->result = true;
		} catch (Exception $pEx) {
			$pResultObject->result = false;
		}

		$pResultObject->record_id = null;
		$pResultObject->messageKey = $pResultObject->result ?
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE :
			self::VIEW_SAVE_FAIL_MESSAGE;

		echo json_encode($pResultObject);

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
	 * rest will be added via js
	 */
	public function addAdminNoticeWrapper()
	{
		echo '<div id="onoffice-notice-wrapper"></div>';
	}

	/**
	 * @return array
	 */
	public function getEnqueueData(): array
	{
		return array(
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The Similar Estate has been saved.', 'onoffice'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the Similar Estate.', 'onoffice'),
			AdminPageEstate::PARAM_TAB => AdminPageEstate::PAGE_SIMILAR_ESTATES,
			self::ENQUEUE_DATA_MERGE => array(AdminPageEstate::PARAM_TAB),
		);
	}
}
