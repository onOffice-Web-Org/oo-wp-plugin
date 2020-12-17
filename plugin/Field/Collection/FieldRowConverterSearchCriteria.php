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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Region\Region;
use onOffice\WPlugin\Region\RegionController;
use onOffice\WPlugin\Region\RegionFilter;
use onOffice\WPlugin\Types\FieldTypes;
use function __;

/**
 *
 */

class FieldRowConverterSearchCriteria
{
	/** @var RegionController */
	private $_pRegionController;

	/** @var RegionFilter */
	private $_pRegionFilter;

	/**
	 * @param RegionController $pRegionController
	 */
	public function __construct(RegionController $pRegionController, RegionFilter $pRegionFilter)
	{
		$this->_pRegionController = $pRegionController;
		$this->_pRegionFilter = $pRegionFilter;
	}

	/**
	 *
	 * @param array $input
	 * @return array
	 *
	 */

	public function convertRow(array $input): array
	{
		$input['label'] = $input['name'];
		unset($input['name']);

		$input['tablename'] = 'ObjSuchkriterien';
		$input['module'] = onOfficeSDK::MODULE_SEARCHCRITERIA;
		$input['content'] = __('Search Criteria', 'onoffice-for-wp-websites');
		$input['permittedvalues'] = $input['values'] ?? [];

		if (FieldTypes::isRegZusatzSearchcritTypes($input['type'])) {
			$input['type'] = FieldTypes::FIELD_TYPE_SINGLESELECT;
			$this->_pRegionController->fetchRegions();
			$regions = $this->_pRegionController->getRegions();
			$input['permittedvalues'] = $this->_pRegionFilter->buildRegions($regions);
			$input['labelOnlyValues'] = $this->_pRegionFilter->collectLabelOnlyValues($regions);
		}
		unset($input['values']);
		$input['rangefield'] = (bool)($input['rangefield'] ?? false);
		return $input;
	}
}
