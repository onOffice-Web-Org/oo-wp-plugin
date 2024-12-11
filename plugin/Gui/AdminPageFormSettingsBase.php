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
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderFromNamesForm;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\Collection\FieldsCollectionConfiguratorForm;
use onOffice\WPlugin\Field\Collection\FieldsCollectionToContentFieldLabelArrayConverter;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelDelete;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelRead;
use onOffice\WPlugin\Field\CustomLabel\Exception\CustomLabelDeleteException;
use onOffice\WPlugin\Field\CustomLabel\ModelToOutputConverter\CustomLabelRowSaver;
use onOffice\WPlugin\Field\DefaultValue\DefaultValueDelete;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueModelToOutputConverter;
use onOffice\WPlugin\Field\DefaultValue\ModelToOutputConverter\DefaultValueRowSaver;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\Form;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Model\FormModel;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilder;
use onOffice\WPlugin\Model\FormModelBuilder\FormModelBuilderDBForm;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigForm;
use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Model\InputModelDBAdapterRow;
use onOffice\WPlugin\Record\BooleanValueToFieldList;
use onOffice\WPlugin\Record\RecordManager;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerInsertException;
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Renderer\InputModelRenderer;
use onOffice\WPlugin\Translation\ModuleTranslation;
use onOffice\WPlugin\Types\FieldsCollection;
use onOffice\WPlugin\Types\FieldTypes;
use onOffice\WPlugin\WP\InstalledLanguageReader;
use stdClass;
use function __;
use function esc_sql;
use function wp_enqueue_script;
use function esc_attr;

/**
 *
 */

