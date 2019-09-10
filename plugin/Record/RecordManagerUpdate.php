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

namespace onOffice\WPlugin\Record;

/**
 *
 */

abstract class RecordManagerUpdate
	extends RecordManager
{
	/** @var int */
	private $_recordId = null;


	/**
	 *
	 * @param int $recordId
	 *
	 */

	public function __construct($recordId)
	{
		$this->_recordId = $recordId;
	}


	/**
	 *
	 * @param array $tableRow
	 * @return bool
	 *
	 */

	abstract public function updateByRow(array $tableRow): bool;


	/** @return int */
	public function getRecordId()
		{ return $this->_recordId; }
}
