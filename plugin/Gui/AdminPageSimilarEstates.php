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

use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\Exception\UnknownModuleException;
use onOffice\WPlugin\DataView\DataSimilarEstatesSettingsHandler;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\Collection\FieldsCollectionToContentFieldLabelArrayConverter;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionBackend;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorInternalAnnotations;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderSimilarEstateSettings;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactorySimilarView;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Model\InputModelOptionAdapterArray;
use onOffice\WPlugin\Renderer\InputModelRenderer;
use onOffice\WPlugin\Types\FieldsCollection;
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

	/** */
	const FORM_VIEW_SORTABLE_FIELDS_CONFIG = 'viewSortableFieldsConfig';

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function renderContent()
	{
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'true' ) {
			echo '<div class="notice notice-success is-dismissible"><p>'
			     . esc_html__( 'The similar estates view has been saved.', 'onoffice-for-wp-websites' )
			     . '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'false' ) {
			echo '<div class="notice notice-error is-dismissible"><p>'
			     . esc_html__( 'There was a problem saving the similar estates view.',
					'onoffice-for-wp-websites' )
			     . '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}
		$pDataSimilarSettingsHandler = $this->getContainer()->get(DataSimilarEstatesSettingsHandler::class);
		$pDataSimilarView = $pDataSimilarSettingsHandler->getDataSimilarEstatesSettings();
		do_action('add_meta_boxes', get_current_screen()->id, null);
		$this->generateMetaBoxes();

		$pFieldsCollection = $this->readAllFields();

		/* @var $pRenderer InputModelRenderer */
		$pRenderer = $this->getContainer()->get(InputModelRenderer::class);
		$pFormViewSortableFields = $this->getFormModelByGroupSlug(self::FORM_VIEW_SORTABLE_FIELDS_CONFIG);

		echo '<form id="onoffice-ajax" action="' . admin_url( 'admin-post.php' ) . '" method="post">';
		echo '<input type="hidden" name="action" value="' . get_current_screen()->id . '" />';
		echo '<input type="hidden" name="tab" value="' . AdminPageEstate::PAGE_SIMILAR_ESTATES . '" />';
		wp_nonce_field( get_current_screen()->id, 'nonce' );
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
		echo '<div style="float:left;">';
		$this->generateAccordionBoxes($pFieldsCollection);
		echo '</div>';
		echo '<div id="listSettings" style="float:left;" class="postbox">';
		do_accordion_sections(get_current_screen()->id, 'advanced', null);
		echo '</div>';
		echo '<div class="fieldsSortable postbox" id="'
			. esc_attr(self::getSpecialDivId(onOfficeSDK::MODULE_ESTATE)) . '">';
		echo '<h2 class="hndle ui-sortable-handle"><span>' . __('Real Estate Fields', 'onoffice-for-wp-websites') . '</span></h2>';
		$pRenderer->buildForAjax($pFormViewSortableFields);
		echo '</div>';
		echo '<div class="clear"></div>';
		echo '</div>';

		do_settings_sections($this->getPageSlug());
		submit_button(null, 'primary', 'send_form');

		echo '</form>';
	}

	/**
	 *
	 * @param string $subTitle
	 *
	 */
	public function generatePageMainTitle($subTitle)
	{
		echo '<h1 class="wp-heading-inline">'.esc_html__('onOffice', 'onoffice-for-wp-websites');
		echo ' › ' . esc_html( $subTitle );
		echo ' › '.esc_html__('Similar Estates', 'onoffice-for-wp-websites');
		echo '</h1>';
		echo '<hr class="wp-header-end">';
	}

	/**
	 *
	 */
	private function generateMetaBoxes()
	{
		$pFormSimilarEstates = $this->getFormModelByGroupSlug(self::FORM_VIEW_SIMILAR_ESTATES);
		$this->createMetaBoxByForm($pFormSimilarEstates, 'normal');
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	protected function generateAccordionBoxes(FieldsCollection $pFieldsCollection)
	{
		$pFieldsCollectionConverter = $this->getContainer()->get(FieldsCollectionToContentFieldLabelArrayConverter::class);
		$fieldsEstate = $pFieldsCollectionConverter->convert($pFieldsCollection, onOfficeSDK::MODULE_ESTATE);

		foreach (array_keys($fieldsEstate) as $category) {
			$pFormFieldsConfig = $this->getFormModelByGroupSlug(onOfficeSDK::MODULE_ESTATE . $category);
			$this->createMetaBoxByForm($pFormFieldsConfig, 'advanced');
		}
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
		$pInputModelSimilarEstatesRadius = $pFormModelBuilder->createInputModelSameEstateRadius();
		$pInputModelSimilarEstatesAmount = $pFormModelBuilder->createInputModelSameEstateAmount();
		$pInputModelSimilarEstatesTemplate = $pFormModelBuilder->createInputModelTemplate
			(InputModelOptionFactorySimilarView::INPUT_FIELD_SIMILAR_ESTATES_TEMPLATE);
		$pInputModelSimilarEstatesActivated = $pFormModelBuilder->getCheckboxEnableSimilarEstates();

		$pFormModelSimilarEstates = new FormModel();
		$pFormModelSimilarEstates->setPageSlug($this->getPageSlug());
		$pFormModelSimilarEstates->setGroupSlug(self::FORM_VIEW_SIMILAR_ESTATES);
		$pFormModelSimilarEstates->setLabel(__('Similar Estates', 'onoffice-for-wp-websites'));
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesActivated);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesEstateKind);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesMarketingMethod);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesSamePostalCode);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesRadius);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesAmount);
		$pFormModelSimilarEstates->addInputModel($pInputModelSimilarEstatesTemplate);
		$this->addFormModel($pFormModelSimilarEstates);

		$pFieldsCollection = $this->readAllFields();
		$pFieldsCollectionConverter = $this->getContainer()->get(FieldsCollectionToContentFieldLabelArrayConverter::class);
		$fieldsEstate = $pFieldsCollectionConverter->convert($pFieldsCollection, onOfficeSDK::MODULE_ESTATE);
		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ESTATE,
			self::FORM_VIEW_SORTABLE_FIELDS_CONFIG, $pFormModelBuilder, $fieldsEstate);

	}

	/**
	 * @return FieldsCollection
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function readAllFields(): FieldsCollection
	{
		$pFieldsCollection = new FieldsCollection;
		$pFieldsCollection->merge
		(new FieldModuleCollectionDecoratorInternalAnnotations
		(new FieldModuleCollectionDecoratorGeoPositionBackend
		(new FieldModuleCollectionDecoratorReadAddress(new FieldsCollection()))));
		$this->getContainer()->get(FieldsCollectionBuilderShort::class)
			->addFieldsAddressEstate($pFieldsCollection);
		return $pFieldsCollection;
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function save_form()
	{
		$this->buildForms();
		$action = filter_input( INPUT_POST, 'action' );
		$nonce  = filter_input( INPUT_POST, 'nonce' );

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die();
		}

		$values                    = (object) $this->transformPostValues();
		$pInputModelDBAdapterArray = new InputModelOptionAdapterArray();

		foreach ( $this->getFormModels() as $pFormModel ) {
			foreach ( $pFormModel->getInputModel() as $pInputModel ) {
				if ( $pInputModel instanceof InputModelOption ) {
					$identifier = $pInputModel->getIdentifier();

					$value = isset( $values->$identifier ) ? $values->$identifier : null;
					$pInputModel->setValue( $value );
					$pInputModelDBAdapterArray->addInputModelOption( $pInputModel );
				}
			}
		}

		$pDataSimilarSettingsHandler = $this->getContainer()->get( DataSimilarEstatesSettingsHandler::class );
		$valuesPrefixless            = $pInputModelDBAdapterArray->generateValuesArray();
		$pDataSimilarView            = $pDataSimilarSettingsHandler->createDataSimilarEstatesSettingsByValues( $valuesPrefixless );
		$success                     = true;

		try {
			$pDataSimilarSettingsHandler->saveDataSimilarEstatesSettings( $pDataSimilarView );
		} catch ( Exception $pEx ) {
			$success = false;
		}

		$tabQuery    = '&tab=' . AdminPageEstate::PAGE_SIMILAR_ESTATES;
		$statusQuery = $success ? '&saved=true' : '&saved=false';

		wp_redirect( admin_url( 'admin.php?page=onoffice-estates' . $tabQuery . $statusQuery ) );

		die();
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
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The similar estates view has been saved.', 'onoffice-for-wp-websites'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the similar estates view.', 'onoffice-for-wp-websites'),
			AdminPageEstate::PARAM_TAB => AdminPageEstate::PAGE_SIMILAR_ESTATES,
			self::ENQUEUE_DATA_MERGE => array(AdminPageEstate::PARAM_TAB),
		);
	}

	/**
	 * @param string $module
	 * @param $groupSlug
	 * @param FormModelBuilderSimilarEstateSettings $pFormModelBuilder
	 * @param array $fieldNames
	 * @throws UnknownModuleException
	 * @throws ExceptionInputModelMissingField
	 */
	private function addFieldsConfiguration($module, $groupSlug, FormModelBuilderSimilarEstateSettings $pFormModelBuilder,
											array $fieldNames)
	{
		foreach ($fieldNames as $category => $fields) {
			$pInputModelFieldsConfig = $pFormModelBuilder->createButtonModelFieldsConfigByCategory
			($module . $category, $fields, $category);
			$pInputModelFieldsConfig->setSpecialDivId(self::getSpecialDivId($module));
			$pFormModelFieldsConfig = new FormModel();
			$pFormModelFieldsConfig->setPageSlug($this->getPageSlug());
			$pFormModelFieldsConfig->setGroupSlug($module . $category);
			$pFormModelFieldsConfig->setLabel($category);
			$pFormModelFieldsConfig->addInputModel($pInputModelFieldsConfig);
			$this->addFormModel($pFormModelFieldsConfig);
		}

		$pInputModelSortableFields = $pFormModelBuilder->createSortableFieldList($module,
			InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST);
		$pFormModelSortableFields = new FormModel();
		$pFormModelSortableFields->setPageSlug($this->getPageSlug());
		$pFormModelSortableFields->setGroupSlug($groupSlug);
		$pFormModelSortableFields->addInputModel($pInputModelSortableFields);
		$this->addFormModel($pFormModelSortableFields);

		$pFormHidden = new FormModel();
		$pFormHidden->setIsInvisibleForm(true);

		foreach ($pInputModelSortableFields->getReferencedInputModels() as $pReference) {
			$pFormHidden->addInputModel($pReference);
		}

		$this->addFormModel($pFormHidden);
	}

	/**
	 * @param string $module
	 * @return string
	 */
	private static function getSpecialDivId($module)
	{
		return 'actionFor' . $module;
	}
}
