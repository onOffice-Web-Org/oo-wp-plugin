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
		$this->_pModifier = $pViewFieldModifierFactory->create($modifier, $viewFields);
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

		$intersection = array_intersect_key
			($newRecord, array_flip($this->_pModifier->getVisibleFields()));

		return $intersection;
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
			$pInstance = new $class($this->_viewFields);
			/* @var $pInstance ViewFieldModifierTypeBase */
			$apiFields = array_merge($apiFields, $pInstance->getAPIFields());
		}

		return array_values(array_unique($apiFields));
	}
}
