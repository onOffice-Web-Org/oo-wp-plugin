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

namespace onOffice\WPlugin\ViewFieldModifier;

use onOffice\SDK\onOfficeSDK;
use UnexpectedValueException;

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
		onOfficeSDK::MODULE_ADDRESS => AddressViewFieldModifierTypes::class,
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
	 * @param array $viewFields
	 * @return ViewFieldModifierTypeBase
	 * @throws UnexpectedValueException
	 *
	 */

	public function create(string $type, array $viewFields = []): ViewFieldModifierTypeBase
	{
		$mapping = $this->getMapping();

		if (!isset($mapping[$type])) {
			throw new UnexpectedValueException;
		}
		return new $mapping[$type]($viewFields);
	}


	/**
	 *
	 * @return string
	 * @throws UnexpectedValueException
	 *
	 */

	private function getMappingClass(): string
	{
		$class = $this->_moduleToTypeClass[$this->_module] ?? null;

		if ($class === null) {
			throw new UnexpectedValueException;
		}

		return $class;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getMapping(): array
	{
		$mappingClass = $this->getMappingClass();
		$pViewFieldModifierTypes = new $mappingClass();
		return $pViewFieldModifierTypes->getMapping();
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getForbiddenAPIFields(): array
	{
		$mappingClass = $this->getMappingClass();
		$pViewFieldModifierTypes = new $mappingClass();
		return $pViewFieldModifierTypes->getForbiddenAPIFields();
	}
}
