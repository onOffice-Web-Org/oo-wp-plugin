<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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
use onOffice\WPlugin\DataView\DataAddressDetailViewHandler;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\Collection\FieldsCollectionToContentFieldLabelArrayConverter;
use onOffice\WPlugin\Model\ExceptionInputModelMissingField;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderAddressDetailSettings;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Model\InputModelOption;
use onOffice\WPlugin\Model\InputModelOptionAdapterArray;
use onOffice\WPlugin\Renderer\InputModelRenderer;
use onOffice\WPlugin\Types\FieldsCollection;
use function __;
use function add_action;
use function do_accordion_sections;
use function do_action;
use function do_meta_boxes;
use function do_settings_sections;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function get_current_screen;
use function plugin_dir_url;
use function wp_die;
use function wp_enqueue_script;
use function wp_nonce_field;
use function wp_register_script;
use function wp_verify_nonce;
use const ONOFFICE_PLUGIN_DIR;
use onOffice\WPlugin\Controller\AddressListEnvironmentDefault;

/**
 *
 */

class AdminPageAddressDetail
	extends AdminPageAjax
{
	/** */
	const VIEW_SAVE_SUCCESSFUL_MESSAGE = 'view_save_success_message';

	/** */
	const VIEW_SAVE_FAIL_MESSAGE = 'view_save_fail_message';

	/** */
	const FORM_VIEW_LAYOUT_DESIGN = 'viewlayoutdesign';

	/** */
	const FORM_VIEW_PICTURE_TYPES = 'viewpicturetypes';

	/** */
	const FORM_VIEW_SORTABLE_FIELDS_CONFIG = 'viewSortableFieldsConfig';

	/** */
	const FORM_VIEW_SEARCH_FIELD_FOR_FIELD_LISTS_CONFIG = 'viewSearchFieldForFieldListsConfig';

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
		$pDataAddressDetailViewHandler = new DataAddressDetailViewHandler();
		$pAddressDataView = $pDataAddressDetailViewHandler->getAddressDetailView();

		do_action('add_meta_boxes', get_current_screen()->id, null);
		$this->generateMetaBoxes();

		/* @var $pRenderer InputModelRenderer */
		$pRenderer = $this->getContainer()->get(InputModelRenderer::class);
		$pFormViewSortableFields = $this->getFormModelByGroupSlug(self::FORM_VIEW_SORTABLE_FIELDS_CONFIG);
		$pFormViewSearchFieldForFieldLists = $this->getFormModelByGroupSlug(self::FORM_VIEW_SEARCH_FIELD_FOR_FIELD_LISTS_CONFIG);

		echo '<form id="onoffice-ajax" action="' . admin_url( 'admin-post.php' ) . '" method="post">';
		echo '<input type="hidden" name="action" value="' . get_current_screen()->id . '" />';
		echo '<input type="hidden" name="tab" value="' . AdminPageAddress::PAGE_ADDRESS_DETAIL . '" />';
		wp_nonce_field( get_current_screen()->id, 'nonce' );
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		echo '<div id="poststuff" class="oo-poststuff oo-poststuff-address-detail">';
		$pageId = $pAddressDataView->getPageId();

		echo '<span class="viewusage">';
		if ($pageId != null) {
			esc_html_e( 'The shortcode ', 'onoffice-for-wp-websites' );
			echo '<input type="text" style="max-width: 100%;" readonly value="[oo_address view=&quot;'
			     . esc_html( $pAddressDataView->getName() ) . '&quot;]">
			     <input type="button" class="button button-copy" data-clipboard-text="[oo_address view=&quot;'
			     . esc_html( $pAddressDataView->getName() ) . '&quot;]" value="' . esc_html__( 'Copy',
					'onoffice-for-wp-websites' ) . '" ><script>if (navigator.clipboard) { jQuery(".button-copy").show(); }</script>';
			/* translators: %s will be replaced with a link to the appropriate page. */
			printf(esc_attr(__('  is used on %s', 'onoffice-for-wp-websites')),
				'<span class="italic">'.esc_html(get_the_title($pageId)).'</span>');
			edit_post_link(__('Edit Page', 'onoffice-for-wp-websites'), ' ', '', $pageId);
		} else {
			esc_html_e( 'The shortcode ', 'onoffice-for-wp-websites' );
			echo '<input type="text" style="max-width: 100%;" readonly value="[oo_address view=&quot;'
			     . esc_html( $pAddressDataView->getName() ) . '&quot;]">
			     <input type="button" class="button button-copy" data-clipboard-text="[oo_address view=&quot;'
			     . esc_html( $pAddressDataView->getName() ) . '&quot;]" value="' . esc_html__( 'Copy',
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
		do_meta_boxes( get_current_screen()->id, 'side', null );
		echo '</div>';

		echo '<div class="clear"></div>';
		$this->renderSearchFieldForFieldLists($pRenderer, $pFormViewSearchFieldForFieldLists);
		echo '<div class="clear"></div>';

		echo '<div class="clear"></div>';
		do_action('add_meta_boxes', get_current_screen()->id, null);
		echo '<div style="float:left;">';
		$this->generateAccordionBoxes();
		echo '</div>';
		echo '<div id="listSettings" style="float:left;" class="postbox">';
		do_accordion_sections(get_current_screen()->id, 'contactperson', null);
		echo '</div>';

		echo '<div class="fieldsSortable postbox">';
		echo '<h2 class="hndle ui-sortable-handle"><span>' . __('Fields', 'onoffice-for-wp-websites') . '</span></h2>';
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

		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'side');
	}

	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	protected function generateAccordionBoxes()
	{
		$fieldNames = array_keys($this->readFieldnamesByContent(onOfficeSDK::MODULE_ADDRESS));

		foreach ($fieldNames as $category) {
			$slug = $this->generateGroupSlugByModuleCategory(onOfficeSDK::MODULE_ADDRESS, $category);
			$pFormFieldsConfig = $this->getFormModelByGroupSlug($slug);
			if (!is_null($pFormFieldsConfig))
			{
				$this->createMetaBoxByForm($pFormFieldsConfig, 'contactperson');
			}
		}
	}

	/**
	 *
	 */
	protected function buildForms()
	{
		$pFormModelBuilder = $this->getContainer()->get(FormModelBuilderAddressDetailSettings::class);
		$pFormModel = $pFormModelBuilder->generate($this->getPageSlug());
		$this->addFormModel($pFormModel);

		$pInputModelTemplate = $pFormModelBuilder->createInputModelTemplate();
		$pInputModelShortCodeForm = $pFormModelBuilder->createInputModelShortCodeForm();
		$pInputModelShortCodeEstate = $pFormModelBuilder->createInputModelShortCodeEstate();
		$pFormModelLayoutDesign = new FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice-for-wp-websites'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$pFormModelLayoutDesign->addInputModel($pInputModelShortCodeForm);
		$pFormModelLayoutDesign->addInputModel($pInputModelShortCodeEstate);
		$this->addFormModel($pFormModelLayoutDesign);

		$pInputModelPictureTypes = $pFormModelBuilder->createInputModelPictureTypes();
		$pFormModelPictureTypes = new FormModel();
		$pFormModelPictureTypes->setPageSlug($this->getPageSlug());
		$pFormModelPictureTypes->setGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$pFormModelPictureTypes->setLabel(__('Photo Types', 'onoffice-for-wp-websites'));
		$pFormModelPictureTypes->addInputModel($pInputModelPictureTypes);
		$this->addFormModel($pFormModelPictureTypes);

		$pEnvironment = new AddressListEnvironmentDefault();
		$pBuilderShort = $pEnvironment->getFieldsCollectionBuilderShort();
		$pFieldsCollection = new FieldsCollection();
		$pBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$fieldNames = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ADDRESS, $pFieldsCollection);
		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ADDRESS,
			self::FORM_VIEW_SORTABLE_FIELDS_CONFIG, $pFormModelBuilder, $fieldNames);
		$this->addSearchFieldForFieldLists(onOfficeSDK::MODULE_ADDRESS, $pFormModelBuilder);
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

		$pDataAddressDetailViewHandler = new DataAddressDetailViewHandler();
		$valuesPrefixless       = $pInputModelDBAdapterArray->generateValuesArray();

		$pDataDetailView        = $pDataAddressDetailViewHandler->createAddressDetailViewByValues( $valuesPrefixless );
		$success                = true;

		try {
			$pDataAddressDetailViewHandler->saveAddressDetailView( $pDataDetailView );
		} catch ( Exception $pEx ) {
			$success = false;
		}

		$tabQuery    = '&tab=' . AdminPageAddress::PAGE_ADDRESS_DETAIL;
		$statusQuery = $success ? '&saved=true' : '&saved=false';

		wp_redirect( admin_url( 'admin.php?page=onoffice-addresses' . $tabQuery . $statusQuery ) );

		die();
	}

	/**
	 *
	 */
	public function doExtraEnqueues()
	{
		wp_register_script('admin-js', plugin_dir_url(ONOFFICE_PLUGIN_DIR.'/index.php').'dist/admin.min.js',
			array('jquery'), '', true);

		wp_enqueue_script('admin-js');
		wp_enqueue_script('postbox');
		wp_register_script( 'oo-copy-shortcode',
			plugin_dir_url( ONOFFICE_PLUGIN_DIR . '/index.php' ) . 'dist/onoffice-copycode.min.js',
			[ 'jquery' ], '', true );
		wp_enqueue_script( 'oo-copy-shortcode' );
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
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The address detail view has been saved.', 'onoffice-for-wp-websites'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the address detail view.', 'onoffice-for-wp-websites'),
			AdminPageAddress::PARAM_TAB => AdminPageAddress::PAGE_ADDRESS_DETAIL,
			self::ENQUEUE_DATA_MERGE => array(AdminPageAddress::PARAM_TAB)
		);
	}

	/**
	 * @param string $module
	 * @param string $groupSlug
	 * @param FormModelBuilderAddressDetailSettings $pFormModelBuilder
	 * @param array $fieldNames
	 * @throws UnknownModuleException
	 * @throws ExceptionInputModelMissingField
	 */
	private function addFieldsConfiguration(string $module, string $groupSlug, FormModelBuilderAddressDetailSettings $pFormModelBuilder,
		array $fieldNames)
	{
		foreach ($fieldNames as $category => $fields) {
			$slug = $this->generateGroupSlugByModuleCategory($module, $category);
			$pInputModelFieldsConfig = $pFormModelBuilder->createButtonModelFieldsConfigByCategory
				($slug, $fields, $category);
			$pFormModelFieldsConfig = new FormModel();
			$pFormModelFieldsConfig->setPageSlug($this->getPageSlug());
			$pFormModelFieldsConfig->setGroupSlug($slug);
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
	 * @param FormModelBuilderAddressDetailSettings $pFormModelBuilder
	 * @param string $htmlType
	 *
	 */

	private function addSearchFieldForFieldLists($module, FormModelBuilderAddressDetailSettings $pFormModelBuilder, string $htmlType = InputModelBase::HTML_SEARCH_FIELD_FOR_FIELD_LISTS)
	{
		$pInputModelSearchFieldForFieldLists = $pFormModelBuilder->createSearchFieldForFieldLists($module, $htmlType);

		$pFormModelFieldsConfig = new FormModel();
		$pFormModelFieldsConfig->setPageSlug($this->getPageSlug());
		$pFormModelFieldsConfig->setGroupSlug(self::FORM_VIEW_SEARCH_FIELD_FOR_FIELD_LISTS_CONFIG);
		$pFormModelFieldsConfig->addInputModel($pInputModelSearchFieldForFieldLists);
		$this->addFormModel($pFormModelFieldsConfig);
	}

	/**
	 *
	 * @param string $module
	 * @param string $category
	 * @return string
	 *
	 */

	protected function generateGroupSlugByModuleCategory($module, $category)
	{
		return $module.'/'.$category;
	}
}