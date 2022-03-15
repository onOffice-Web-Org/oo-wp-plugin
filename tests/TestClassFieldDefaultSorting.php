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

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\FieldDefaultSorting;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassFieldDefaultSorting
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetDefaultSortByFieldsAddress()
	{
		$pFieldDefaultSorting = new FieldDefaultSorting();
		$sorting = $pFieldDefaultSorting->getDefaultSortByFields(onOfficeSDK::MODULE_ADDRESS);
		$expectation = [
			'KdNr',
			'Eintragsdatum',
			'Name',
		];

		$this->assertEquals($expectation, $sorting);
	}


	/**
	 *
	 */

	public function testGetDefaultSortByFieldsEstate()
	{
		$pFieldDefaultSorting = new FieldDefaultSorting();
		$sorting = $pFieldDefaultSorting->getDefaultSortByFields(onOfficeSDK::MODULE_ESTATE);
		$expectation = [
			'erstellt_am',
			'letzte_aktion',
			'verkauft_am',
			'objektnr_extern',
			'kaufpreis',
			'kaltmiete',
			'wohnflaeche',
			'grundstuecksflaeche',
			'gesamtflaeche',
			'anzahl_zimmer',
			'anzahl_badezimmer'
		];

		$this->assertEquals($expectation, $sorting);
	}


	/**
	 *
	 */

	public function testGetDefaultSortByFieldsUnknownModule()
	{
		$pFieldDefaultSorting = new FieldDefaultSorting();
		$sorting = $pFieldDefaultSorting->getDefaultSortByFields('unknown module');
		$this->assertEquals([], $sorting);
	}
}
