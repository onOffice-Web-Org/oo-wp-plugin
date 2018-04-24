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

namespace onOffice\WPlugin\Model;

use onOffice\WPlugin\Model\InputModelDB;
use onOffice\WPlugin\Record\RecordManager;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 */

class InputModelDBAdapterRow
{
	/** @var InputModelDB[] */
	private $_inputModels = array();


	/**
	 *
	 * array_keys($_foreignKeys[$table])[1] must always be the field that
	 * holds the foreign key to the main table
	 *
	 * @var array
	 *
	 */

	private $_foreignKeys = array(
		'oo_plugin_fieldconfig' => array(
			'fieldconfig_id' => null,
			'listview_id' => array('oo_plugin_listviews', 'listview_id'),
		),

		'oo_plugin_address_fieldconfig' => array(
			'address_fieldconfig_id' => null,
			'listview_address_id' => array('oo_plugin_listviews_address', 'listview_address_id'),
		),

		'oo_plugin_picturetypes' => array(
			'picturetype_id' => null,
			'listview_id' => array('oo_plugin_listviews', 'listview_id'),
		),

		'oo_plugin_form_fieldconfig' => array(
			'form_fieldconfig_id' => null,
			'form_id' => array('oo_plugin_forms', 'form_id'),
		),
	);

	/** @var array */
	private $_primaryKeys = array(
		'oo_plugin_listviews' => 'listview_id',
		'oo_plugin_forms' => 'form_id',
	);


	/**
	 *
	 * @param InputModelDB $pInputModelDB
	 *
	 */

	public function addInputModelDB(InputModelDB $pInputModelDB)
	{
		$this->_inputModels []= $pInputModelDB;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function createUpdateValuesByTable() {
		$valuesByTable = array();

		foreach ($this->_inputModels as $pInputModel)
		{
			$table = $pInputModel->getTable();
			$field = $pInputModel->getField();
			$mainId = $pInputModel->getMainRecordId();
			$value = $pInputModel->getValue();

			if (!array_key_exists($table, $valuesByTable)) {
				$valuesByTable[$table] = array();
			}

			if ($this->isForeignKey($table, $field)) {
				list($foreignTable, $foreignKey) = $this->_foreignKeys[$table][$field];
				if ($this->isPrimaryKey($foreignTable, $foreignKey)) {
					$valuesByTable[$foreignTable][$foreignKey][] = $mainId;
				}
			} else {
				if (is_array($value)) {
					foreach ($value as $id => $subValue) {
						$valuesByTable[$table][$id][$field] = RecordManager::postProcessValue
							($subValue, $table, $field);
						$mainColumn = $this->getMainForeignKeyColumnOfRelation($table);
						if ($mainColumn !== null) {
							$valuesByTable[$table][$id][$mainColumn] = $mainId;
						}
					}
				} else {
					$valuesByTable[$table][$field] = RecordManager::postProcessValue
						($value, $table, $field);
				}
			}
		}

		return $valuesByTable;
	}


	/**
	 *
	 * @param string $table
	 * @param string $key
	 * @return bool
	 *
	 */

	private function isPrimaryKey($table, $key)
	{
		return array_key_exists($table, $this->_primaryKeys) &&
			$this->_primaryKeys[$table] === $key;
	}


	/**
	 *
	 * @param string $table
	 * @param string $field
	 * @return bool
	 *
	 */

	private function isForeignKey($table, $field)
	{
		return array_key_exists($table, $this->_foreignKeys) &&
			array_key_exists($field, $this->_foreignKeys[$table]);
	}


	/**
	 *
	 * @param string $relationTable
	 * @return array
	 *
	 */

	private function getMainForeignKeyColumnOfRelation($relationTable) {
		$result = null;

		if (isset($this->_foreignKeys[$relationTable])) {
			$possibleValues = array_filter($this->_foreignKeys[$relationTable]); // without primary
			$keyNames = array_keys($possibleValues);
			$result = array_shift($keyNames);
		}

		return $result;
	}


	/** @return array */
	public function getForeignKeys()
		{ return $this->_foreignKeys; }

	/** @return array */
	public function getPrimaryKeys()
		{ return $this->_primaryKeys; }
}
