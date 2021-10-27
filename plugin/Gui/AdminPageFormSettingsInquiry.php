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

use DI\DependencyException;
use DI\NotFoundException;
use onOffice\WPlugin\Record\RecordManager;
use function __;

/**
 *
 * Class for forms with additional check for Email address
 *
 */

class AdminPageFormSettingsInquiry
	extends AdminPageFormSettingsContact
{
	/** */
	const VIEW_SAVE_FAIL_NO_MAIL = 'view_save_fail_no_mail';

	/** @var bool */
	private $_hasEmailError = false;

	/**
	 *
	 * @param array $row
	 * @return bool
	 *
	 */

	protected function checkFixedValues($row)
	{
		$table = RecordManager::TABLENAME_FORMS;
		$resultName = isset($row[$table]['name']) && $row[$table]['name'] != null;
		$resultRecipient = isset($row[$table]['recipient']) && $row[$table]['recipient'] != null;

		$this->_hasEmailError = !$resultRecipient;

		return $resultName && $resultRecipient;
	}


	/**
	 *
	 * @param bool $result
	 * @return string
	 *
	 */

	protected function getResponseMessagekey($result)
	{
		$key = parent::getResponseMessagekey($result);

		if ($this->_hasEmailError) {
			$key = self::VIEW_SAVE_FAIL_NO_MAIL;
		}

		return $key;
	}


	/**
	 *
	 * @return array
	 * @throws DependencyException
	 * @throws NotFoundException
	 */

	public function getEnqueueData(): array
	{
		$returnArray = parent::getEnqueueData();
		$returnArray[self::VIEW_SAVE_FAIL_NO_MAIL] = __('Please provide an Email address!',
			'onoffice-for-wp-websites');

		return $returnArray;
	}
}
