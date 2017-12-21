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

use onOffice\WPlugin\Record\RecordManagerReadForm;
use onOffice\WPlugin\DataFormConfiguration\UnknownFormException;

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

	protected function validate($recordId = null)
	{
		if ($recordId == null) {
			return;
		}

		$pRecordReadManager = new RecordManagerReadForm();
		$pWpDb = $pRecordReadManager->getWpdb();
		$prefix = $pRecordReadManager->getTablePrefix();
		$value = $pWpDb->get_var('SELECT form_id FROM `'.esc_sql($prefix)
			.'oo_plugin_forms` WHERE `form_id` = "'.esc_sql($recordId).'"');

		if ($value != $recordId) {
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

		if ($recordId != null)
		{
			// update by row
		}
		else
		{
			// insert
			$result = ($recordId != null);
		}

		$pResult->result = $result;
		$pResult->record_id = $recordId;
	}

	/** @return string */
	public function getType()
		{ return $this->_type; }

	/** @param string $type */
	public function setType($type)
		{ $this->_type = $type; }
}
