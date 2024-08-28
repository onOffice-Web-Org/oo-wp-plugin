<?php

/**
 *
 *    Copyright (C) 2017-2019 onOffice GmbH
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
use onOffice\WPlugin\Controller\SortList\SortListTypes;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorGeoPositionBackend;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBEstateListSettings;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderEstateDetailSettings;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigEstate;
use onOffice\WPlugin\Model\InputModelBuilder\InputModelBuilderGeoRange;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelDBAdapterRow;
use onOffice\WPlugin\Model\InputModelLabel;
use onOffice\WPlugin\Record\BooleanValueToFieldList;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Renderer\InputModelRenderer;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Record\RecordManager;
use stdClass;
use function __;
use function wp_enqueue_script;
use function wp_localize_script;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class AdminPageEstateListSettings
	extends AdminPageEstateListSettingsBase
{
	/** */
	const FORM_VIEW_GEOFIELDS = 'geofields';

	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$this->setPageTitle(__('Edit List View', 'onoffice-for-wp-websites'));
	}


	/**
	 *
	 */

	protected function buildForms()
	{
		$pFormModelBuilder = new FormModelBuilderDBEstateListSettings();
		$pFormModel = $pFormModelBuilder->generate($this->getPageSlug(), $this->getListViewId());
		$this->addFormModel($pFormModel);

		$pInputModelName = $pFormModelBuilder->createInputModelName();
		$pFormModelName = new FormModel();
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

		$pInputModelListType = $pFormModelBuilder->createInputModelListType();
		$pInputModelListReferenceEstates = $pFormModelBuilder->createInputModelShowReferenceEstates();
		$pInputModelFilter = $pFormModelBuilder->createInputModelFilter();
		$pInputModelRecordsPerPage = $pFormModelBuilder->createInputModelRecordsPerPage();
		$pInputModelShowStatus = $pFormModelBuilder->createInputModelShowStatus();
		$pInputModelShowMap = $pFormModelBuilder->createInputModelShowMap();
		$pInputModelShowPriceOnRequest = $pFormModelBuilder->createInputModelShowPriceOnRequest();
		$pDataDetailViewHandler = new DataDetailViewHandler();
		$pDataDetailView = $pDataDetailViewHandler->getDetailView();
		$restrictAccessControl     = $pDataDetailView->getViewRestrict();
		if ( $restrictAccessControl ) {
			$restrictedPageDetail = '<a href="' . esc_attr( admin_url( 'admin.php?page=onoffice-estates&tab=detail' ) ) . '" target="_blank">' . __( 'restricted',
					'onoffice-for-wp-websites' ) . '</a>';
			$pInputModelListReferenceEstates->setHintHtml( sprintf( __( 'Reference estates will not link to their detail page, because the access is %s.',
				'onoffice-for-wp-websites' ), $restrictedPageDetail ) );
		} else {
			$restrictedPageDetail = '<a href="' . esc_attr( admin_url( 'admin.php?page=onoffice-estates&tab=detail' ) ) . '" target="_blank">' . __( 'not restricted',
					'onoffice-for-wp-websites' ) . '</a>';
			$pInputModelListReferenceEstates->setHintHtml( sprintf( __( 'Reference estates will link to their detail page, because the access is %s.',
				'onoffice-for-wp-websites' ), $restrictedPageDetail ) );
		}

		$pFormModelRecordsFilter = new FormModel();
		$pFormModelRecordsFilter->setPageSlug($this->getPageSlug());
		$pFormModelRecordsFilter->setGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$pFormModelRecordsFilter->setLabel(__('Filters & Records', 'onoffice-for-wp-websites'));
		$pFormModelRecordsFilter->addInputModel($pInputModelListType);
		$pFormModelRecordsFilter->addInputModel($pInputModelListReferenceEstates);
		$pFormModelRecordsFilter->addInputModel($pInputModelFilter);
		$pFormModelRecordsFilter->addInputModel($pInputModelRecordsPerPage);

		$this->addFormModel($pFormModelRecordsFilter);

		$pInputModelSortBySelectTwoStandard = $pFormModelBuilder->createInputModelSortBySelectTwoStandard();
		$pInputModelSorting              = $pFormModelBuilder->createInputModelSortingSelection();
		$pInputModelSortBySelectTwoUser  = $pFormModelBuilder->createInputModelSortBySelectTwo();
		$pInputModelSortByDefault        = $pFormModelBuilder->createInputModelSortByDefault();
		$pInputModelSortByspec           = $pFormModelBuilder->createInputModelSortBySpec();
		$pInputModelSortOrder            = $pFormModelBuilder->createInputModelSortOrder();
		$pInputModelRandomSort           = $pFormModelBuilder->createInputModelRandomSort();
		$pInputModelMarkedPropertiesSort = $pFormModelBuilder->createInputModelMarkedPropertiesSort();
		$pInputModelSortByTags           = $pFormModelBuilder->createInputModelSortByTags();
		$pInputModelSortByTagsDirection  = $pFormModelBuilder->createInputModelSortByTagsDirection();

		$pFormModelRecordsFilter = new FormModel();
		$pFormModelRecordsFilter->setPageSlug( $this->getPageSlug() );
		$pFormModelRecordsFilter->setGroupSlug( self::FORM_VIEW_RECORDS_SORTING );
		$pFormModelRecordsFilter->setLabel( __( 'Sorting', 'onoffice-for-wp-websites' ) );
		$pFormModelRecordsFilter->addInputModel( $pInputModelSorting );
		$pFormModelRecordsFilter->addInputModel( $pInputModelSortBySelectTwoUser );
		$pFormModelRecordsFilter->addInputModel( $pInputModelSortByDefault );
		$pFormModelRecordsFilter->addInputModel( $pInputModelSortByspec );
		$pFormModelRecordsFilter->addInputModel( $pInputModelSortBySelectTwoStandard );
		$pFormModelRecordsFilter->addInputModel( $pInputModelSortOrder );
		$pFormModelRecordsFilter->addInputModel( $pInputModelRandomSort );
		$pFormModelRecordsFilter->addInputModel( $pInputModelMarkedPropertiesSort );
		$pFormModelRecordsFilter->addInputModel( $pInputModelSortByTags );
		$pFormModelRecordsFilter->addInputModel( $pInputModelSortByTagsDirection );
		$this->addFormModel( $pFormModelRecordsFilter );

		$pInputModelTemplate = $pFormModelBuilder->createInputModelTemplate('estate');
		$pFormModelLayoutDesign = new FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice-for-wp-websites'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$pFormModelLayoutDesign->addInputModel($pInputModelShowStatus);
		$pFormModelLayoutDesign->addInputModel($pInputModelShowPriceOnRequest);
		$pFormModelLayoutDesign->addInputModel($pInputModelShowMap);
		$this->addFormModel($pFormModelLayoutDesign);

		$pInputModelPictureTypes = $pFormModelBuilder->createInputModelPictureTypes();
		$pFormModelPictureTypes = new FormModel();
		$pFormModelPictureTypes->setPageSlug($this->getPageSlug());
		$pFormModelPictureTypes->setGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$pFormModelPictureTypes->setLabel(__('Photo Types', 'onoffice-for-wp-websites'));
		$pFormModelPictureTypes->addInputModel($pInputModelPictureTypes);
		$this->addFormModel($pFormModelPictureTypes);

		$pListView = new DataListView($this->getListViewId() ?? 0, '');

		$pFormModelGeoFields = new FormModel();
		$pFormModelGeoFields->setPageSlug($this->getPageSlug());
		$pFormModelGeoFields->setGroupSlug(self::FORM_VIEW_GEOFIELDS);
		$pFormModelGeoFields->setLabel(__('Geo Fields', 'onoffice-for-wp-websites'));
		$pInputModelBuilderGeoRange = new InputModelBuilderGeoRange(onOfficeSDK::MODULE_ESTATE);
		foreach ($pInputModelBuilderGeoRange->build($pListView) as $pInputModel) {
			$pFormModelGeoFields->addInputModel($pInputModel);
		}

		$geoNotice = __('At least city or postcode are required.', 'onoffice-for-wp-websites');
		$pInputModelGeoLabel = new InputModelLabel(null, $geoNotice);
		$pInputModelGeoLabel->setValueEnclosure(InputModelLabel::VALUE_ENCLOSURE_ITALIC);
		$pFormModelGeoFields->addInputModel($pInputModelGeoLabel);

		$this->addFormModel($pFormModelGeoFields);

		$pFieldCollection = new FieldModuleCollectionDecoratorGeoPositionBackend(new FieldsCollection());
		$fieldNames = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ESTATE, $pFieldCollection);

		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ESTATE, $pFormModelBuilder, $fieldNames);
		$this->addSortableFieldsList([onOfficeSDK::MODULE_ESTATE], $pFormModelBuilder);
		$this->addSearchFieldForFieldLists(onOfficeSDK::MODULE_ESTATE, $pFormModelBuilder);
	}


	/**
	 *
	 */

	protected function generateMetaBoxes()
	{
		$pFormRecordsFilter = $this->getFormModelByGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$this->createMetaBoxByForm($pFormRecordsFilter, 'normal');

		$pFormRecordsFilter = $this->getFormModelByGroupSlug(self::FORM_VIEW_RECORDS_SORTING);
		$this->createMetaBoxByForm($pFormRecordsFilter, 'normal');

		$pFormPictureTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$this->createMetaBoxByForm($pFormPictureTypes, 'side');

		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'side');

		$pFormGeoPosition = $this->getFormModelByGroupSlug(self::FORM_VIEW_GEOFIELDS);
		$this->createMetaBoxByForm($pFormGeoPosition, 'normal');
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		$this->cleanPreviousBoxes();
		$module = onOfficeSDK::MODULE_ESTATE;

		$pFieldCollection = new FieldModuleCollectionDecoratorGeoPositionBackend(new FieldsCollection());
		$fieldNames = array_keys($this->readFieldnamesByContent($module, $pFieldCollection));

		foreach ($fieldNames as $category) {
			$slug = $this->generateGroupSlugByModuleCategory($module, $category);
			$pFormFieldsConfig = $this->getFormModelByGroupSlug($slug);
			$pFormFieldsConfig->setOoModule($module);
			$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
		}
	}


	/**
	 *
	 * Since checkboxes are only being submitted if checked they need to be reorganized
	 * @todo Examine booleans automatically
	 *
	 * @param stdClass $pValues
	 *
	 */

	protected function prepareValues(stdClass $pValues)
	{
		$pBoolToFieldList = new BooleanValueToFieldList(new InputModelDBFactoryConfigEstate, $pValues);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigEstate::INPUT_FIELD_FILTERABLE);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigEstate::INPUT_FIELD_HIDDEN);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigEstate::INPUT_FIELD_AVAILABLE_OPTIONS);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigEstate::INPUT_FIELD_CONVERT_TEXT_TO_SELECT_FOR_CITY_FIELD);
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

		if (!in_array($pDataListView->getListType(), array('default', 'reference', 'favorites'))) {
			throw new UnknownViewException;
		}
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		$translation = array(
			'multipleSelectOptions' => __('Select Some Options', 'onoffice-for-wp-websites'),
			'singleSelectOption' => __('Select an Option', 'onoffice-for-wp-websites'),
		);

		parent::doExtraEnqueues();
		wp_enqueue_script('oo-checkbox-js');
		wp_enqueue_script('onoffice-custom-form-label-js');
		wp_enqueue_script('oo-reference-estate-js');
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
		wp_localize_script('oo-sanitize-shortcode-name', 'shortcode', ['name' => 'oopluginlistviews-name']);
		wp_register_script('onoffice-multiselect', plugins_url('/dist/onoffice-multiselect.min.js', $pluginPath));
		wp_register_style('onoffice-multiselect', plugins_url('/css/onoffice-multiselect.css', $pluginPath));
		wp_enqueue_script('onoffice-multiselect');
		wp_enqueue_style('onoffice-multiselect');
		wp_enqueue_script('oo-sanitize-shortcode-name');
		wp_enqueue_script('oo-copy-shortcode');
		wp_enqueue_script('select2',  plugin_dir_url( ONOFFICE_PLUGIN_DIR . '/index.php' ) . 'vendor/select2/select2/dist/js/select2.min.js');
		wp_enqueue_style('select2',  plugin_dir_url( ONOFFICE_PLUGIN_DIR . '/index.php' ) . 'vendor/select2/select2/dist/css/select2.min.css');
		wp_enqueue_script('onoffice-custom-select',  plugins_url('/dist/onoffice-custom-select.min.js', $pluginPath));
		wp_localize_script('onoffice-custom-select', 'custom_select2_translation', $translation);
	}
}
