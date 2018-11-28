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

namespace onOffice\WPlugin\Types;

use onOffice\WPlugin\Field\FieldModuleCollection;
use onOffice\WPlugin\Field\UnknownFieldException;

/**
 *
 */

class FieldsCollection implements FieldModuleCollection
{
	/** @var array */
	private $_fields = [];

	/** @var array */
	private $_fieldsByModule = [];


	/**
	 *
	 * @param Field $pField
	 *
	 */

	public function addField(Field $pField)
	{
		$name = $pField->getName();
		$module = $pField->getModule();
		$this->_fields []= $pField;
		$this->_fieldsByModule[$module][$name] = $pField;
	}


	/**
	 *
	 * @param string $name
	 * @return bool
	 *
	 */

	public function containsFieldByModule(string $module, string $name): bool
	{
		return isset($this->_fieldsByModule[$module][$name]);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAllFields(): array
	{
		return $this->_fields;
	}


	/**
	 *
	 * @param string $module
	 * @param string $name
	 * @return Field
	 * @throws UnknownFieldException
	 *
	 */

	public function getFieldByModuleAndName(string $module, string $name): Field
	{
		$pField = $this->_fieldsByModule[$module][$name] ?? null;

		if ($pField === null) {
			throw new UnknownFieldException();
		}

		return $pField;
	}
}
