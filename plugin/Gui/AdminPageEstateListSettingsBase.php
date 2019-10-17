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
use stdClass;
use function __;

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
	const FORM_VIEW_DOCUMENT_TYPES = 'viewdocumenttypes';

	/** */
	const FORM_VIEW_FIELDS_CONFIG = 'viewfieldsconfig';


	/**
	 *
	 */

	public function renderContent()
	{
		$this->validate($this->getListViewId());
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
	{var_dump($row);
		$result = false;
		$pDummyDetailView = new DataDetailView();
		$type = RecordManagerFactory::TYPE_ESTATE;

		if ($row[RecordManager::TABLENAME_LIST_VIEW]['name'] === $pDummyDetailView->getName()) {
			// false / null
			$pResultObject->result = false;
			$pResultObject->record_id = null;
			return;
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
				];

				$pInsert->insertAdditionalValues($row);
				$result = true;
			} catch (RecordManagerInsertException $pException) {
				$result = false;
				$recordId = null;
			}
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
		$result = isset($row[$table]['name']) && $row[$table]['name'] != null;

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

	public function getEnqueueData()
	{
		return array(
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The view has been saved.', 'onoffice'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the view. Please make '
				.'sure the name of the view is unique, even across all estate list types.', 'onoffice'),
			self::ENQUEUE_DATA_MERGE => array(AdminPageSettingsBase::POST_RECORD_ID),
			AdminPageSettingsBase::POST_RECORD_ID => $this->getListViewId(),
		);
	}
}
