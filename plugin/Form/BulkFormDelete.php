<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

declare (strict_types=1);

namespace onOffice\WPlugin\Form;

use Exception;
use onOffice\WPlugin\Controller\UserCapabilities;
use onOffice\WPlugin\Record\RecordManagerDeleteForm;
use onOffice\WPlugin\WP\WPQueryWrapper;
use function current_user_can;

/**
 *
 */

class BulkFormDelete
{
	/** @var RecordManagerDeleteForm */
	private $_pRecordManagerDeleteForm;

	/** @var WPQueryWrapper */
	private $_pWPQueryWrapper;

	/** @var UserCapabilities */
	private $_pUserCapabilities;


	/**
	 *
	 * @param RecordManagerDeleteForm $pRecordManagerDeleteForm
	 * @param WPQueryWrapper $pWPQueryWrapper
	 * @param UserCapabilities $pUserCapabilities
	 *
	 */

	public function __construct(
		RecordManagerDeleteForm $pRecordManagerDeleteForm,
		WPQueryWrapper $pWPQueryWrapper,
		UserCapabilities $pUserCapabilities)
	{
		$this->_pRecordManagerDeleteForm = $pRecordManagerDeleteForm;
		$this->_pWPQueryWrapper = $pWPQueryWrapper;
		$this->_pUserCapabilities = $pUserCapabilities;
	}


	/**
	 *
	 * @return int
	 * @throws Exception
	 *
	 */

	public function delete(string $action, array $records): int
	{
		$this->doPreChecks();

		switch ($action) {
			case 'delete':
			case 'bulk_delete':
				$this->_pRecordManagerDeleteForm->deleteByIds($records);
				return count($records);
		}

		return 0;
	}


	/**
	 *
	 * @throws Exception
	 *
	 */

	private function doPreChecks()
	{
		$roleEditForms = $this->_pUserCapabilities->getCapabilityForRule(UserCapabilities::RULE_EDIT_VIEW_FORM);

		if (!current_user_can($roleEditForms)) {
			throw new Exception('Not allowed');
		}
	}
}
