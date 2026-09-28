<?php

/**
 *
 *    Copyright (C) 2025 onOffice GmbH
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

namespace onOffice\WPlugin\Field\Collection;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Field\UnknownFieldException;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\FieldsCollection;

/**
 * The API delivers the country of the radius search (search criteria field `range_land`)
 * as a single select without any permitted values. An empty select is rendered as a
 * dropdown without options, which Tom Select turns into a free text box - the country is
 * then either not transmitted at all or transmitted as arbitrary text.
 *
 * The estate field `land` carries the country list the search criteria field expects.
 * Fieldnames::setPermittedValuesForEstateSearchFields() uses the same fallback for the
 * country field of the estate list search.
 */
class FieldsCollectionCountryValuesForSearchCriteria
{
	/** estate field the country list is taken from */
	const FIELD_ESTATE_COUNTRY = 'land';

	/**
	 * Fills the country list of the radius search field in place. Does nothing if the
	 * API already delivers permitted values for it, so an account that has its own list
	 * keeps it.
	 *
	 * @param FieldsCollection $pFieldsCollection
	 */
	public function addCountryValues(FieldsCollection $pFieldsCollection)
	{
		$searchCriteriaCountry = (new GeoPosition)->getSearchCriteriaFields()
			[GeoPosition::ESTATE_LIST_SEARCH_COUNTRY];

		try {
			$pFieldRangeCountry = $pFieldsCollection->getFieldByModuleAndName
				(onOfficeSDK::MODULE_SEARCHCRITERIA, $searchCriteriaCountry);

			if ($pFieldRangeCountry->getPermittedvalues() !== []) {
				return;
			}

			$pFieldEstateCountry = $pFieldsCollection->getFieldByModuleAndName
				(onOfficeSDK::MODULE_ESTATE, self::FIELD_ESTATE_COUNTRY);
		} catch (UnknownFieldException $pException) {
			return;
		}

		$pFieldRangeCountry->setPermittedvalues($pFieldEstateCountry->getPermittedvalues());
	}
}
