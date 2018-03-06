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

namespace onOffice\WPlugin\EstateViewFieldModifier;

use onOffice\WPlugin\DataView\DataView;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateFieldModifierHandler
{
	/** @var DataView */
	private $_pDataView = null;

	/** @var \onOffice\WPlugin\EstateViewFieldModifier\EstateViewFieldModifierTypeBase */
	private $_pModifier = null;


	/**
	 *
	 * @param DataView $pDataView
	 * @param string $modifier
	 * @throws Exception
	 *
	 */

	public function __construct(DataView $pDataView, $modifier)
	{
		$this->_pDataView = $pDataView;
		$this->_pModifier = EstateViewFieldModifierFactory::create($modifier);

		if (is_null($this->_pModifier)) {
			throw new Exception('Unknown Modifier');
		}
	}


	/**
	 *
	 * @param array $record
	 * @return array
	 *
	 */

	public function processRecord(array $record)
	{
		$newRecord = $this->_pModifier->reduceRecord($record);
		$allExtraFields = $this->getAllAPIFields();
		$extraFieldsModifier = $this->_pModifier->getAPIFields();
		$extraFieldsForRemoval = array_diff($allExtraFields, $extraFieldsModifier);

		foreach (array_keys($newRecord) as $key) {
			if (in_array($key, $extraFieldsForRemoval) &&
				!in_array($key, $this->_pDataView->getFields()) &&
				!in_array($key, $this->_pModifier->getVisibleFields())) {
				unset($newRecord[$key]);
			}
		}

		return $newRecord;
	}


	/**
	 *
	 * @return array
	 *
	 */

	static public function getAllAPIFields()
	{
		$mapping = EstateViewFieldModifierTypes::getMapping();
		$apiFields = array();

		foreach ($mapping as $class) {
			$pInstance = new $class;
			/* @var $pInstance EstateViewFieldModifier */
			$apiFields = array_merge($apiFields, $pInstance->getAPIFields());
		}

		return $apiFields;
	}
}
