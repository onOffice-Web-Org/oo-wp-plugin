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
use onOffice\WPlugin\Record\RecordManagerDelete;

/**
 *
 */

class BulkDeleteRecord
{
	/** @var UserCapabilities */
	private $_pUserCapabilities;


	/**
	 *
	 * @param UserCapabilities $pUserCapabilities
	 *
	 */

	public function __construct(
		UserCapabilities $pUserCapabilities)
	{
		$this->_pUserCapabilities = $pUserCapabilities;
	}


	/**
	 *
	 * @param RecordManagerDelete $pRecordManagerDelete
	 * @param string $capability
	 * @param array $records
	 * @return int
	 *
	 */

	public function delete(RecordManagerDelete $pRecordManagerDelete, string $capability, array $records): int
	{
		$this->doPreChecks($capability);

		$pRecordManagerDelete->deleteByIds($records);
		return count($records);
	}


	/**
	 *
	 * @throws Exception
	 *
	 */

	private function doPreChecks(string $capability)
	{
		$this->_pUserCapabilities->checkIfCurrentUserCan($capability);
	}
}
