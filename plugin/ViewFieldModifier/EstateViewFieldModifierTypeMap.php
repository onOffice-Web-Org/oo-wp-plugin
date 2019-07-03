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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateViewFieldModifierTypeMap
	extends EstateViewFieldModifierTypeEstateGeoBase
{
	/**
	 *
	 * @return array
	 *
	 */

	public function getAPIFields(): array
	{
		$parent = parent::getAPIFields();
		$mapSpecific = [
			'showGoogleMap',
			'strasse',
			'hausnummer',
			'objekttitel',
		];

		return $this->merge($parent, $mapSpecific);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getVisibleFields(): array
	{
		$mapFields = [
			'showGoogleMap',
			'laengengrad',
			'breitengrad',
			'virtualAddress',
			'objekttitel',
		];

		return $this->merge($mapFields, parent::getVisibleFields());
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
