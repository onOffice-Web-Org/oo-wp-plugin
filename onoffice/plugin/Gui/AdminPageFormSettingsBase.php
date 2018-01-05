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
use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\Record\RecordManagerInsertForm;
use onOffice\WPlugin\Record\RecordManagerUpdateForm;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;
use onOffice\WPlugin\DataFormConfiguration\DataFormConfigurationFactory;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
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
	const GET_PARAM_TYPE = 'type';

	/** @var string */
	private $_type = null;

	/**
	 *
	 * @param string $pageSlug
	 *
	 */

	public function __construct($pageSlug)
	{
		$this->setPageTitle(__('Edit Form', 'onoffice'));
		parent::__construct($pageSlug);
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
			.'oo_plugin_forms` WHERE `form_id` = "'.esc_sql($recordId).'"');

		if ($value != (int)$recordId) {
			throw new UnknownFormException;
		}
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
		$type = $this->getType();

		if ($recordId != 0) {
			// update by row
			$pRecordManagerUpdateForm = new RecordManagerUpdateForm($recordId);
			$result = $pRecordManagerUpdateForm->updateByRow($row[RecordManager::TABLENAME_FORMS]);

			if (array_key_exists(RecordManager::TABLENAME_FIELDCONFIG_FORMS, $row)) {
				$result = $result && $pRecordManagerUpdateForm->updateFieldConfigByRow
					($row[RecordManager::TABLENAME_FIELDCONFIG_FORMS]);
			}
		} else {
			// insert
			$pFormDataConfigFactory = new DataFormConfigurationFactory($type);
			$pFormData = $pFormDataConfigFactory->createByRow($row[RecordManager::TABLENAME_FORMS]);
			$pFormData->setFormType($type);
			/* @var $pFormData \onOffice\WPlugin\DataFormConfiguration\DataFormConfiguration */
			$formConfigRow = array_key_exists(RecordManager::TABLENAME_FIELDCONFIG_FORMS, $row) ?
					$row[RecordManager::TABLENAME_FIELDCONFIG_FORMS] : array();
			$pFormDataConfigFactory->addModulesByFields($formConfigRow, $pFormData);

			$pRecordManagerInserForm = new RecordManagerInsertForm();
			$recordId = $pRecordManagerInserForm->insertByDataFormConfiguration($pFormData);

			$result = ($recordId != null);
		}

		$pResult->result = $result;
		$pResult->record_id = $recordId;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getEnqueueData()
	{
		return array(
			self::GET_PARAM_TYPE => $this->getType(),
			self::VIEW_SAVE_SUCCESSFUL_MESSAGE => __('The Form was Saved.', 'onoffice'),
			self::VIEW_SAVE_FAIL_MESSAGE => __('There was a problem saving the form. Please make '
					.'sure the name of the form is unique.', 'onoffice'),
			self::ENQUEUE_DATA_MERGE => array(
					AdminPageSettingsBase::POST_RECORD_ID,
					self::GET_PARAM_TYPE,
				),
			AdminPageSettingsBase::POST_RECORD_ID => (int)$this->getListViewId(),
		);
	}


	/** @return string */
	public function getType()
		{ return $this->_type; }

	/** @param string $type */
	public function setType($type)
		{ $this->_type = $type; }
}
