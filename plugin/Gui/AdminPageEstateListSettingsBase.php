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

use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\Record\RecordManager;
use onOffice\WPlugin\Record\RecordManagerFactory;
use onOffice\WPlugin\Record\RecordManagerInsertException;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelRead;
use onOffice\WPlugin\Field\CustomLabel\CustomLabelDelete;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderFromNamesForm;
use onOffice\WPlugin\Field\CustomLabel\ModelToOutputConverter\CustomLabelRowSaver;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Types\FieldsCollection;
use stdClass;
use function __;
use onOffice\WPlugin\Field\CustomLabel\Exception\CustomLabelDeleteException;
use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\WP\InstalledLanguageReader;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

abstract class AdminPageEstateListSettingsBase
	extends AdminPageSettingsBase
{
	/** */
	const CUSTOM_LABELS = 'customlabels';

	/**
	 *
	 */

	public function renderContent()
	{
		$this->validate($this->getListViewId());
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'empty' ) {
			echo '<div class="notice notice-error is-dismissible"><p>'
				. esc_html__( 'There was a problem saving the list. The Name field cannot be empty.', 'onoffice-for-wp-websites' )
				. '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}
		if ( isset( $_GET['saved'] ) && $_GET['saved'] === 'false' ) {
			echo '<div class="notice notice-error is-dismissible"><p>'
			     . esc_html__( 'There was a problem saving the view. Please make '
			                   . 'sure the name of the view is unique, even across all estate list types.',
					'onoffice-for-wp-websites' )
			     . '</p><button type="button" class="notice-dismiss notice-save-view"></button></div>';
		}
		parent::renderContent();
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
		$result = false;
		$pDummyDetailView = new DataDetailView();
		$type = RecordManagerFactory::TYPE_ESTATE;

		if ($row[RecordManager::TABLENAME_LIST_VIEW]['name'] === $pDummyDetailView->getName()) {
			// false / null
			$pResult->result = false;
			$pResult->record_id = null;
			return;
		}
		else{
			$row[RecordManager::TABLENAME_LIST_VIEW]['name'] =
				$this->sanitizeShortcodeName($row[RecordManager::TABLENAME_LIST_VIEW]['name']);
		}

		if ($recordId != null) {
			$action = RecordManagerFactory::ACTION_UPDATE;
			$pUpdate = RecordManagerFactory::createByTypeAndAction($type, $action, $recordId);
			$result = $pUpdate->updateByRow($row);
		} else {
			$action = RecordManagerFactory::ACTION_INSERT;
			$pInsert = RecordManagerFactory::createByTypeAndAction($type, $action);

			try {
				$recordId = $pInsert->insertByRow($row);

				$row = $this->addOrderValues($row, RecordManager::TABLENAME_FIELDCONFIG);
				$row = [
					RecordManager::TABLENAME_FIELDCONFIG => $this->prepareRelationValues
						(RecordManager::TABLENAME_FIELDCONFIG, 'listview_id', $row, $recordId),
					RecordManager::TABLENAME_LISTVIEW_CONTACTPERSON => $this->prepareRelationValues
						(RecordManager::TABLENAME_LISTVIEW_CONTACTPERSON, 'listview_id', $row, $recordId),
					RecordManager::TABLENAME_PICTURETYPES => $this->prepareRelationValues
						(RecordManager::TABLENAME_PICTURETYPES, 'listview_id', $row, $recordId),
					RecordManager::TABLENAME_SORTBYUSERVALUES => $this->prepareRelationValues
						(RecordManager::TABLENAME_SORTBYUSERVALUES, 'listview_id', $row, $recordId),
					RecordManager::TABLENAME_FIELDCONFIG_ESTATE_TRANSLATED_LABELS => $this->prepareRelationValues
						(RecordManager::TABLENAME_FIELDCONFIG_ESTATE_TRANSLATED_LABELS, 'listview_id', $row, $recordId),
				];

				$pInsert->insertAdditionalValues($row);
				$result = true;
			} catch (RecordManagerInsertException $pException) {
				$result = false;
				$recordId = null;
			}
		}
		if ($result) {
			$this->saveCustomLabels($recordId, $row, RecordManager::TABLENAME_FIELDCONFIG_ESTATE_CUSTOMS_LABELS, RecordManager::TABLENAME_FIELDCONFIG_ESTATE_TRANSLATED_LABELS);
		}
		$pResult->result = $result;
		$pResult->record_id = $recordId;
	}


	/**
	 *
	 * @param array $row
	 * @return bool
	 *
	 */

	protected function checkFixedValues($row)
	{
		$table = RecordManager::TABLENAME_LIST_VIEW;
		$result = isset($row[$table]['name']) && !empty(trim($row[$table]['name']));

		return $result;
	}


	/**
	 *
	 * @param array $row
	 * @return array
	 *
	 */

	protected function setFixedValues(array $row)
	{
		$rowCleanRecordsPerPage = $this->setRecordsPerPage($row, RecordManager::TABLENAME_LIST_VIEW);
		return $this->addOrderValues($rowCleanRecordsPerPage, RecordManager::TABLENAME_FIELDCONFIG);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEnqueueData(): array
	{
		return array(
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The view has been saved.', 'onoffice-for-wp-websites'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the view. Please make '
				.'sure the name of the view is unique, even across all estate list types.', 'onoffice-for-wp-websites'),
			self::ENQUEUE_DATA_MERGE => array(AdminPageSettingsBase::POST_RECORD_ID),
			self::CUSTOM_LABELS => $this->readCustomLabels(),
			'label_custom_label' => __('Custom Label: %s', 'onoffice-for-wp-websites'),
			AdminPageSettingsBase::POST_RECORD_ID => $this->getListViewId(),
			self::VIEW_UNSAVED_CHANGES_MESSAGE => __('Your changes have not been saved yet! Do you want to leave the page without saving?', 'onoffice-for-wp-websites'),
			self::VIEW_LEAVE_WITHOUT_SAVING_TEXT => __('Leave without saving', 'onoffice-for-wp-websites'),
		);
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

		foreach (array_chunk($this->buildFieldsCollectionForCurrentEstate()->getAllFields(), 100) as $pField) {
			$pCustomLabelModel = $pCustomLabelRead->getCustomLabelsFieldsForAdmin
			((int)$this->getListViewId(), $pField, $currentLocale, RecordManager::TABLENAME_FIELDCONFIG_ESTATE_CUSTOMS_LABELS, RecordManager::TABLENAME_FIELDCONFIG_ESTATE_TRANSLATED_LABELS);
			if (count($pCustomLabelModel)) $result = array_merge($result, $pCustomLabelModel);
		}

		return $result;
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
		$pFieldsCollectionBuilder = $this->getContainer()->get(FieldsCollectionBuilderShort::class);
		$pDefaultFieldsCollection = new FieldsCollection();
		$pFieldsCollectionBuilder->addFieldsAddressEstate( $pDefaultFieldsCollection )
		                         ->addFieldsEstateGeoPosisionBackend( $pDefaultFieldsCollection )
		                         ->addFieldsEstateDecoratorReadAddressBackend( $pDefaultFieldsCollection );

		foreach ($pDefaultFieldsCollection->getAllFields() as $pField) {
			if (!in_array($pField->getModule(), [onOfficeSDK::MODULE_ESTATE], true)) {
				$pDefaultFieldsCollection->removeFieldByModuleAndName
					($pField->getModule(), $pField->getName());
			}
			
		}
		
		return $pDefaultFieldsCollection;
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
		$fields = $row[RecordManager::TABLENAME_FIELDCONFIG] ?? [];
		$fieldNamesSelected = array_column($fields, 'fieldname');
		$pFieldsCollectionBase = $this->buildFieldsCollectionForCurrentEstate();

		foreach ($fieldNamesSelected as $key => $name) {
			if (!$pFieldsCollectionBase->containsFieldByModule(onOfficeSDK::MODULE_ESTATE, $name)) {
				unset($fieldNamesSelected[$key]);
				unset($row['oo_plugin_fieldconfig_estate_translated_labels'][$name]);
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
			$row['oo_plugin_fieldconfig_estate_translated_labels'] ?? [], $pFieldsCollectionCurrent, $pCustomsLabelConfigurationField, $pTranslateLabelConfigurationField);
	}
}
