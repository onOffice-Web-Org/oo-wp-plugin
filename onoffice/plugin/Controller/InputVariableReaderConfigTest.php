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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class InputVariableReaderConfigTest
	implements InputVariableReaderConfig
{
	/** @var array */
	private $_fieldTypes = [];

	/** @var array */
	private $_values = [];

	/** @var string */
	private $_timezoneStr = 'Europe/Berlin';


	/**
	 *
	 * @param string $field
	 * @param string $module
	 * @return string
	 *
	 */

	public function getFieldType(string $field, string $module): string
	{
		return $this->_fieldTypes[$module][$field];
	}


	/**
	 *
	 * @param string $field
	 * @param string $module
	 * @param string $type
	 *
	 */

	public function setFieldTypeByModule(string $field, string $module, string $type)
	{
		$this->_fieldTypes[$module][$field] = $type;
	}


	/**
	 *
	 * @param string $field
	 * @param array $values
	 *
	 */

	public function setValueArray(string $field, array $values)
	{
		$this->_values[$field] = $values;
	}


	/**
	 *
	 * @param string $field
	 * @param string $value
	 *
	 */

	public function setValue(string $field, string $value)
	{
		$this->_values[$field] = $value;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function getTimezoneString(): string
	{
		return $this->_timezoneStr;
	}


	/**
	 *
	 * @param string $timezoneStr
	 *
	 */

	public function setTimezoneString(string $timezoneStr)
	{
		$this->_timezoneStr = $timezoneStr;
	}


	/**
	 *
	 * @param int $var
	 * @param string $name
	 * @param int $sanitizer
	 * @return array
	 *
	 */

	public function getFilterVariable(int $var, string $name, int $sanitizer)
	{
		$value = $this->_values[$name] ?? null;
		return is_null($value) ? null : filter_var($value, $sanitizer, FILTER_FORCE_ARRAY);
	}


	/**
	 *
	 * @param string $name
	 * @return bool
	 *
	 */

	public function getIsRequestVarArray(string $name): bool
	{
		return is_array($this->_values[$name] ?? null);
	}
}
