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

use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Utility\__String;

abstract class EstateViewFieldModifierTypeEstateGeoBase
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
	 * @return array
	 */
	public function getAPIFields(): array
	{
		$geoSpecific = [
			'virtualStreet',
			'virtualHouseNumber',
			'laengengrad',
			'breitengrad',
			'virtualAddress',
			'virtualLatitude',
			'virtualLongitude',
			'objektadresse_freigeben',
			'strasse',
		];

		$apiFields = array_merge($this->_viewFields, $geoSpecific);
		return array_unique($this->editViewFieldsForApiGeoPosition($apiFields));
	}

	/**
	 * @return array
	 */
	public function getVisibleFields(): array
	{
		return $this->editViewFieldsForApiGeoPosition($this->_viewFields);
	}

	/**
	 * @param array $record
	 * @return array
	 */
	public function reduceRecord(array $record): array
	{
		if (1 == $record['virtualAddress']) {
			$record['strasse'] = $record['virtualStreet'] ?? '';
			$record['hausnummer'] = $record['virtualHouseNumber'] ?? '';
			$record['laengengrad'] = $record['virtualLongitude'] ?? .0;
			$record['breitengrad'] = $record['virtualLatitude'] ?? .0;
		} elseif (0 == $record['objektadresse_freigeben'] ||
			__String::getNew($record['strasse'] ?? '')->isEmpty()) {
			$record['laengengrad'] = 0;
			$record['breitengrad'] = 0;
			unset($record['strasse']);
		}
		return $record;
	}

	/**
	 * @param array $viewFields
	 * @return array
	 */
	protected function editViewFieldsForApiGeoPosition(array $viewFields): array
	{
		$pos = array_search(GeoPosition::FIELD_GEO_POSITION, $viewFields);

		if ($pos !== false) {
			unset($viewFields[$pos]);
		}

		return $viewFields;
	}

	/** @return array */
	protected function getViewFields(): array
		{ return $this->_viewFields; }
}
