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

declare (strict_types=1);

namespace onOffice\WPlugin\Controller;

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Record\RecordManagerRead;
use const ONOFFICE_FEATURE_CONFIGURE_GEO;
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

	/** @var InputModelDBFactoryConfigGeoFields */
	private $_pInputModelFactory = null;

	/** @var array */
	private $_records = [];


	/**
	 *
	 * @param int $listviewId
	 * @param RecordManagerRead $pRecordManagerRead
	 * @param InputModelDBFactoryConfigGeoFields $pInputModelDBFactoryConfigGeoFields
	 *
	 */

	public function __construct(int $listviewId,
		RecordManagerRead $pRecordManagerRead,
		InputModelDBFactoryConfigGeoFields $pInputModelDBFactoryConfigGeoFields = null)
	{
		$this->_listViewId = $listviewId;
		$this->_pRecordManager = $pRecordManagerRead;
		$moduleByTable = array_search($this->_pRecordManager->getMainTable(),
			InputModelDBFactoryConfigGeoFields::MODULE_TO_TABLE);
		$this->_pInputModelFactory = $pInputModelDBFactoryConfigGeoFields ??
			new InputModelDBFactoryConfigGeoFields($moduleByTable);
	}


	/**
	 *
	 * Load values from DB.
	 *
	 * No-op if listview ID is 0
	 *
	 */

	public function readValues()
	{
		if ($this->_listViewId !== 0) {
			$booleanFields = $this->_pInputModelFactory->getBooleanFields();

			array_map(function($column) {
				$this->_pRecordManager->addColumn($column);
			}, $booleanFields);
			$this->_pRecordManager->addColumn('radius');

			$idColumn = $this->_pRecordManager->getIdColumnMain();
			$where = '`'.esc_sql($idColumn).'` = "'.esc_sql($this->_listViewId).'"';
			$this->_pRecordManager->addWhere($where);

			$this->_records = (array)($this->_pRecordManager->getRecords()[0] ?? []);
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getActiveFields(): array
	{
		$activeFields = array_filter($this->_records, function($value) {
			return $value === '1';
		});

		$booleanFields = $this->_pInputModelFactory->getBooleanFields();
		$activeGeoFields = array_intersect_key(array_flip($booleanFields), $activeFields);
		return $activeGeoFields;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getActiveFieldsWithValue(): array
	{
		$activeFields = array_values($this->getActiveFields());
		$values = array_replace
			(array_combine($activeFields , array_fill
				(0, count($activeFields), null)), $this->_records);
		$valuesDiff = array_diff_key
			($values, array_flip($this->_pInputModelFactory->getBooleanFields()));
		return $valuesDiff;
	}


	/**  @return int */
	public function getRadiusValue(): int
		{ return intval($this->_records['radius'] ?? 0); }

	/** @return int */
	public function getListviewId(): int
		{ return $this->_listViewId; }
}
