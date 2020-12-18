<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
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

use Generator;
use onOffice\WPlugin\GeoPosition;
use onOffice\WPlugin\Types\FieldTypes;
use function __;

/**
 *
 */

class FieldCategoryToFieldConverterSearchCriteriaGeoBackend
	implements FieldCategoryToFieldConverter
{
	/** @var FieldRowConverterSearchCriteria */
	private $_pFieldRowConverter = null;


	/**
	 *
	 * @param FieldRowConverterSearchCriteria $pFieldRowConverter
	 *
	 */

	public function __construct(FieldRowConverterSearchCriteria $pFieldRowConverter)
	{
		$this->_pFieldRowConverter = $pFieldRowConverter;
	}


	/**
	 *
	 * @param array $category
	 * @return Generator
	 *
	 */

	public function convertCategory(array $category): Generator
	{
		if ($category['name'] === 'Umkreis') {
			yield GeoPosition::FIELD_GEO_POSITION => $this->buildGeoPositionField();
			return;
		}
	}



	/**
	 *
	 * @return array
	 *
	 */

	private function buildGeoPositionField(): array
	{
		$field = [
			'id' => GeoPosition::FIELD_GEO_POSITION,
			'name' => __('Geo Position', 'onoffice-for-wp-websites'),
			'type' => FieldTypes::FIELD_TYPE_FLOAT,
		];
		return $this->_pFieldRowConverter->convertRow($field);
	}
}
