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

use onOffice\WPlugin\DataView\DataViewFilterableFields;
use onOffice\WPlugin\Model\InputModel\InputModelDBFactoryConfigGeoFields;
use onOffice\WPlugin\Record\RecordManagerFactory;
use function esc_sql;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class GeoPositionFieldHandler
	implements GeoPositionFieldHandlerBase
{
	/** @var RecordManagerFactory */
	private $_pRecordManagerFactory = null;

	/** @var array */
	private $_booleanFields = [];

	/** @var array */
	private $_records = [];


	/**
	 *
	 * @param int $listviewId
	 * @param RecordManagerFactory $pRecordManagerFactory
	 * @param InputModelDBFactoryConfigGeoFields $pInputModelDBFactoryConfigGeoFields
	 *
	 */

	public function __construct(
		RecordManagerFactory $pRecordManagerFactory = null)
	{
		$this->_pRecordManagerFactory = $pRecordManagerFactory ?? new RecordManagerFactory();
	}


	/**
	 *
	 * @param ViewProperty $pViewProperty
	 *
	 */

	public function readValues(ViewProperty $pViewProperty)
	{
		$pInputModelFactory = new InputModelDBFactoryConfigGeoFields($pViewProperty->getModule());
		$pRecordManager = $this->_pRecordManagerFactory->create
			($pViewProperty->getModule(), RecordManagerFactory::ACTION_READ, $pViewProperty->getId());
		$this->_booleanFields = $pInputModelFactory->getBooleanFields();

		array_map(function($column) use ($pRecordManager) {
			$pRecordManager->addColumn($column);
		}, $this->_booleanFields);
		$pRecordManager->addColumn('radius');

		$idColumn = $pRecordManager->getIdColumnMain();
		$where = '`'.esc_sql($idColumn).'` = "'.esc_sql($pViewProperty->getId()).'"';
		$pRecordManager->addWhere($where);

		$this->_records = (array)($pRecordManager->getRecords()[0] ?? []);
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

		$activeGeoFields = array_intersect_key(array_flip($this->_booleanFields), $activeFields);
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
		$valuesDiff = array_diff_key($values, array_flip($this->_booleanFields));
		return $valuesDiff;
	}


	/**  @return int */
	public function getRadiusValue(): int
		{ return intval($this->_records['radius'] ?? 0); }
}