abstract class AdminPageFormSettingsBase
	extends AdminPageSettingsBase
{
	/** */
	const FORM_VIEW_LAYOUT_DESIGN = 'viewlayoutdesign';

	/** */
	const FORM_VIEW_FORM_SPECIFIC = 'viewformspecific';

	/** */
	const FORM_VIEW_FORM_ACTIVITYCONFIG = 'viewformactivityconfig';

	/** */
	const MODULE_LABELS = 'modulelabels';

	/** */
	const FIELD_MODULE = 'fieldmodule';

	/** */
	const FIELD_MULTISELECT_EDIT_VALUES = 'field_multiselect_edit_values';

	/** */
	const GET_PARAM_TYPE = 'type';

	/** */
	const DEFAULT_VALUES = 'defaultvalues';

	/** */
	const CUSTOM_LABELS = 'customlabels';

	/** */
	const FORM_VIEW_TASKCONFIG = 'viewtaskconfig';

	/** @var bool */
	private $_showEstateFields = false;

	/** @var bool */
	private $_showAddressFields = false;

	/** @var bool */
	private $_showSearchCriteriaFields = false;

	/** @var array */
	private $_sortableFieldModules = [];

	/** @var string */
	private $_type = null;

	/** @var FormModelBuilderDBForm */
	private $_pFormModelBuilder = null;

	/** @var bool message field has no module */
	private $_showMessageInput = false;

	/**
	 * @param string $pageSlug
	 * @throws Exception
	 */

	public function __construct($pageSlug)
	{
		$this->setPageTitle(__('Edit Form', 'onoffice-for-wp-websites'));
		parent::__construct($pageSlug);
	}


	/**
	 *
	 * @param array $row
	 * @return bool
	 *
	 */

	protected function checkFixedValues($row)
	{
		$table = RecordManager::TABLENAME_FORMS;
		$result = isset($row[$table]['name']) && !empty(trim($row[$table]['name']));

		return $result;
	}


	/**
	 *
	 * @param int $recordId
	 * @throws UnknownFormException
	 *
	 */

	protected function validate($recordId = 0)
	{
		if ((int)$recordId === 0) {
			return;
		}

		$pRecordReadManager = new RecordManagerReadForm();
		$pWpDb = $pRecordReadManager->getWpdb();
		$prefix = $pRecordReadManager->getTablePrefix();
		$value = $pWpDb->get_var('SELECT form_id FROM `'.esc_sql($prefix)
			.'oo_plugin_forms` WHERE `form_id` = "'.esc_sql($recordId).'" AND '
			.'`form_type` = "'.esc_sql($this->getType()).'"');

		if ($value != (int)$recordId) {
			throw new UnknownFormException;
		}
	}


	/**
	 *
	 * Since checkbox are only being submitted if checked they need to be reorganized
	 *
	 * @param stdClass $pValues
	 *
	 */

	protected function prepareValues(stdClass $pValues)
	{
		$pBoolToFieldList = new BooleanValueToFieldList(new InputModelDBFactoryConfigForm, $pValues);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigForm::INPUT_FORM_REQUIRED);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigForm::INPUT_FORM_HIDDEN_FIELD);
	}

	/**
	 *
	 * @param array $row
	 * @param stdClass $pResult
	 * @param int $recordId
	 *
	 * @throws Exception
	 */

	protected function updateValues(array $row, stdClass $pResult, $recordId = null)
	{
		$result = false;
		$type = RecordManagerFactory::TYPE_FORM;

		if (array_key_exists('name', $row[RecordManager::TABLENAME_FORMS])) {
			$row[RecordManager::TABLENAME_FORMS]['name'] = $this->sanitizeShortcodeName(
				$row[RecordManager::TABLENAME_FORMS]['name']);
		}
		
		if (array_key_exists(RecordManager::TABLENAME_ACTIVITY_CONFIG_FORM, $row) && !empty($row[RecordManager::TABLENAME_ACTIVITY_CONFIG_FORM])) {
			$row[RecordManager::TABLENAME_ACTIVITY_CONFIG_FORM] = $this->convertCharacteristicActivityConfigData($row);
		}

		if ($recordId != 0) {
			$action = RecordManagerFactory::ACTION_UPDATE;
			// update by row
			$pRecordManagerUpdateForm = RecordManagerFactory::createByTypeAndAction($type, $action, $recordId);
			$result = $pRecordManagerUpdateForm->updateByRow($row[RecordManager::TABLENAME_FORMS]);

			if (array_key_exists(RecordManager::TABLENAME_FIELDCONFIG_FORMS, $row)) {
				$result = $result && $pRecordManagerUpdateForm->updateFieldConfigByRow
					($row[RecordManager::TABLENAME_FIELDCONFIG_FORMS]);
			}

			if (array_key_exists(RecordManager::TABLENAME_CONTACT_TYPES, $row)) {
				$result = $result && $pRecordManagerUpdateForm->updateContactTypeByRow($row[RecordManager::TABLENAME_CONTACT_TYPES]);
			}

			if (array_key_exists(RecordManager::TABLENAME_ACTIVITY_CONFIG_FORM, $row)) {
				$result = $result && $pRecordManagerUpdateForm->updateActivityConfigByRow
					($row[RecordManager::TABLENAME_ACTIVITY_CONFIG_FORM]);
			}

			if (array_key_exists(RecordManager::TABLENAME_TASKCONFIG_FORMS, $row)) {
				$result = $result && $pRecordManagerUpdateForm->updateTasksConfigByRow($row[RecordManager::TABLENAME_TASKCONFIG_FORMS]);
			}
		} else {
			$action = RecordManagerFactory::ACTION_INSERT;
			// insert
			$pRecordManagerInsertForm = RecordManagerFactory::createByTypeAndAction($type, $action);

			try {
				$recordId = $pRecordManagerInsertForm->insertByRow($row);

				$rowFieldConfig = $this->addOrderValues($row, RecordManager::TABLENAME_FIELDCONFIG_FORMS);
				$rowFieldConfig = $this->prepareRelationValues
					(RecordManager::TABLENAME_FIELDCONFIG_FORMS, 'form_id', $row, $recordId);
				$row[RecordManager::TABLENAME_FIELDCONFIG_FORMS] = $rowFieldConfig;
				$row[RecordManager::TABLENAME_CONTACT_TYPES] =
					$this->prepareRelationValues(RecordManager::TABLENAME_CONTACT_TYPES, 'form_id', $row, $recordId);
				$row[RecordManager::TABLENAME_TASKCONFIG_FORMS]['form_id'] = $recordId;
				$pRecordManagerInsertForm->insertSingleRow($row, RecordManager::TABLENAME_TASKCONFIG_FORMS);
				$row[RecordManager::TABLENAME_ACTIVITY_CONFIG_FORM]['form_id'] = $recordId;
				$pRecordManagerInsertForm->insertSingleRow($row, RecordManager::TABLENAME_ACTIVITY_CONFIG_FORM);
				$pRecordManagerInsertForm->insertAdditionalValues($row);
				$result = true;
			} catch (RecordManagerInsertException $pException) {
				$result = false;
				$recordId = null;
			}
		}

		if ($result) {
			$this->saveDefaultValues($recordId, $row);
			$this->saveCustomLabels($recordId, $row, RecordManager::TABLENAME_FIELDCONFIG_FORM_CUSTOMS_LABELS, RecordManager::TABLENAME_FIELDCONFIG_FORM_TRANSLATED_LABELS);
		}

		$pResult->result = $result;
		$pResult->record_id = $recordId;
	}

	/**
	 * @param array $row
	 * @return array
	 */
	private function convertCharacteristicActivityConfigData(array $row): array
	{
		$rowActivityConfig = $row[RecordManager::TABLENAME_ACTIVITY_CONFIG_FORM];
		$data = [];
		foreach ($rowActivityConfig as $key => $value) {
			if (is_array($value)) {
				$data[] = $value['characteristic'];
				unset($rowActivityConfig[$key]);
			}
		}
		$rowActivityConfig['characteristic'] = implode(',', $data);

		return $rowActivityConfig;
	}

	/**
	 *
	 * @param array $row
	 * @param string $table
	 * @return array
	 *
	 */

	protected function addOrderValues(array $row, $table)
	{
		if (array_key_exists($table, $row)) {
			if ( $table == RecordManager::TABLENAME_FIELDCONFIG_FORMS && $this->getType() !== Form::TYPE_APPLICANT_SEARCH ) {
				unset( $row[ RecordManager::TABLENAME_FIELDCONFIG_FORMS ]['availableOptions'] );
			}
			array_walk($row[$table], function (&$value, $key) {
				$value['order'] = (int)$key + 1;
			});
		}
		return $row;
	}

	/**
	 *
	 * @param stdClass $pValues
	 *
	 */

	protected function customFontMarkdown(stdClass $pValues)
	{
		$pBoolToFieldList = new BooleanValueToFieldList(new InputModelDBFactoryConfigForm, $pValues);
		$pBoolToFieldList->fillCheckboxValues(InputModelDBFactoryConfigForm::INPUT_FORM_MARK_DOWN);
	}


	/**
	 *
	 * @param array $row
	 * @return array
	 *
	 */

	protected function setFixedValues(array $row)
	{
		$row = $this->addOrderValues($row, RecordManager::TABLENAME_FIELDCONFIG_FORMS);
		$row[RecordManager::TABLENAME_FORMS]['form_type'] = $this->getType();

		return $row;
	}

	/**
	 *
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function getEnqueueData(): array
	{
		/** @var Language $pInstalledLanguageReader */
		$pLanguage = $this->getContainer()->get(Language::class);
		return [
			self::GET_PARAM_TYPE => $this->getType(),
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The Form was saved.', 'onoffice-for-wp-websites'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the form. Please make '
				.'sure the name of the form is unique.', 'onoffice-for-wp-websites'),
			self::ENQUEUE_DATA_MERGE => [
				AdminPageSettingsBase::POST_RECORD_ID,
				self::GET_PARAM_TYPE,
			],
			AdminPageSettingsBase::POST_RECORD_ID => (int)$this->getListViewId(),
			self::MODULE_LABELS => ModuleTranslation::getAllLabelsSingular(),
			/* translators: %s is a translated module name */
			self::FIELD_MODULE => __('Module: %s', 'onoffice-for-wp-websites'),
			self::FIELD_MULTISELECT_EDIT_VALUES => __('Edit Values', 'onoffice-for-wp-websites'),
			self::DEFAULT_VALUES => $this->readDefaultValues(),
			self::CUSTOM_LABELS => $this->readCustomLabels(),
			'label_add_language' => __('Add Language', 'onoffice-for-wp-websites'),
			'label_choose_language' => __('Choose Language', 'onoffice-for-wp-websites'),
			/* translators: %s is the name of a language */
			'label_default_value' => __('Default Value: %s', 'onoffice-for-wp-websites'),
			'label_custom_label' => __('Custom Label: %s', 'onoffice-for-wp-websites'),
			'label_default_value_from' => __('Default Value From:', 'onoffice-for-wp-websites'),
			'label_default_value_up_to' => __('Default Value Up To:', 'onoffice-for-wp-websites'),
			'fieldList' => $this->getFieldList(),
			'installed_wp_languages' => $this->getInstalledLanguages(),
			'language_native' => $pLanguage->getLocale(),
			self::VIEW_UNSAVED_CHANGES_MESSAGE => __('Your changes have not been saved yet! Do you want to leave the page without saving?', 'onoffice-for-wp-websites'),
			self::VIEW_LEAVE_WITHOUT_SAVING_TEXT => __('Leave without saving', 'onoffice-for-wp-websites'),
		];
	}

	/**
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getInstalledLanguages(): array
	{
		/** @var InstalledLanguageReader $pInstalledLanguageReader */
		$pInstalledLanguageReader = $this->getContainer()->get(InstalledLanguageReader::class);
		return $pInstalledLanguageReader->readAvailableLanguageNamesUsingNativeName();
	}

	/**
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	private function getFieldList(): array
	{
		$result = [];
		foreach ($this->buildFieldsCollectionForCurrentForm()->getAllFields() as $pField) {
			$result[$pField->getModule()][$pField->getName()] = $pField->getAsRow();
			if ($pField->getType() === FieldTypes::FIELD_TYPE_BOOLEAN) {
				$result[$pField->getModule()][$pField->getName()]['permittedvalues'] = [
					'0' => __('No', 'onoffice-for-wp-websites'),
					'1' => __('Yes', 'onoffice-for-wp-websites'),
				];
			} elseif ($pField->getType() === FieldTypes::FIELD_TYPE_DATATYPE_TINYINT) {
				$result[ $pField->getModule() ][ $pField->getName() ]['permittedvalues'] = [
					'' => __('Not Specified', 'onoffice-for-wp-websites'),
					'0' => __('No', 'onoffice-for-wp-websites'),
					'1' => __('Yes', 'onoffice-for-wp-websites'),
				];
			}
		}
		return $result;
	}

	/**
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */
	private function readDefaultValues(): array
	{
		$result = [];
		/** @var DefaultValueModelToOutputConverter $pDefaultValueConverter */
		$pDefaultValueConverter = $this->getContainer()->get(DefaultValueModelToOutputConverter::class);

		foreach (array_chunk($this->buildFieldsCollectionForCurrentForm()->getAllFields(), 100) as $pField) {
			$pDefaultFields = $pDefaultValueConverter->getConvertedMultiFieldsForAdmin((int) $this->getListViewId(), $pField);
			if (count($pDefaultFields)) $result = array_merge($result, $pDefaultFields);
		}
		return $result;
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

		foreach (array_chunk($this->buildFieldsCollectionForCurrentForm()->getAllFields(), 100) as $pField) {
			$pCustomLabelModel = $pCustomLabelRead->getCustomLabelsFieldsForAdmin
			((int)$this->getListViewId(), $pField, $currentLocale, RecordManager::TABLENAME_FIELDCONFIG_FORM_CUSTOMS_LABELS, RecordManager::TABLENAME_FIELDCONFIG_FORM_TRANSLATED_LABELS);

			if (count($pCustomLabelModel)) $result = array_merge($result, $pCustomLabelModel);
		}

		return $result;
	}


	/**
	 *
	 * Call this first in overriding class
	 *
	 */

	protected function buildForms()
	{
		$this->_pFormModelBuilder = $this->getContainer()->get(FormModelBuilderDBForm::class);
		$this->_pFormModelBuilder->setFormType($this->getType());
		$pFormModel = $this->_pFormModelBuilder->generate($this->getPageSlug(), $this->getListViewId());
		$this->addFormModel($pFormModel);

		$pInputModelName = $this->_pFormModelBuilder->createInputModelName();
		$pFormModelName = new FormModel();
		$pFormModelName->setPageSlug($this->getPageSlug());
		$pFormModelName->setGroupSlug(self::FORM_RECORD_NAME);
		$pFormModelName->setLabel(__('choose name', 'onoffice-for-wp-websites'));
		$pFormModelName->addInputModel($pInputModelName);

		$pInputModelType = $this->_pFormModelBuilder->createInputModelFormType();
		$pFormModelName->addInputModel($pInputModelType);

		if ($this->getListViewId() !== null) {
			$pInputModelEmbedCode = $this->_pFormModelBuilder->createInputModelEmbedCode();
			$pFormModelName->addInputModel($pInputModelEmbedCode);
			$pInputModelButton = $this->_pFormModelBuilder->createInputModelButton();
			$pFormModelName->addInputModel($pInputModelButton);
		}

		$this->addFormModel($pFormModelName);

		$pInputModelTemplate = $this->_pFormModelBuilder->createInputModelTemplate('form');
		$pFormModelLayoutDesign = new FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice-for-wp-websites'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$this->addFormModel($pFormModelLayoutDesign);
	}


	/**
	 *
	 */

	protected function generateMetaBoxes()
	{
		$pFormLayoutDesign = $this->getFormModelByGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$this->createMetaBoxByForm($pFormLayoutDesign, 'normal');
	}


	/**
	 *
	 */

	protected function generateAccordionBoxes()
	{
		$this->cleanPreviousBoxes();
		$pDefaultFieldsCollection = $this->buildFieldsCollectionForCurrentForm();
		$pFieldsCollectionConverter = new FieldsCollectionToContentFieldLabelArrayConverter();

		foreach ($this->getCurrentFormModules() as $module) {
			$fieldNames = $pFieldsCollectionConverter->convert($pDefaultFieldsCollection, $module);

			foreach (array_keys($fieldNames) as $category) {
				$slug = $this->generateGroupSlugByModuleCategory($module, $category);
				$pFormFieldsConfig = $this->getFormModelByGroupSlug($slug);
				if (!is_null($pFormFieldsConfig)) {
					$pFormFieldsConfig->setOoModule($module);
					$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
				}
			}
		}
	}

	/**
	 *
	 * @return FieldsCollection
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */

	private function buildFieldsCollectionForCurrentForm(): FieldsCollection
	{
		$pFieldsCollectionBuilder = $this->getContainer()->get(FieldsCollectionBuilderShort::class);
		$pDefaultFieldsCollection = new FieldsCollection();
		$modules = $this->getCurrentFormModules();

		if (in_array(onOfficeSDK::MODULE_ADDRESS, $modules) ||
			in_array(onOfficeSDK::MODULE_ESTATE, $modules)) {
			$pFieldsCollectionBuilder->addFieldsAddressEstate($pDefaultFieldsCollection);
		}

		if (in_array(onOfficeSDK::MODULE_SEARCHCRITERIA, $modules)) {
			$pFieldsCollectionBuilder
				->addFieldsSearchCriteria($pDefaultFieldsCollection)
				->addFieldsSearchCriteriaSpecificBackend($pDefaultFieldsCollection)
				->addFieldSupervisorForSearchCriteria($pDefaultFieldsCollection);
		}

		$pFieldsCollectionBuilder->addFieldsFormBackend($pDefaultFieldsCollection,$this->getType());

		foreach ($pDefaultFieldsCollection->getAllFields() as $pField) {
			if (!in_array($pField->getModule(), $modules, true)) {
				$pDefaultFieldsCollection->removeFieldByModuleAndName
					($pField->getModule(), $pField->getName());
			}
		}

		/** @var FieldsCollectionConfiguratorForm $pFieldsCollectionConfiguratorForm */
		$pFieldsCollectionConfiguratorForm = $this->getContainer()->get(FieldsCollectionConfiguratorForm::class);
		return $pFieldsCollectionConfiguratorForm->buildForFormType($pDefaultFieldsCollection, $this->getType());
	}

	/**
	 *
	 * Call this in method `buildForms` of overriding class
	 *
	 * Don't forget to call
	 * <code>
	 *        $this->addSortableFieldsList($this->getSortableFieldModules(), $pFormModelBuilder,
	 *            InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST_FORM);
	 * </code>
	 * afterwards.
	 *
	 * @param FormModelBuilder $pFormModelBuilder
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws UnknownFieldException
	 */

	protected function addFieldConfigurationForMainModules(FormModelBuilder $pFormModelBuilder)
	{
		$specificFieldText = __('Special Fields', 'onoffice-for-wp-websites');
		$pFieldsCollection = $this->buildFieldsCollectionForCurrentForm();
		$specificFields = [
				$specificFieldText => [],
		];

		foreach ($this->getCurrentFormModules() as $module) {
			$fieldNames = $this->addFieldConfigurationByModule
				($pFormModelBuilder, $pFieldsCollection, $module);
			if (isset($fieldNames[ $specificFieldText ])) {
				$specificFields[ $specificFieldText ] = array_merge($specificFields[ $specificFieldText ], $fieldNames[ $specificFieldText ]);
				unset($fieldNames[ $specificFieldText ]);
			}
			$this->addFieldsConfiguration($module, $pFormModelBuilder, $fieldNames, true);
			$this->addSortableFieldModule($module);
		}
		$this->addFieldsConfiguration('', $pFormModelBuilder, $specificFields, true);
	}


	/**
	 *
	 * @param FormModelBuilder $pFormModelBuilder
	 * @param FieldsCollection $pFieldsCollection
	 * @param string $module
	 *
	 */

	private function addFieldConfigurationByModule(
		FormModelBuilder $pFormModelBuilder, FieldsCollection $pFieldsCollection, string $module)
	{
		$pFieldsCollectionConverter = new FieldsCollectionToContentFieldLabelArrayConverter();
		return $pFieldsCollectionConverter->convert($pFieldsCollection, $module);

	}

	/**
	 *
	 * @param string $name
	 * @param FieldsCollection $pFieldsCollection
	 * @return string
	 */

	private function getModule(string $name, FieldsCollection $pFieldsCollection): string
	{
		try {
			return $pFieldsCollection->getFieldByKeyUnsafe($name)->getModule();
		} catch (UnknownFieldException $pEx) {
			return '';
		}
	}

	/**
	 * @param int $recordId
	 * @param array $row
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */

	private function saveDefaultValues(int $recordId, array $row)
	{
		$fields = $row[RecordManager::TABLENAME_FIELDCONFIG_FORMS] ?? [];
		$fieldNamesSelected = array_column($fields, 'fieldname');
		$pFieldsCollectionBase = $this->buildFieldsCollectionForCurrentForm();

		foreach ($fieldNamesSelected as $key => $name) {
			$module = $this->getModule($name, $pFieldsCollectionBase);
			if (!$pFieldsCollectionBase->containsFieldByModule($module, $name)) {
				unset($fieldNamesSelected[$key]);
				unset($row['oo_plugin_fieldconfig_form_defaults_values'][$name]);
			}
		}
		/** @var FieldsCollectionBuilderFromNamesForm $pFieldsCollectionBuilder */
		$pFieldsCollectionBuilder = $this->getContainer()->get(FieldsCollectionBuilderFromNamesForm::class);
		$pFieldsCollectionCurrent = $pFieldsCollectionBuilder->buildFieldsCollectionFromBaseCollection
			($fieldNamesSelected, $pFieldsCollectionBase);

		/** @var DefaultValueDelete $pDefaultValueDelete */
		$pDefaultValueDelete = $this->getContainer()->get(DefaultValueDelete::class);
		$pDefaultValueDelete->deleteByFormIdAndFieldNames($recordId, $fieldNamesSelected);

		$pDefaultValueSave = $this->getContainer()->get(DefaultValueRowSaver::class);
		$pDefaultValueSave->saveDefaultValues($recordId,
			$row['oo_plugin_fieldconfig_form_defaults_values'] ?? [], $pFieldsCollectionCurrent);
	}


	/**
	 * @param int $recordId
	 * @param array $row
	 * @throws CustomLabelDeleteException
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws RecordManagerInsertException
	 * @throws UnknownFieldException
	 */

	private function saveCustomLabels(int $recordId, array $row ,string $pCustomsLabelConfigurationField, string $pTranslateLabelConfigurationField)
	{
		$fields = $row[RecordManager::TABLENAME_FIELDCONFIG_FORMS] ?? [];
		$fieldNamesSelected = array_column($fields, 'fieldname');
		$pFieldsCollectionBase = $this->buildFieldsCollectionForCurrentForm();

		foreach ($fieldNamesSelected as $key => $name) {
			$module = $this->getModule($name, $pFieldsCollectionBase);
			if (!$pFieldsCollectionBase->containsFieldByModule($module, $name)) {
				unset($fieldNamesSelected[$key]);
				unset($row['oo_plugin_fieldconfig_form_translated_labels'][$name]);
			}
		}
		/** @var FieldsCollectionBuilderFromNamesForm $pFieldsCollectionBuilder */
		$pFieldsCollectionBuilder = $this->getContainer()->get(FieldsCollectionBuilderFromNamesForm::class);
		$pFieldsCollectionCurrent = $pFieldsCollectionBuilder->buildFieldsCollectionFromBaseCollection
		($fieldNamesSelected, $pFieldsCollectionBase);


		/** @var CustomLabelDelete $pCustomLabelDelete */
		$pCustomLabelDelete = $this->getContainer()->get(CustomLabelDelete::class);
		$pCustomLabelDelete->deleteByFormIdAndFieldNames($recordId, $fieldNamesSelected, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField);

		$pCustomLabelSave = $this->getContainer()->get(CustomLabelRowSaver::class);
		$pCustomLabelSave->saveCustomLabels($recordId,
			$row['oo_plugin_fieldconfig_form_translated_labels'] ?? [], $pFieldsCollectionCurrent, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField);
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		parent::doExtraEnqueues();
		wp_enqueue_script('oo-checkbox-js');
		wp_enqueue_script('onoffice-default-form-values-js');
		wp_enqueue_script('onoffice-custom-form-label-js');
		$pluginPath = ONOFFICE_PLUGIN_DIR.'/index.php';

		wp_register_script('onoffice-multiselect', plugins_url('/dist/onoffice-multiselect.min.js', $pluginPath));
		wp_register_style('onoffice-multiselect', plugins_url('/css/onoffice-multiselect.css', $pluginPath));
		wp_enqueue_script('onoffice-multiselect');
		wp_enqueue_style('onoffice-multiselect');

		wp_localize_script('oo-sanitize-shortcode-name', 'shortcode', ['name' => 'oopluginforms-name']);
		wp_enqueue_script('oo-sanitize-shortcode-name');
		wp_enqueue_script('oo-copy-shortcode');

		if ($this->getType() !== Form::TYPE_APPLICANT_SEARCH) {
			wp_enqueue_script('select2',  plugin_dir_url( ONOFFICE_PLUGIN_DIR . '/index.php' ) . 'vendor/select2/select2/dist/js/select2.min.js');
			wp_enqueue_style('select2',  plugin_dir_url( ONOFFICE_PLUGIN_DIR . '/index.php' ) . 'vendor/select2/select2/dist/css/select2.min.css');
			wp_enqueue_script('onoffice-custom-select',  plugins_url('/dist/onoffice-custom-select.min.js', $pluginPath));
		}
	}


	/**
	 * @return array
	 */

	public function getCurrentFormModules(): array
	{
		// empty module name for `message` field
		$modules = [''];
		if ($this->_showEstateFields) {
			$modules[] = onOfficeSDK::MODULE_ESTATE;
		}

		if ($this->_showAddressFields) {
			$modules[] = onOfficeSDK::MODULE_ADDRESS;
		}

		if ($this->_showSearchCriteriaFields) {
			$modules[] = onOfficeSDK::MODULE_SEARCHCRITERIA;
		}

		return $modules;
	}


	/**
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function renderContent()
	{
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'true' ) {
			echo '<div class="notice notice-success is-dismissible"><p>'
			     . esc_html__( 'The Form was saved.', 'onoffice-for-wp-websites' )
			     . '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'false' ) {
			echo '<div class="notice notice-error is-dismissible"><p>'
			     . esc_html__( 'There was a problem saving the form. Please make '
			                   . 'sure the name of the form is unique.', 'onoffice-for-wp-websites' )
			     . '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'empty' ) {
			echo '<div class="notice notice-error is-dismissible"><p>'
			     . esc_html__( 'There was a problem saving the form. The Name field cannot be empty.', 'onoffice-for-wp-websites' )
			     . '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}

		do_action( 'add_meta_boxes', get_current_screen()->id, null );
		$this->generateMetaBoxes();

		/* @var $pInputModelRenderer InputModelRenderer */
		$pInputModelRenderer     = $this->getContainer()->get( InputModelRenderer::class );
		$pFormViewName           = $this->getFormModelByGroupSlug( self::FORM_RECORD_NAME );
		$pFormViewSortableFields = $this->getFormModelByGroupSlug( self::FORM_VIEW_SORTABLE_FIELDS_CONFIG );
		$pFormViewSearchFieldForFieldLists = $this->getFormModelByGroupSlug(self::FORM_VIEW_SEARCH_FIELD_FOR_FIELD_LISTS_CONFIG);

		$this->generatePageMainTitle( $this->getPageTitle() );
		echo '<form id="onoffice-ajax" action="' . admin_url( 'admin-post.php' ) . '" method="post">';
		echo '<input type="hidden" name="action" value="' . get_current_screen()->id . '" />';
		echo '<input type="hidden" name="record_id" value="' . esc_attr( $_GET['id'] ?? 0 ) . '" />';
		echo '<input type="hidden" name="type" value="' . $this->getType() . '" />';
		wp_nonce_field( get_current_screen()->id, 'nonce' );
		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
		echo '<div id="poststuff" class="oo-poststuff">';
		echo '<div id="post-head-content">';
		$pInputModelRenderer->buildForAjax( $pFormViewName );
		echo '</div>';
		echo '<div id="post-body" class="metabox-holder columns-'
		     . ( 1 == get_current_screen()->get_columns() ? '1' : '2' ) . '">';
		echo '<div class="postbox-container" id="postbox-container-1">';
		do_meta_boxes( get_current_screen()->id, 'normal', null );
		echo '</div>';
		echo '<div class="postbox-container" id="postbox-container-2">';
		do_meta_boxes( get_current_screen()->id, 'side', null );
		do_meta_boxes( get_current_screen()->id, 'advanced', null );
		echo '</div>';
		echo '<div class="clear"></div>';
		$this->renderSearchFieldForFieldLists($pInputModelRenderer, $pFormViewSearchFieldForFieldLists);
		echo '<div class="clear"></div>';
		do_action( 'add_meta_boxes', get_current_screen()->id, null );
		echo '<div style="float:left;">';
		$this->generateAccordionBoxes();
		echo '</div>';
		echo '<div id="listSettings" style="float:left;" class="postbox">';
		do_accordion_sections( get_current_screen()->id, 'side', null );
		echo '</div>';
		echo '<div class="fieldsSortable postbox">';
		echo '<h2 class="hndle ui-sortable-handle"><span>' . __( 'Fields',
				'onoffice-for-wp-websites' ) . '</span></h2>';
		$pInputModelRenderer->buildForAjax( $pFormViewSortableFields );
		echo '</div>';
		echo '<div class="clear"></div>';
		echo '</div>';
		do_settings_sections( $this->getPageSlug() );
		$this->generateBlockPublish();
		echo '</div>';
		echo '</form>';
	}


	/**
	 * @throws UnknownFormException
	 */

	public function save_form()
	{
		$this->buildForms();
		$action   = filter_input( INPUT_POST, 'action' );
		$nonce    = filter_input( INPUT_POST, 'nonce' );
		$recordId = (int) filter_input( INPUT_POST, self::POST_RECORD_ID );
		$this->validate( $recordId );

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die();
		}

		$mainRecordId = $recordId != 0 ? $recordId : null;
		$values = (object) $this->transformPostValues();

		$this->prepareValues( $values );
		$this->customFontMarkdown( $values );
		$pInputModelDBAdapterRow = new InputModelDBAdapterRow();

		foreach ( $this->getFormModels() as $pFormModel ) {
			foreach ( $pFormModel->getInputModel() as $pInputModel ) {
				if ( $pInputModel instanceof InputModelDB ) {
					$identifier = $pInputModel->getIdentifier();
					$value      = isset( $values->$identifier ) ? $values->$identifier : null;
					$pInputModel->setValue( $value );
					$pInputModel->setMainRecordId( $mainRecordId ?? 0 );
					$pInputModelDBAdapterRow->addInputModelDB( $pInputModel );
				}
			}
		}

		$row                      = $pInputModelDBAdapterRow->createUpdateValuesByTable();
		$row                      = $this->setFixedValues( $row );
		$checkResult              = $this->checkFixedValues( $row );
		$pResultObject            = new stdClass();
		$pResultObject->result    = null;
		$pResultObject->record_id = $recordId;

		$row['oo_plugin_fieldconfig_form_defaults_values']   =
			(array) ( $row['oo_plugin_fieldconfig_form_defaults_values']['value'] ?? [] ) +
			(array) ( $values->{'defaultvalue-lang'} ) ?? [];
		$row['oo_plugin_fieldconfig_form_translated_labels'] =
			(array) ( $row['oo_plugin_fieldconfig_form_translated_labels']['value'] ?? [] ) +
			(array) ( $values->{'customlabel-lang'} ) ?? [];

		if ( $checkResult ) {
			$this->updateValues( $row, $pResultObject, $recordId );
		}

		$pageQuery   = str_replace( 'admin_page_', 'page=', $_POST['action'] );
		$typeQuery   = '&type=' . $_POST['type'];
		$statusQuery = '&saved=false';
		if (is_null($pResultObject->result)) {
			$statusQuery = '&saved=empty';
		} else if ($pResultObject->result) {
			$statusQuery = '&saved=true';
		}
		$idQuery     = $pResultObject->record_id ? '&id=' . $pResultObject->record_id : '';

		wp_redirect( admin_url( 'admin.php?' . $pageQuery . $typeQuery . $idQuery . $statusQuery ) );
		die();
	}

	/** @return string */
	public function getType()
		{ return $this->_type; }

	/** @param string $type */
	public function setType(string $type)
		{ $this->_type = $type; }

	/** @return FormModelBuilder */
	protected function getFormModelBuilder()
		{ return $this->_pFormModelBuilder; }

	/** @param string $module */
	protected function addSortableFieldModule($module)
		{ $this->_sortableFieldModules []= $module; }

	/** @return array */
	protected function getSortableFieldModules()
		{ return $this->_sortableFieldModules; }

	/** @param bool $showEstateFields */
	public function setShowEstateFields(bool $showEstateFields)
		{ $this->_showEstateFields = $showEstateFields; }

	/** @param bool $showAddressFields */
	public function setShowAddressFields(bool $showAddressFields)
		{ $this->_showAddressFields = $showAddressFields; }

	/** @param bool $showSearchCriteriaFields */
	public function setShowSearchCriteriaFields(bool $showSearchCriteriaFields)
		{ $this->_showSearchCriteriaFields = $showSearchCriteriaFields; }

	/** @return bool */
	public function getShowMessageInput(): bool
	{ return $this->_showMessageInput; }

	/** @param bool $showMessageInput */
	public function setShowMessageInput(bool $showMessageInput)
	{ $this->_showMessageInput = $showMessageInput; }
}
