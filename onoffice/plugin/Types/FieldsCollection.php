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

use Exception;

/**
 *
 */

class FieldsCollection
{
	/** @var array */
	private $_fields = [];

	/** @var string */
	private $_module = '';


	/**
	 *
	 * @param string $module
	 *
	 */

	public function __construct(string $module)
	{
		$this->_module = $module;
	}


	/**
	 *
	 * @param Field $pField
	 *
	 */

	public function addField(Field $pField)
	{
		$this->_fields[$pField->getName()] = $pField;
	}


	/**
	 *
	 * @param string $name
	 * @return Field
	 * @throws Exception
	 *
	 */

	public function getByName(string $name): Field
	{
		if (!isset($this->_fields[$name])) {
			throw new Exception('Field not in collection');
		}

		return $this->_fields[$name];
	}


	/**
	 *
	 * @param string $name
	 * @return bool
	 *
	 */

	public function containsField(string $name): bool
	{
		return isset($this->_fields[$name]);
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
	 * @return string
	 *
	 */

	public function getModule(): string
	{
		return $this->_module;
	}
}
