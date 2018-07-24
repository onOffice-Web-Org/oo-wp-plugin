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

use onOffice\SDK\onOfficeSDK;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class ViewFieldModifierFactory
{
	/** @var array */
	private $_moduleToTypeClass = [
		onOfficeSDK::MODULE_ESTATE => EstateViewFieldModifierTypes::class,
	];

	/** @var string */
	private $_module = null;


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
	 * @param string $type
	 * @return ViewFieldModifierTypeBase
	 *
	 */

	public function create(string $type)
	{
		$mapping = $this->getMapping();

		if (isset($mapping[$type])) {
			return new $mapping[$type];
		}

		return null;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getMappingClass()
	{
		return $this->_moduleToTypeClass[$this->_module] ?? null;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getMapping(): array
	{
		$mappingClass = $this->getMappingClass();
		$mapping = [];

		if ($mappingClass !== null) {
			$pViewFieldModifierTypes = new $mappingClass();
			$mapping = $pViewFieldModifierTypes->getMapping();
		}

		return $mapping;
	}
}
