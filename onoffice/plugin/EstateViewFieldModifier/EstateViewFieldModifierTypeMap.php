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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class EstateViewFieldModifierTypeMap
	implements EstateViewFieldModifierTypeBase
{
	/**
	 *
	 * @return array
	 *
	 */

	public function getAPIFields()
	{
		return array(
			'virtualStreet',
			'virtualHouseNumber',
			'showGoogleMap',
			'laengengrad',
			'breitengrad',
			'virtualAddress',
			'virtualLatitude',
			'virtualLongitude',
			'strasse',
			'hausnummer',
			'objekttitel',
		);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getVisibleFields()
	{
		return array(
			'showGoogleMap',
			'laengengrad',
			'breitengrad',
			'virtualAddress',
			'strasse',
			'hausnummer',
			'objekttitel',
		);
	}


	/**
	 *
	 * @param array $record
	 * @return array
	 *
	 */

	public function reduceRecord(array $record)
	{
		if ( 1 == $record['virtualAddress'] ) {
			if (isset($record['strasse'])) {
				$record['strasse'] = $record['virtualStreet'];
			}

			if (isset($record['hausnummer'])) {
				$record['hausnummer'] = $record['virtualHouseNumber'];
			}

			$record['laengengrad'] = $record['virtualLongitude'];
			$record['breitengrad'] = $record['virtualLatitude'];
		}

		return $record;
	}
}
