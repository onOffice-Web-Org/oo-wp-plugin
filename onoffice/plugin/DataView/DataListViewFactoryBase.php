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

namespace onOffice\WPlugin\DataView;

use onOffice\WPlugin\Record\RecordManagerRead;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

abstract class DataListViewFactoryBase
{
	/** @var RecordManagerRead */
	private $_pRecordManagerRead = null;


	/**
	 *
	 * @param string $listViewName
	 * @param string $type For forms only
	 * @return DataListView
	 *
	 */

	public function getListViewByName($listViewName, $type = null)
	{
		$pRecordRead = $this->getRecordManagerRead();
		$record = $pRecordRead->getRowByName($listViewName, $type);

		if ($record === null) {
			throw new UnknownViewException($listViewName);
		}

		return $this->createListViewByRow($record);
	}


	/**
	 *
	 * @param array $row
	 * @return object
	 *
	 */

	abstract public function createListViewByRow(array $row);

	/** @param RecordManagerRead $pRecordManagerRead */
	protected function setRecordManagerRead(RecordManagerRead $pRecordManagerRead)
		{ $this->_pRecordManagerRead = $pRecordManagerRead; }

	/** @return RecordManagerRead */
	public function getRecordManagerRead()
		{ return $this->_pRecordManagerRead; }
}
