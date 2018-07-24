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

namespace onOffice\WPlugin\ViewFieldModifier;

use Exception;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class ViewFieldModifierHandler
{
	/** @var array */
	private $_modifierMapping = [];

	/** @var array */
	private $_viewFields = [];

	/** @var ViewFieldModifierTypeBase */
	private $_pModifier = null;


	/**
	 *
	 * @param array $viewFields
	 * @param string $module
	 *
	 */

	public function __construct(array $viewFields, string $module, string $modifier = '')
	{
		$this->_viewFields = $viewFields;
		$pViewFieldModifierFactory = new ViewFieldModifierFactory($module);
		$this->_modifierMapping = $pViewFieldModifierFactory->getMapping();
		$this->_pModifier = $pViewFieldModifierFactory->create($modifier);
	}


	/**
	 *
	 * @param array $record
	 * @return array
	 * @throws Exception
	 *
	 */

	public function processRecord(array $record): array
	{
		if ($this->_pModifier === null) {
			throw new Exception('Unknown Modifier');
		}

		$newRecord = $this->_pModifier->reduceRecord($record);
		$allExtraFields = $this->getAllAPIFields();
		$extraFieldsModifier = $this->_pModifier->getAPIFields();
		$extraFieldsForRemoval = array_diff($allExtraFields, $extraFieldsModifier);

		foreach (array_keys($newRecord) as $key) {
			if (in_array($key, $extraFieldsForRemoval) &&
				!in_array($key, $this->_viewFields) &&
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

	public function getAllAPIFields(): array
	{
		$apiFields = [];

		foreach ($this->_modifierMapping as $class) {
			$pInstance = new $class;
			/* @var $pInstance EstateViewFieldModifier */
			$apiFields = array_merge($apiFields, $pInstance->getAPIFields());
		}

		return $apiFields;
	}
}
