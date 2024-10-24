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
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\Collection\FieldsCollectionToContentFieldLabelArrayConverter;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionBackend;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorInternalAnnotations;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings;
use onOffice\WPlugin\Model\InputModel\InputModelOptionFactoryDetailView;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Model\InputModelOptionAdapterArray;
use onOffice\WPlugin\Renderer\InputModelRenderer;
use onOffice\WPlugin\Types\FieldsCollection;
use stdClass;
use function __;
use function add_action;
use function do_accordion_sections;
use function do_action;
use function do_meta_boxes;
use function do_settings_sections;
use function edit_post_link;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html__;
use function get_current_screen;
use function get_the_title;
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
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Language;
/**
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
	const FORM_VIEW_LAYOUT_DESIGN = 'viewlayoutdesign';

	/** */
	const FORM_VIEW_ACCESS_CONTROL = 'viewaccesscontrol';

	/** */
	const FORM_VIEW_PICTURE_TYPES = 'viewpicturetypes';

	/** */
	const FORM_VIEW_ADDITIONAL_MEDIA = 'viewdocumenttypes';

	/** */
	const FORM_VIEW_CONTACT_DATA_FIELDS = 'viewcontactdatafields';

	/** */
	const FORM_VIEW_SORTABLE_FIELDS_CONFIG = 'viewSortableFieldsConfig';

	/** */
	const CUSTOM_LABELS = 'customlabels';

	/** */
	const VIEW_UNSAVED_CHANGES_MESSAGE = 'view_unsaved_changes_message';

	/** */
	const VIEW_LEAVE_WITHOUT_SAVING_TEXT = 'view_leave_without_saving_text';

	/** */
	const FORM_VIEW_SEARCH_FIELD_FOR_FIELD_LISTS_CONFIG = 'viewSearchFieldForFieldListsConfig';

	/** */
	const FORM_VIEW_CONTACT_IMAGE_TYPES = 'viewcontactimagetypes';

	/**
	 *
	 */

	public function renderContent()
	{
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'true' ) {
			echo '<div class="notice notice-success is-dismissible"><p>'
			     . esc_html__( 'The detail view has been saved.', 'onoffice-for-wp-websites' )
			     . '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'false' ) {
			echo '<div class="notice notice-error is-dismissible"><p>'
			     . esc_html__( 'There was a problem saving the detail view.', 'onoffice-for-wp-websites' )
			     . '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$pDataView = $pDataDetailViewHandler->getDetailView();
		do_action('add_meta_boxes', get_current_screen()->id, null);
		$this->generateMetaBoxes();

		$pFieldsCollection = $this->readAllFields();

		/* @var $pRenderer InputModelRenderer */
		$pRenderer = $this->getContainer()->get(InputModelRenderer::class);
		$pFormViewSortableFields = $this->getFormModelByGroupSlug(self::FORM_VIEW_SORTABLE_FIELDS_CONFIG);
		$pFormViewSortablecontactFields = $this->getFormModelByGroupSlug(self::FORM_VIEW_CONTACT_DATA_FIELDS);
		$pFormViewSearchFieldForFieldLists = $this->getFormModelByGroupSlug(self::FORM_VIEW_SEARCH_FIELD_FOR_FIELD_LISTS_CONFIG);

		echo '<form id="onoffice-ajax" action="' . admin_url( 'admin-post.php' ) . '" method="post">';
		echo '<input type="hidden" name="action" value="' . get_current_screen()->id . '" />';
		echo '<input type="hidden" name="tab" value="' . AdminPageEstate::PAGE_ESTATE_DETAIL . '" />';
		wp_nonce_field( get_current_screen()->id, 'nonce' );
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		echo '<div id="poststuff" class="oo-poststuff oo-poststuff-estate-detail">';
		$pageId = $pDataView->getPageId();

		echo '<span class="viewusage">';
		if ($pageId != null) {
			esc_html_e( 'The shortcode ', 'onoffice-for-wp-websites' );
			echo '<input type="text" style="max-width: 100%;" readonly value="[oo_estate view=&quot;'
			     . esc_html( $pDataView->getName() ) . '&quot;]">
			     <input type="button" class="button button-copy" data-clipboard-text="[oo_estate view=&quot;'
			     . esc_html( $pDataView->getName() ) . '&quot;]" value="' . esc_html__( 'Copy',
					'onoffice-for-wp-websites' ) . '" ><script>if (navigator.clipboard) { jQuery(".button-copy").show(); }</script>';
			/* translators: %s will be replaced with a link to the appropriate page. */
			printf(esc_attr(__('  is used on %s', 'onoffice-for-wp-websites')),
				'<span class="italic">'.esc_html(get_the_title($pageId)).'</span>');
			edit_post_link(__('Edit Page', 'onoffice-for-wp-websites'), ' ', '', $pageId);
		} else {
			esc_html_e( 'The shortcode ', 'onoffice-for-wp-websites' );
			echo '<input type="text" style="max-width: 100%;" readonly value="[oo_estate view=&quot;'
			     . esc_html( $pDataView->getName() ) . '&quot;]">
			     <input type="button" class="button button-copy" data-clipboard-text="[oo_estate view=&quot;'
			     . esc_html( $pDataView->getName() ) . '&quot;]" value="' . esc_html__( 'Copy',
					'onoffice-for-wp-websites' ) . '" ><script>if (navigator.clipboard) { jQuery(".button-copy").show(); }</script>';
			esc_html_e( ' is not yet used.', 'onoffice-for-wp-websites' );
		}
		echo '</span>';

		echo '<div id="post-body" class="metabox-holder columns-'
			.(1 == get_current_screen()->get_columns() ? '1' : '2').'">';
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
		$this->generateAccordionBoxesContactPerson($pFieldsCollection);
		echo '</div>';
		echo '<div class="clear"></div>';
		$this->renderSearchFieldForFieldLists($pRenderer, $pFormViewSearchFieldForFieldLists);
		echo '<div class="clear"></div>';
		echo '<div id="listSettings" style="float:left;" class="postbox">';
		do_accordion_sections(get_current_screen()->id, 'contactperson', null);
		echo '</div>';
		echo '<div class="fieldsSortable postbox" id="'
			.esc_attr(self::getSpecialDivId(onOfficeSDK::MODULE_ADDRESS)).'">';
		echo '<h2 class="hndle ui-sortable-handle"><span>'.__('Contact Person Fields', 'onoffice-for-wp-websites').'</span></h2>';
		$pRenderer->buildForAjax($pFormViewSortablecontactFields);
		echo '</div>';
		echo '<div class="clear"></div>';

		echo '<div class="clear"></div>';
		do_action('add_meta_boxes', get_current_screen()->id, null);
		echo '<div style="float:left;">';
		$this->generateAccordionBoxes($pFieldsCollection);
		echo '</div>';
		echo '<div id="listSettings" style="float:left;" class="postbox">';
		do_accordion_sections(get_current_screen()->id, 'advanced', null);
		echo '</div>';
		echo '<div class="fieldsSortable postbox" id="'
			.esc_attr(self::getSpecialDivId(onOfficeSDK::MODULE_ESTATE)).'">';
		echo '<h2 class="hndle ui-sortable-handle"><span>'.__('Real Estate Fields', 'onoffice-for-wp-websites').'</span></h2>';
		$pRenderer->buildForAjax($pFormViewSortableFields);
		echo '</div>';
		echo '<div class="clear"></div>';
		echo '</div>';

		do_settings_sections($this->getPageSlug());
		$this->generateBlockPublish();
		echo '</div>';

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
		echo ' › '.esc_html__('Detail View', 'onoffice-for-wp-websites');
		echo '</h1>';
		echo '<hr class="wp-header-end">';
	}


	/**
	 *
	 */

	private function generateMetaBoxes()
	{
		$pFormPictureTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$this->createMetaBoxByForm($pFormPictureTypes, 'side');

		$pFormContactImageTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_CONTACT_IMAGE_TYPES);
		$this->createMetaBoxByForm($pFormContactImageTypes, 'side');

		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'normal');

		$pFormAccessControl = $this->getFormModelByGroupSlug( self::FORM_VIEW_ACCESS_CONTROL );
		$this->createMetaBoxByForm( $pFormAccessControl, 'normal' );

		$pFormDocumentTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_ADDITIONAL_MEDIA);
		$this->createMetaBoxByForm($pFormDocumentTypes, 'side');

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
			$pFormFieldsConfig = $this->getFormModelByGroupSlug(onOfficeSDK::MODULE_ESTATE.$category);
			$this->createMetaBoxByForm($pFormFieldsConfig, 'advanced');
		}
	}

	/**
	 * @param FieldsCollection $pFieldsCollection
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	protected function generateAccordionBoxesContactPerson(FieldsCollection $pFieldsCollection)
	{
		$pFieldsCollectionConverter = $this->getContainer()->get(FieldsCollectionToContentFieldLabelArrayConverter::class);
		$fieldNamesContactData = $pFieldsCollectionConverter->convert($pFieldsCollection, onOfficeSDK::MODULE_ADDRESS);

		foreach (array_keys($fieldNamesContactData) as $category) {
			$pFormFieldsConfig = $this->getFormModelByGroupSlug(onOfficeSDK::MODULE_ADDRESS.$category);
			$this->createMetaBoxByForm($pFormFieldsConfig, 'contactperson');
		}
	}

	/**
	 *
	 */
	protected function buildForms()
	{
		$pFormModelBuilder = $this->getContainer()->get(FormModelBuilderEstateDetailSettings::class);
		$pFormModel = $pFormModelBuilder->generate($this->getPageSlug());
		$this->addFormModel($pFormModel);

		$pInputModelTemplate = $pFormModelBuilder->createInputModelTemplate();
		$pInputModelShortCodeForm = $pFormModelBuilder->createInputModelShortCodeForm();
		$pInputShowStatus = $pFormModelBuilder->createInputModelShowStatus();
		$pInputModelShowPriceOnRequest = $pFormModelBuilder->createInputModelShowPriceOnRequest();
		$pFormModelLayoutDesign = new FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice-for-wp-websites'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$pFormModelLayoutDesign->addInputModel( $pInputModelShortCodeForm );
		$pFormModelLayoutDesign->addInputModel($pInputShowStatus);
		$pFormModelLayoutDesign->addInputModel($pInputModelShowPriceOnRequest);
		$this->addFormModel($pFormModelLayoutDesign);

		$pInputModelPictureTypes = $pFormModelBuilder->createInputModelPictureTypes();
		$pFormModelPictureTypes = new FormModel();
		$pFormModelPictureTypes->setPageSlug($this->getPageSlug());
		$pFormModelPictureTypes->setGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$pFormModelPictureTypes->setLabel(__('Photo Types', 'onoffice-for-wp-websites'));
		$pFormModelPictureTypes->addInputModel($pInputModelPictureTypes);
		$this->addFormModel($pFormModelPictureTypes);

		$pInputModelContactImageTypes = $pFormModelBuilder->createInputModelContactImageTypes();
		$pFormModelContactImageTypes = new FormModel();
		$pFormModelContactImageTypes->setPageSlug($this->getPageSlug());
		$pFormModelContactImageTypes->setGroupSlug(self::FORM_VIEW_CONTACT_IMAGE_TYPES);
		$pFormModelContactImageTypes->setLabel(__('Contact Image', 'onoffice-for-wp-websites'));
		$pFormModelContactImageTypes->addInputModel($pInputModelContactImageTypes);
		$this->addFormModel($pFormModelContactImageTypes);

		$pInputModelAccessControl = $pFormModelBuilder->createInputRestrictAccessControl();
		$pFormModelAccessControl  = new FormModel();
		$pFormModelAccessControl->setPageSlug( $this->getPageSlug() );
		$pFormModelAccessControl->setGroupSlug( self::FORM_VIEW_ACCESS_CONTROL );
		$pFormModelAccessControl->setLabel( __( 'Access Control', 'onoffice-for-wp-websites' ) );
		$pFormModelAccessControl->addInputModel( $pInputModelAccessControl );
		$this->addFormModel( $pFormModelAccessControl );

		$pInputModelDocumentTypes = $pFormModelBuilder->createInputModelExpose();
		$pInputModelMovieLinks = $pFormModelBuilder->createInputModelMovieLinks();
		$pInputModelOguloLinks = $pFormModelBuilder->createInputModelOguloLinks();
		$pInputModelObjectLinks = $pFormModelBuilder->createInputModelObjectLinks();
		$pInputModelLinks = $pFormModelBuilder->createInputModelLinks();
		$pFormModelDocumentTypes = new FormModel();
		$pFormModelDocumentTypes->setPageSlug($this->getPageSlug());
		$pFormModelDocumentTypes->setGroupSlug(self::FORM_VIEW_ADDITIONAL_MEDIA);
		$pFormModelDocumentTypes->setLabel(__('Additional Media', 'onoffice-for-wp-websites'));
		$pFormModelDocumentTypes->addInputModel($pInputModelDocumentTypes);
		$pFormModelDocumentTypes->addInputModel($pInputModelMovieLinks);
		$pFormModelDocumentTypes->addInputModel($pInputModelOguloLinks);
		$pFormModelDocumentTypes->addInputModel($pInputModelObjectLinks);
		$pFormModelDocumentTypes->addInputModel($pInputModelLinks);
		$this->addFormModel($pFormModelDocumentTypes);

		$pFieldsCollection = $this->readAllFields();
		$pFieldsCollectionConverter = $this->getContainer()->get(FieldsCollectionToContentFieldLabelArrayConverter::class);
		$fieldsEstate = $pFieldsCollectionConverter->convert($pFieldsCollection, onOfficeSDK::MODULE_ESTATE);
		$fieldsAddress = $pFieldsCollectionConverter->convert($pFieldsCollection, onOfficeSDK::MODULE_ADDRESS);
		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ESTATE,
			self::FORM_VIEW_SORTABLE_FIELDS_CONFIG, $pFormModelBuilder, $fieldsEstate);
		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ADDRESS,
			self::FORM_VIEW_CONTACT_DATA_FIELDS, $pFormModelBuilder, $fieldsAddress);
		$this->addSearchFieldForFieldLists([onOfficeSDK::MODULE_ADDRESS, onOfficeSDK::MODULE_ESTATE], $pFormModelBuilder);
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

	public function save_form() {
		$this->buildForms();
		$action = filter_input( INPUT_POST, 'action' );
		$nonce  = filter_input( INPUT_POST, 'nonce' );

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die();
		}

		$values = (object) $this->transformPostValues();

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

		$pDataDetailViewHandler = new DataDetailViewHandler();
		$valuesPrefixless       = $pInputModelDBAdapterArray->generateValuesArray();

		$valuesPrefixless = $this->saveField($valuesPrefixless, $values);

		$pDataDetailView        = $pDataDetailViewHandler->createDetailViewByValues( $valuesPrefixless );
		$success                = true;

		try {
			$pDataDetailViewHandler->saveDetailView( $pDataDetailView );
		} catch ( Exception $pEx ) {
			$success = false;
		}

		$tabQuery    = '&tab=' . AdminPageEstate::PAGE_ESTATE_DETAIL;
		$statusQuery = $success ? '&saved=true' : '&saved=false';

		wp_redirect( admin_url( 'admin.php?page=onoffice-estates' . $tabQuery . $statusQuery ) );

		die();
	}

	/**
	 *
	 */
	public function doExtraEnqueues()
	{
		wp_register_script('admin-js', plugin_dir_url(ONOFFICE_PLUGIN_DIR.'/index.php').'/dist/admin.min.js',
			array('jquery'), '', true);

		wp_enqueue_script('admin-js');
		wp_enqueue_script('postbox');
		wp_register_script( 'oo-copy-shortcode',
			plugin_dir_url( ONOFFICE_PLUGIN_DIR . '/index.php' ) . '/dist/onoffice-copycode.min.js',
			[ 'jquery' ], '', true );
		wp_enqueue_script( 'oo-copy-shortcode' );
		wp_register_script('onoffice-custom-form-label-js',
			plugin_dir_url(ONOFFICE_PLUGIN_DIR.'/index.php').'dist/onoffice-custom-form-label.min.js', ['onoffice-multiselect'], '', true);
		wp_enqueue_script('onoffice-custom-form-label-js');
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		wp_register_script('onoffice-multiselect', plugins_url('/dist/onoffice-multiselect.min.js', $pluginPath));
		wp_register_style('onoffice-multiselect', plugins_url('/css/onoffice-multiselect.css', $pluginPath));
		wp_enqueue_script('onoffice-multiselect');
		wp_enqueue_style('onoffice-multiselect');

		wp_register_script('oo-unsaved-changes-message', plugin_dir_url(ONOFFICE_PLUGIN_DIR.'/index.php').'/dist/onoffice-unsaved-changes-message.min.js',
			['jquery'], '', true);
		wp_enqueue_script('oo-unsaved-changes-message');
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
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The detail view has been saved.', 'onoffice-for-wp-websites'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the detail view.', 'onoffice-for-wp-websites'),
			AdminPageEstate::PARAM_TAB => AdminPageEstate::PAGE_ESTATE_DETAIL,
			self::ENQUEUE_DATA_MERGE => array(AdminPageEstate::PARAM_TAB),
			self::CUSTOM_LABELS => $this->readCustomLabels(),
			'label_custom_label' => __('Custom Label: %s', 'onoffice-for-wp-websites'),
			self::VIEW_UNSAVED_CHANGES_MESSAGE => __('Your changes have not been saved yet! Do you want to leave the page without saving?', 'onoffice-for-wp-websites'),
			self::VIEW_LEAVE_WITHOUT_SAVING_TEXT => __('Leave without saving', 'onoffice-for-wp-websites'),
		);
	}

	/**
	 * @param string $module
	 * @param $groupSlug
	 * @param FormModelBuilderEstateDetailSettings $pFormModelBuilder
	 * @param array $fieldNames
	 * @throws UnknownModuleException
	 * @throws ExceptionInputModelMissingField
	 */
	private function addFieldsConfiguration($module, $groupSlug, FormModelBuilderEstateDetailSettings $pFormModelBuilder,
		array $fieldNames)
	{
		foreach ($fieldNames as $category => $fields) {
			$pInputModelFieldsConfig = $pFormModelBuilder->createButtonModelFieldsConfigByCategory
				($module.$category, $fields, $category);
			$pInputModelFieldsConfig->setSpecialDivId(self::getSpecialDivId($module));
			$pFormModelFieldsConfig = new FormModel();
			$pFormModelFieldsConfig->setPageSlug($this->getPageSlug());
			$pFormModelFieldsConfig->setGroupSlug($module.$category);
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
	 *
	 * @param InputModelRenderer $pInputModelRenderer
	 * @param $pFormViewSearchFieldForFieldLists
	 *
	 */

	private function renderSearchFieldForFieldLists(InputModelRenderer $pRenderer, $pFormViewSearchFieldForFieldLists)
	{
		echo '<div class="oo-search-field postbox ">';
		echo '<h2 class="hndle ui-sortable-handle"><span>' . __( 'Field list search', 'onoffice-for-wp-websites' ) . '</span></h2>';
		echo '<div class="inside">';
		$pRenderer->buildForAjax($pFormViewSearchFieldForFieldLists);
		echo '</div>';
		echo '</div>';
	}

	/**
	 *
	 * @param $modules
	 * @param FormModelBuilderEstateDetailSettings $pFormModelBuilder
	 * @param string $htmlType
	 *
	 */

	private function addSearchFieldForFieldLists($module, FormModelBuilderEstateDetailSettings $pFormModelBuilder, string $htmlType = InputModelBase::HTML_SEARCH_FIELD_FOR_FIELD_LISTS)
	{
		$pInputModelSearchFieldForFieldLists = $pFormModelBuilder->createSearchFieldForFieldLists($module, $htmlType);

		$pFormModelFieldsConfig = new FormModel();
		$pFormModelFieldsConfig->setPageSlug($this->getPageSlug());
		$pFormModelFieldsConfig->setGroupSlug(self::FORM_VIEW_SEARCH_FIELD_FOR_FIELD_LISTS_CONFIG);
		$pFormModelFieldsConfig->addInputModel($pInputModelSearchFieldForFieldLists);
		$this->addFormModel($pFormModelFieldsConfig);
	}

	/**
	 * @param string $module
	 * @return string
	 */
	private static function getSpecialDivId($module)
	{
		return 'actionFor'.$module;
	}

	/**
	 *
	 * @return FieldsCollection
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */

	private function buildFieldsCollectionForCurrentEstate(): FieldsCollection
	{
		$pFieldsCollectionBuilder = $this->getContainer()->get( FieldsCollectionBuilderShort::class );
		$pDefaultFieldsCollection = new FieldsCollection();
		$pFieldsCollectionBuilder->addFieldsAddressEstate( $pDefaultFieldsCollection )
		                         ->addFieldsEstateGeoPosisionBackend( $pDefaultFieldsCollection )
		                         ->addFieldsEstateDecoratorReadAddressBackend( $pDefaultFieldsCollection );

		foreach ( $pDefaultFieldsCollection->getAllFields() as $pField ) {
			if ( ! in_array( $pField->getModule(), [ onOfficeSDK::MODULE_ADDRESS, onOfficeSDK::MODULE_ESTATE ], true ) ) {
				$pDefaultFieldsCollection->removeFieldByModuleAndName
				( $pField->getModule(), $pField->getName() );
			}

		}

		return $pDefaultFieldsCollection;
	}

	/**
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	private function readCustomLabels(): array
	{
		$result                 = [];
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$dataDetailView         = $pDataDetailViewHandler->getDetailView();
		$pLanguage              = $this->getContainer()->get( Language::class );

		foreach ( $this->buildFieldsCollectionForCurrentEstate()->getAllFields() as $pField ) {
			$valuesByLocale = $dataDetailView->getCustomLabels();
			$currentLocale  = $pLanguage->getLocale();
			$valuesByLocale = $valuesByLocale[ $pField->getName() ] ?? '';

			if ( isset( $valuesByLocale[ $currentLocale ] ) && is_array( $valuesByLocale ) ) {
				$valuesByLocale['native'] = $valuesByLocale[ $currentLocale ];
				unset( $valuesByLocale[ $currentLocale ] );
			}
			$result[ $pField->getName() ] = $valuesByLocale;
		}

		return $result;
	}

	/**
	 * @param array $valuesPrefixless
	 * @param string $value
	 */
	private function saveField( array $valuesPrefixless, $values )
	{
		$data        = [];
		$customLabel = (array) ( $values->{'customlabel-lang'} );

		foreach ( $customLabel as $key => $value ) {
			$data[ $key ] = $this->addLocaleToModelForField( $value );
		}
		$valuesPrefixless['oo_plugin_fieldconfig_estate_translated_labels'] =
			(array) ( $valuesPrefixless['oo_plugin_fieldconfig_form_translated_labels']['value'] ?? [] ) +
			( $data );

		return $valuesPrefixless;
	}

	/**
	 * @param array $value
	 */
	private function addLocaleToModelForField( $value )
	{
		$pLanguage = $this->getContainer()->get( Language::class );

		foreach ( $value as $locale => $values ) {
			$value = (array) $value;
			if ( $locale === 'native' ) {
				$value[ $pLanguage->getLocale() ] = $values;
				unset( $value['native'] );
			}
		}

		return $value;
	}
}
