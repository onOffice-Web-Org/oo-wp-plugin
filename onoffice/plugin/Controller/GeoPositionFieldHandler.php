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

namespace onOffice\WPlugin\Controller;

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Record\RecordManagerRead;
use function esc_sql;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class GeoPositionFieldHandler
{
	/** @var DataListView */
	private $_listViewId = 0;

	/** @var RecordManagerRead */
	private $_pRecordManager = null;


	/**
	 *
	 * @param int $listviewId
	 * @param RecordManagerRead $pRecordManagerRead
	 *
	 */

	public function __construct(int $listviewId, RecordManagerRead $pRecordManagerRead)
	{
		$this->_listViewId = $listviewId;
		$this->_pRecordManager = $pRecordManagerRead;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getActiveFields(): array
	{
		$moduleByTable = array_search($this->_pRecordManager->getMainTable(),
			InputModelDBFactoryConfigGeoFields::MODULE_TO_TABLE);
		$pInputModelFactory = new InputModelDBFactoryConfigGeoFields($moduleByTable);
		$booleanFields = $pInputModelFactory->getBooleanFields();

		array_map(function($column) {
			$this->_pRecordManager->addColumn($column);
		}, $booleanFields);

		$idColumn = $this->_pRecordManager->getIdColumnMain();
		$where = '`'.esc_sql($idColumn).'` = "'.esc_sql($this->_listViewId).'"';
		$this->_pRecordManager->addWhere($where);
		$records = (array)$this->_pRecordManager->getRecords()[0] ?? [];
		$activeFields = array_filter($records, function($value) {
			return $value === '1';
		});
		$activeGeoFields = array_intersect_key(array_flip($booleanFields), $activeFields);
		return $activeGeoFields;
	}
}
