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

declare(strict_types=1);

namespace onOffice\WPlugin\Field;

use onOffice\SDK\onOfficeSDK;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class FieldDefaultSorting
{
	/** @var array */
	private $_defaultSortByFields = [
		onOfficeSDK::MODULE_ADDRESS => [
			'KdNr',
			'Eintragsdatum',
			'Name',
		],
		onOfficeSDK::MODULE_ESTATE => [
			'kaufpreis',
			'kaltmiete',
			'pacht',
			'wohnflaeche',
			'anzahl_zimmer',
			'ort',
			'grundstuecksflaeche',
			'gesamtflaeche',
			'erstellt_am',
			'geandert_am',
			'verkauft_am',
			'letzte_aktion',
			'objektnr_extern',
		],
	];



	/**
	 *
	 * @return array
	 *
	 */

	public function getDefaultSortByFields(string $module): array
	{
		return $this->_defaultSortByFields[$module] ?? [];
	}
}
