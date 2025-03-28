<?php

/**
 *
 *    Copyright (C) 2018 onOffice GmbH
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
use onOffice\WPlugin\DataView\UnknownViewException;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBAddress;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigAddress;
use onOffice\WPlugin\Model\InputModelBase;
use onOffice\WPlugin\Record\BooleanValueToFieldList;
use onOffice\WPlugin\Record\RecordManager;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerInsertException;
use onOffice\WPlugin\Record\RecordManagerInsertGeneric;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use onOffice\WPlugin\Record\RecordManagerUpdateListViewAddress;
use stdClass;
use onOffice\WPlugin\Controller\AddressListEnvironmentDefault;
use onOffice\WPlugin\Field\FieldModuleCollectionDecoratorReadAddress;
use onOffice\WPlugin\Types\FieldsCollection;
use function __;
use function wp_enqueue_script;
use onOffice\WPlugin\DataView\DataAddressDetailView;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelRead;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelDelete;
use onOffice\WPlugin\Field\CustomLabel\ModelToOutputConverter\CustomLabelRowSaver;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderFromNamesForm;
use onOffice\WPlugin\Field\CustomLabel\Exception\CustomLabelDeleteException;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionToContentFieldLabelArrayConverter;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class AdminPageAddressListSettings
	extends AdminPageSettingsBase
{
	/** @var FormModelBuilderDBAddress */
	private $_pFormModelBuilderAddress = null;

	/** @var FieldsCollection */
	private $_pFieldsCollection = null;

	/** */
	const CUSTOM_LABELS = 'customlabels';

	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$this->setPageTitle(__('Edit Address List', 'onoffice-for-wp-websites'));
	}

	/**
	 *
	 */

	public function renderContent()
	{
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'empty' ) {
			echo '<div class="notice notice-error is-dismissible"><p>'
				. esc_html__( 'There was a problem saving the address. The Name field cannot be empty.', 'onoffice-for-wp-websites' )
				. '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'false' ) {
			echo '<div class="notice notice-error is-dismissible"><p>'
			     . esc_html__( 'There was a problem saving the view. Please make '
			                   . 'sure the name of the view is unique, even across all address list types.',
					'onoffice-for-wp-websites' )
			     . '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}
		parent::renderContent();
	}


	/**
	 *
	 */

	protected function buildForms()
	{
		$pEnvironment = new AddressListEnvironmentDefault();
		$this->_pFormModelBuilderAddress = new FormModelBuilderDBAddress();
		$pFormModel = $this->_pFormModelBuilderAddress->generate($this->getPageSlug(), $this->getListViewId());
		$this->addFormModel($pFormModel);
		$pBuilderShort = $pEnvironment->getFieldsCollectionBuilderShort();
		$pFieldsCollection = new FieldsCollection();
		$pFieldsCollectionConverter = $this->getContainer()->get(FieldsCollectionToContentFieldLabelArrayConverter::class);
		$pBuilderShort->addFieldsAddressEstate($pFieldsCollection);
		$pBuilderShort->addFieldsEstateDecoratorReadAddressBackend($pFieldsCollection);
		$fieldNames = $pFieldsCollectionConverter->convert($pFieldsCollection, onOfficeSDK::MODULE_ADDRESS);
		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ADDRESS, $this->_pFormModelBuilderAddress, $fieldNames);
		$this->addSortableFieldsList(array(onOfficeSDK::MODULE_ADDRESS), $this->_pFormModelBuilderAddress,
			InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST);
		$this->addSearchFieldForFieldLists(onOfficeSDK::MODULE_ADDRESS, $this->_pFormModelBuilderAddress);
		$this->_pFieldsCollection = $pFieldsCollection;

		$this->addFormModelName();
		$this->addFormModelPictureTypes();
		$this->addFormModelTemplate();
		$this->addFormModelRecordsFilter();
		$this->addFormModelRecordsSorting();
	}


	/**
	 *
	 */

	private function addFormModelName()
	{
		$pInputModelName = $this->_pFormModelBuilderAddress->createInputModelName();
		$pFormModelName  = new FormModel();
		$pFormModelName->setPageSlug($this->getPageSlug());
		$pFormModelName->setGroupSlug(self::FORM_RECORD_NAME);
		$pFormModelName->setLabel(__('choose name', 'onoffice-for-wp-websites'));
		$pFormModelName->addInputModel($pInputModelName);
		$this->addFormModel($pFormModelName);
		if ( $this->getListViewId() !== null ) {
			$pInputModelEmbedCode = $this->_pFormModelBuilderAddress->createInputModelEmbedCode();
			$pFormModelName->addInputModel( $pInputModelEmbedCode );
			$pInputModelButton = $this->_pFormModelBuilderAddress->createInputModelButton();
			$pFormModelName->addInputModel( $pInputModelButton );
		}
	}


	/**
	 *
	 */

	private function addFormModelTemplate()
	{
		$pInputModelTemplate = $this->_pFormModelBuilderAddress->createInputModelTemplate('address');
		$pInputModelShowMap = $this->_pFormModelBuilderAddress->createInputModelShowMap();
		$pFormModelLayoutDesign = new FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice-for-wp-websites'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$pFormModelLayoutDesign->addInputModel($pInputModelShowMap);
		$this->addFormModel($pFormModelLayoutDesign);
	}


	/**
	 *
	 */

	private function addFormModelRecordsFilter()
	{
		$pInputModelFilter = $this->_pFormModelBuilderAddress->createInputModelFilter();
		$pInputModelRecordCount = $this->_pFormModelBuilderAddress->createInputModelRecordsPerPage();
		$pFormModelFilterRecords = new FormModel();
		$pFormModelFilterRecords->setPageSlug($this->getPageSlug());
		$pFormModelFilterRecords->setGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$pFormModelFilterRecords->setLabel(__('Filter & Records', 'onoffice-for-wp-websites'));
		$pFormModelFilterRecords->addInputModel($pInputModelFilter);
		$pFormModelFilterRecords->addInputModel($pInputModelRecordCount);
		$this->addFormModel($pFormModelFilterRecords);


	}


	/**
	 * @return void
	 */

	private function addFormModelRecordsSorting() {
		$pInputModelSortBy       = $this->_pFormModelBuilderAddress->createInputModelSortBy
		( onOfficeSDK::MODULE_ADDRESS );
		$pInputModelSortOrder    = $this->_pFormModelBuilderAddress->createInputModelSortOrder();
		$pFormModelFilterRecords = new FormModel();
		$pFormModelFilterRecords->setPageSlug( $this->getPageSlug() );
		$pFormModelFilterRecords->setGroupSlug( self::FORM_VIEW_RECORDS_SORTING );
		$pFormModelFilterRecords->setLabel( __( 'Sorting', 'onoffice-for-wp-websites' ) );
		$pFormModelFilterRecords->addInputModel( $pInputModelSortBy );
		$pFormModelFilterRecords->addInputModel( $pInputModelSortOrder );
		$this->addFormModel( $pFormModelFilterRecords );
	}


	/**
	 *
	 */

	private function addFormModelPictureTypes()
	{
		$pInputModelPictureTypes = $this->_pFormModelBuilderAddress->createInputModelPictureTypes();
		$pInputModelBildWebseite = $this->_pFormModelBuilderAddress->createInputModelBildWebseite();
		$pFormModelPictureTypes = new FormModel();
		$pFormModelPictureTypes->setPageSlug($this->getPageSlug());
		$pFormModelPictureTypes->setGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$pFormModelPictureTypes->setLabel(__('Photo Types', 'onoffice-for-wp-websites'));
		$pFormModelPictureTypes->addInputModel($pInputModelPictureTypes);
		$pFormModelPictureTypes->addInputModel($pInputModelBildWebseite);
		$this->addFormModel($pFormModelPictureTypes);
	}


	/**
	 *
	 */

	protected function generateMetaBoxes()
	{
		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'side');

		$pFormPictureTypes = $this->getFormModelByGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$this->createMetaBoxByForm($pFormPictureTypes, 'side');

		$pFormFilterRecords = $this->getFormModelByGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$this->createMetaBoxByForm($pFormFilterRecords, 'normal');

		$pFormFilterRecords = $this->getFormModelByGroupSlug(self::FORM_VIEW_RECORDS_SORTING);
		$this->createMetaBoxByForm($pFormFilterRecords, 'normal');
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		$this->cleanPreviousBoxes();
		$fieldNames = array_keys($this->readFieldnamesByContent(onOfficeSDK::MODULE_ADDRESS));

		foreach ($fieldNames as $category) {
			$slug = $this->generateGroupSlugByModuleCategory
				(onOfficeSDK::MODULE_ADDRESS, $category);
			$pFormFieldsConfig = $this->getFormModelByGroupSlug($slug);
			if (!is_null($pFormFieldsConfig))
			{
				$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
			}
		}
	}


	/**
	 *
	 * @param array $row
	 * @param stdClass $pResult
	 * @param int $recordId
	 *
	 */

	protected function updateValues(array $row, stdClass $pResult, $recordId = null)
	{
		$type = RecordManagerFactory::TYPE_ADDRESS;
		$result = false;
		$pDummyAddressDetailView = new DataAddressDetailView();

		if ($row[RecordManager::TABLENAME_LIST_VIEW_ADDRESS]['name'] === $pDummyAddressDetailView->getName()) {
			// false / null
			$pResult->result = false;
			$pResult->record_id = null;
			return;
		}

		if (array_key_exists('name', $row[RecordManager::TABLENAME_LIST_VIEW_ADDRESS])) {
			$row[RecordManager::TABLENAME_LIST_VIEW_ADDRESS]['name'] = $this->sanitizeShortcodeName(
				$row[RecordManager::TABLENAME_LIST_VIEW_ADDRESS]['name']);
		}

		if ($recordId != null) {
			$action = RecordManagerFactory::ACTION_UPDATE;
			/* @var $pRecordManagerUpdate RecordManagerUpdateListViewAddress */
			$pRecordManagerUpdate = RecordManagerFactory::createByTypeAndAction($type, $action, $recordId);
			$result = $pRecordManagerUpdate->updateByRow($row[RecordManager::TABLENAME_LIST_VIEW_ADDRESS]);
			$result = $result && $pRecordManagerUpdate->updateRelations($row, $recordId);
		} else {
			$action = RecordManagerFactory::ACTION_INSERT;
			/* @var $pRecordManagerInsert RecordManagerInsertGeneric */
			$pRecordManagerInsert = RecordManagerFactory::createByTypeAndAction($type, $action);

			try {
				$recordId = $pRecordManagerInsert->insertByRow($row);

				$row = $this->addOrderValues($row, RecordManager::TABLENAME_FIELDCONFIG_ADDRESS);
				$row = [
					RecordManager::TABLENAME_FIELDCONFIG_ADDRESS => $this->prepareRelationValues
					(RecordManager::TABLENAME_FIELDCONFIG_ADDRESS, 'listview_address_id', $row, $recordId),
					RecordManager::TABLENAME_FIELDCONFIG_ADDRESS_TRANSLATED_LABELS => $this->prepareRelationValues
					(RecordManager::TABLENAME_FIELDCONFIG_ADDRESS_TRANSLATED_LABELS, 'listview_address_id', $row, $recordId),
				];
				$pRecordManagerInsert->insertAdditionalValues($row);
				$result = true;
			} catch (RecordManagerInsertException $pException) {
				$result = false;
				$recordId = null;
			}
		}

		if ($result) {
			$this->saveCustomLabels($recordId, $row, RecordManager::TABLENAME_FIELDCONFIG_ADDRESS_CUSTOMS_LABELS, RecordManager::TABLENAME_FIELDCONFIG_ADDRESS_TRANSLATED_LABELS);
		}
		$pResult->result = $result;
		$pResult->record_id = $recordId;
	}


	/**
	 *
	 * @param array $row
	 * @return array
	 *
	 */

	protected function setFixedValues(array $row)
	{
		$rowFixedRecordsPerPage = $this->setRecordsPerPage($row, RecordManager::TABLENAME_LIST_VIEW_ADDRESS);
		return $this->addOrderValues($rowFixedRecordsPerPage, RecordManager::TABLENAME_FIELDCONFIG_ADDRESS);
	}


	/**
	 *
	 * @return void
	 * @throws UnknownViewException
	 *
	 */

	protected function validate($recordId = null)
	{
		if ($recordId == null) {
			return;
		}

		$pRecordReadManager = new RecordManagerReadListViewAddress();
		$values = $pRecordReadManager->getRowById($recordId);

		if (count($values) === 0) {
			throw new UnknownViewException;
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
		$pBoolToFieldList = new BooleanValueToFieldList(new InputModelDBFactoryConfigAddress, $pValues);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigAddress::INPUT_FIELD_FILTERABLE);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigAddress::INPUT_FIELD_HIDDEN);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigAddress::INPUT_FIELD_CONVERT_INPUT_TEXT_TO_SELECT_FOR_FIELD);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEnqueueData(): array
	{
		return array(
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The address list was saved.', 'onoffice-for-wp-websites'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the list. Please make sure the name of the list is unique.', 'onoffice-for-wp-websites'),
			self::ENQUEUE_DATA_MERGE => array(AdminPageSettingsBase::POST_RECORD_ID),
			AdminPageSettingsBase::POST_RECORD_ID => $this->getListViewId(),
			self::VIEW_UNSAVED_CHANGES_MESSAGE => __('Your changes have not been saved yet! Do you want to leave the page without saving?', 'onoffice-for-wp-websites'),
			self::VIEW_LEAVE_WITHOUT_SAVING_TEXT => __('Leave without saving', 'onoffice-for-wp-websites'),
			self::CUSTOM_LABELS => $this->readCustomLabels(),
			'label_custom_label' => __('Custom Label: %s', 'onoffice-for-wp-websites'),
		);
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		parent::doExtraEnqueues();
		wp_enqueue_script('oo-checkbox-js');
		wp_localize_script('oo-sanitize-shortcode-name', 'shortcode', ['name' => 'oopluginlistviewsaddress-name']);
		wp_enqueue_script('oo-sanitize-shortcode-name');
		wp_enqueue_script( 'oo-copy-shortcode');
		wp_register_script('onoffice-custom-form-label-js',
			plugin_dir_url(ONOFFICE_PLUGIN_DIR.'/index.php').'dist/onoffice-custom-form-label.min.js', ['onoffice-multiselect'], '', true);
		wp_enqueue_script('onoffice-custom-form-label-js');
        $pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';
        wp_register_script('onoffice-multiselect', plugins_url('dist/onoffice-multiselect.min.js', $pluginPath));
        wp_register_style('onoffice-multiselect', plugins_url('css/onoffice-multiselect.css', $pluginPath));
        wp_enqueue_script('onoffice-multiselect');
        wp_enqueue_style('onoffice-multiselect');
	}

	/**
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	private function readCustomLabels(): array
	{
		$result = [];
		/** @var CustomLabelRead $pCustomLabelRead*/
		$pCustomLabelRead = $this->getContainer()->get(CustomLabelRead::class);
		$pLanguage = $this->getContainer()->get(Language::class);
		$currentLocale = $pLanguage->getLocale();
		if (is_null($this->_pFieldsCollection)) {
			return [];
		}

		foreach (array_chunk($this->_pFieldsCollection->getAllFields(), 100) as $pField) {
			$pCustomLabelModel = $pCustomLabelRead->getCustomLabelsFieldsForAdmin
			((int)$this->getListViewId(), $pField, $currentLocale, RecordManager::TABLENAME_FIELDCONFIG_ADDRESS_CUSTOMS_LABELS, RecordManager::TABLENAME_FIELDCONFIG_ADDRESS_TRANSLATED_LABELS);
			if (count($pCustomLabelModel)) $result = array_merge($result, $pCustomLabelModel);
		}

		return $result;
	}

	/**
	 * @param int $recordId
	 * @param array $row
	 * @param string $pCustomsLabelConfigurationField
	 * @param string $pTranslateLabelConfigurationField
	 * @throws CustomLabelDeleteException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */

	private function saveCustomLabels(int $recordId, array $row ,string $pCustomsLabelConfigurationField, string $pTranslateLabelConfigurationField)
	{
		$fields = $row[RecordManager::TABLENAME_FIELDCONFIG_ADDRESS] ?? [];
		$fieldNamesSelected = array_column($fields, 'fieldname');

		foreach ($fieldNamesSelected as $key => $name) {
			if (!$this->_pFieldsCollection->containsFieldByModule(onOfficeSDK::MODULE_ADDRESS, $name)) {
				unset($fieldNamesSelected[$key]);
				unset($row['oo_plugin_fieldconfig_address_translated_labels'][$name]);
			}
		}
		/** @var FieldsCollectionBuilderFromNamesForm $pFieldsCollectionBuilder */
		$pFieldsCollectionBuilder = $this->getContainer()->get(FieldsCollectionBuilderFromNamesForm::class);
		$pFieldsCollectionCurrent = $pFieldsCollectionBuilder->buildFieldsCollectionFromBaseCollection
		($fieldNamesSelected, $this->_pFieldsCollection);

		/** @var CustomLabelDelete $pCustomLabelDelete */
		$pCustomLabelDelete = $this->getContainer()->get(CustomLabelDelete::class);
		$pCustomLabelDelete->deleteByFormIdAndFieldNames($recordId, $fieldNamesSelected, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField);

		$pCustomLabelSave = $this->getContainer()->get(CustomLabelRowSaver::class);
		$pCustomLabelSave->saveCustomLabels($recordId,
			$row['oo_plugin_fieldconfig_address_translated_labels'] ?? [], $pFieldsCollectionCurrent, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField);
	}

	/**
	 * @param array $row
	 * @return bool
	 */
	protected function checkFixedValues($row)
	{
		$table = RecordManager::TABLENAME_LIST_VIEW_ADDRESS;
		$result = isset($row[$table]['name']) && !empty(trim($row[$table]['name']));

		return $result;
	}
}
