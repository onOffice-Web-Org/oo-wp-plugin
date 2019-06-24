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
use onOffice\WPlugin\Record\RecordManagerInsertGeneric;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use onOffice\WPlugin\Record\RecordManagerUpdateListViewAddress;
use stdClass;
use function __;

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

	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		parent::__construct($pageSlug);
		$this->setPageTitle(__('Edit Address List', 'onoffice'));
	}


	/**
	 *
	 */

	protected function buildForms()
	{
		$this->_pFormModelBuilderAddress = new FormModelBuilderDBAddress($this->getPageSlug());
		$pFormModel = $this->_pFormModelBuilderAddress->generate($this->getListViewId());
		$this->addFormModel($pFormModel);

		$fieldNames = $this->readFieldnamesByContent(onOfficeSDK::MODULE_ADDRESS);

		$this->addFieldsConfiguration(onOfficeSDK::MODULE_ADDRESS, $this->_pFormModelBuilderAddress, $fieldNames);
		$this->addSortableFieldsList(array(onOfficeSDK::MODULE_ADDRESS), $this->_pFormModelBuilderAddress,
			InputModelBase::HTML_TYPE_COMPLEX_SORTABLE_DETAIL_LIST);

		$this->addFormModelName();
		$this->addFormModelPictureTypes();
		$this->addFormModelTemplate();
		$this->addFormModelRecordsFilter();
	}


	/**
	 *
	 */

	private function addFormModelName()
	{
		$pInputModelName = $this->_pFormModelBuilderAddress->createInputModelName();
		$pFormModelName = new FormModel();
		$pFormModelName->setPageSlug($this->getPageSlug());
		$pFormModelName->setGroupSlug(self::FORM_RECORD_NAME);
		$pFormModelName->setLabel(__('choose name', 'onoffice'));
		$pFormModelName->addInputModel($pInputModelName);
		$this->addFormModel($pFormModelName);
	}


	/**
	 *
	 */

	private function addFormModelTemplate()
	{
		$pInputModelTemplate = $this->_pFormModelBuilderAddress->createInputModelTemplate('address');
		$pFormModelLayoutDesign = new FormModel();
		$pFormModelLayoutDesign->setPageSlug($this->getPageSlug());
		$pFormModelLayoutDesign->setGroupSlug(self::FORM_VIEW_LAYOUT_DESIGN);
		$pFormModelLayoutDesign->setLabel(__('Layout & Design', 'onoffice'));
		$pFormModelLayoutDesign->addInputModel($pInputModelTemplate);
		$this->addFormModel($pFormModelLayoutDesign);
	}


	/**
	 *
	 */

	private function addFormModelRecordsFilter()
	{
		$pInputModelFilter = $this->_pFormModelBuilderAddress->createInputModelFilter();
		$pInputModelRecordCount = $this->_pFormModelBuilderAddress->createInputModelRecordsPerPage();
		$pInputModelSortBy = $this->_pFormModelBuilderAddress->createInputModelSortBy
			(onOfficeSDK::MODULE_ADDRESS);
		$pInputModelSortOrder = $this->_pFormModelBuilderAddress->createInputModelSortOrder();
		$pFormModelFilterRecords = new FormModel();
		$pFormModelFilterRecords->setPageSlug($this->getPageSlug());
		$pFormModelFilterRecords->setGroupSlug(self::FORM_VIEW_RECORDS_FILTER);
		$pFormModelFilterRecords->setLabel(__('Filter & Records', 'onoffice'));
		$pFormModelFilterRecords->addInputModel($pInputModelFilter);
		$pFormModelFilterRecords->addInputModel($pInputModelRecordCount);
		$pFormModelFilterRecords->addInputModel($pInputModelSortBy);
		$pFormModelFilterRecords->addInputModel($pInputModelSortOrder);
		$this->addFormModel($pFormModelFilterRecords);
	}


	/**
	 *
	 */

	private function addFormModelPictureTypes()
	{
		$pInputModelPictureTypes = $this->_pFormModelBuilderAddress->createInputModelPictureTypes();
		$pFormModelPictureTypes = new FormModel();
		$pFormModelPictureTypes->setPageSlug($this->getPageSlug());
		$pFormModelPictureTypes->setGroupSlug(self::FORM_VIEW_PICTURE_TYPES);
		$pFormModelPictureTypes->setLabel(__('Photo Types', 'onoffice'));
		$pFormModelPictureTypes->addInputModel($pInputModelPictureTypes);
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
			$this->createMetaBoxByForm($pFormFieldsConfig, 'side');
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
			$row = $this->addOrderValues($row, RecordManager::TABLENAME_FIELDCONFIG_ADDRESS);

			$recordId = $pRecordManagerInsert->insertByRow($row);
			$result = $recordId != null;

			if ($result) {
				$row = $this->addOrderValues($row, RecordManager::TABLENAME_FIELDCONFIG);
				$row = $this->prepareRelationValues(RecordManager::TABLENAME_FIELDCONFIG_ADDRESS,
					'listview_address_id', $row, $recordId);
				$pRecordManagerInsert->insertAdditionalValues($row);
			}
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
		return $this->addOrderValues($row, RecordManager::TABLENAME_FIELDCONFIG_ADDRESS);
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
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEnqueueData()
	{
		return array(
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The address list was saved.', 'onoffice'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the list. Please make sure the name of the list is unique.', 'onoffice'),
			self::ENQUEUE_DATA_MERGE => array(AdminPageSettingsBase::POST_RECORD_ID),
			AdminPageSettingsBase::POST_RECORD_ID => $this->getListViewId(),
		);
	}


	/**
	 *
	 */

	public function doExtraEnqueues()
	{
		parent::doExtraEnqueues();
		wp_enqueue_script('oo-checkbox-js');
	}
}
