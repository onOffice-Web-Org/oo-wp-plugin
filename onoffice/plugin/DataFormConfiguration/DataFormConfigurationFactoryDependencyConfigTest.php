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

namespace onOffice\WPlugin\DataFormConfiguration;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class DataFormConfigurationFactoryDependencyConfigTest
	implements DataFormConfigurationFactoryDependencyConfigBase
{
	/** @var array */
	private $_fieldsByFormId = [];

	/** @var array */
	private $_mainRowsById = [];

	/** @var array */
	private $_mainRowsByName = [];

	/** @var bool */
	private $_isAdminInterface = false;


	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */

	public function getFieldsByFormId(int $formId): array
	{
		return $this->_fieldsByFormId[$formId];
	}


	/**
	 *
	 * @param int $formId
	 * @return array
	 *
	 */

	public function getMainRowById(int $formId): array
	{
		return $this->_mainRowsById[$formId];
	}


	/**
	 *
	 * @param string $name
	 * @return array
	 *
	 */

	public function getMainRowByName(string $name): array
	{
		return $this->_mainRowsByName[$name];
	}


	/**
	 *
	 * @param string $name
	 * @param array $row
	 *
	 */

	public function addMainRowByName(string $name, array $row)
	{
		$this->_mainRowsByName[$name] = $row;
	}


	/**
	 *
	 * @param int $id
	 * @param array $row
	 *
	 */

	public function addMainRowById(int $id, array $row)
	{
		$this->_mainRowsById[$id] = $row;
	}


	/**
	 *
	 * @param int $formId
	 * @param array $fields
	 *
	 */

	public function setFieldsByFormId(int $formId, array $fields)
	{
		$this->_fieldsByFormId[$formId] = $fields;
	}


	/**
	 *
	 * @param bool $isAdminInterface
	 *
	 */

	public function setAdminInterface(bool $isAdminInterface)
	{
		$this->_isAdminInterface = $isAdminInterface;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getIsAdminInterface(): bool
	{
		return $this->_isAdminInterface;
	}
}
