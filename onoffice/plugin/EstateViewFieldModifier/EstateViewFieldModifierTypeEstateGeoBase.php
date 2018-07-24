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

namespace onOffice\WPlugin\EstateViewFieldModifier;

use onOffice\WPlugin\Utility\__String;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

abstract class EstateViewFieldModifierTypeEstateGeoBase
	implements EstateViewFieldModifierTypeBase
{
	/**
	 *
	 * @return array
	 *
	 */

	public function getAPIFields(): array
	{
		return [
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
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getVisibleFields(): array
	{
		return [];
	}


	/**
	 *
	 * @param array $record
	 * @return array
	 *
	 */

	public function reduceRecord(array $record): array
	{
		if (1 == $record['virtualAddress']) {
			if (isset($record['strasse'])) {
				$record['strasse'] = $record['virtualStreet'];
			}

			if (isset($record['hausnummer'])) {
				$record['hausnummer'] = $record['virtualHouseNumber'];
			}

			if (isset($record['laengengrad'])) {
				$record['laengengrad'] = $record['virtualLongitude'];
			}
			if (isset($record['breitengrad'])) {
				$record['breitengrad'] = $record['virtualLatitude'];
			}
		} elseif (0 == $record['objektadresse_freigeben'] ||
			__String::getNew($record['strasse'])->isEmpty()) {
			$record['laengengrad'] = 0;
			$record['breitengrad'] = 0;
			unset($record['strasse']);
		}

		return $record;
	}
}
