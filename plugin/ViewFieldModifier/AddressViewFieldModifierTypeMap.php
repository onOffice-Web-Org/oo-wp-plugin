<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2024, onOffice(R) GmbH
 *
 */

class AddressViewFieldModifierTypeMap
	implements ViewFieldModifierTypeBase
{
	/** @var array */
	private $_viewFields = [];

	/**
	 * @param array $viewFields
	 */
	public function __construct(array $viewFields)
	{
		$this->_viewFields = $viewFields;
	}

	/**
	 *
	 * @return array
	 *
	 */

	public function getAPIFields(): array
	{
		$mapSpecific = [
			'Vorname',
			'Name',
			'Zusatz1',
			'laengengrad',
			'breitengrad',
		];

		return $mapSpecific;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getVisibleFields(): array
	{
		$mapFields = [
			'Vorname',
			'Name',
			'Zusatz1',
			'laengengrad',
			'breitengrad',
		];

		return $this->merge($mapFields, $this->_viewFields);
	}

	/**
	 * @param array $record
	 * @return array
	 */
	public function reduceRecord(array $record): array
	{
		return $record;
	}

	/**
	 *
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 *
	 */

	private function merge(array $array1, array $array2): array
	{
		return array_values(array_unique(array_merge($array1, $array2)));
	}
}
