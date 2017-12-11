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

use onOffice\WPlugin\Record\RecordManager;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\Record\RecordManagerUpdateListView;
use onOffice\WPlugin\Record\RecordManagerInsertListView;

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
	const VIEW_SAVE_SUCCESSFUL_MESSAGE = 'view_save_success_message';

	/** */
	const VIEW_SAVE_FAIL_MESSAGE = 'view_save_fail_message';

	/** */
	const FORM_VIEW_RECORDS_FILTER = 'viewrecordsfilter';

	/** */
	const FORM_VIEW_LAYOUT_DESIGN = 'viewlayoutdesign';

	/** */
	const FORM_VIEW_PICTURE_TYPES = 'viewpicturetypes';

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
	 * @param \stdClass $pResult
	 * @param int $recordId
	 *
	 */

	protected function updateValues(array $row, \stdClass $pResult, $recordId = null)
	{
		$result = false;
		$pDummyDetailView = new DataDetailView();

		if ($row[RecordManager::TABLENAME_LIST_VIEW]['name'] === $pDummyDetailView->getName()) {
			// false / null
			$pResultObject->result = false;
			$pResultObject->record_id = null;
			return;
		}

		if ($recordId != null)
		{
			$pUpdate = new RecordManagerUpdateListView($recordId);
			$result = $pUpdate->updateByRow($row);
		}
		else
		{
			$pInsert = new RecordManagerInsertListView();
			$recordId = $pInsert->insertByRow($row);

			$result = ($recordId != null);
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

	protected function setFixedValues(array $row) {
		return $row;
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
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the view. Please make sure the name of the view is unique.', 'onoffice'),
			self::ENQUEUE_DATA_MERGE => array(AdminPageSettingsBase::POST_RECORD_ID),
			AdminPageSettingsBase::POST_RECORD_ID => $this->getListViewId(),
		);
	}
}
