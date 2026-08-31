<?php

/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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

namespace onOffice\WPlugin\Controller;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\SDKWrapper;

/**
 * Loads the ids of the child estates related to a given parent/main estate via the Enterprise
 * COMPLEX_ESTATE_UNITS object relation (same relation used by the existing detail-page "units"
 * sub-list, see EstateUnits::loadByMainEstates()). Used by the standalone "complexunits" list
 * type, which - unlike "units" - is not embedded in a detail page and therefore has no
 * page-context estate id to derive the parent from; it is given the parent estate id explicitly.
 */
class ComplexUnitsChildEstateIdsLoader
{
	/** @var SDKWrapper */
	private $_pSDKWrapper;

	/**
	 * @param SDKWrapper $pSDKWrapper
	 */
	public function __construct(SDKWrapper $pSDKWrapper)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
	}

	/**
	 * @param string $parentEstateId onOffice internal estate id of the parent/main estate
	 * @return int[]
	 */
	public function loadChildEstateIds(string $parentEstateId): array
	{
		if ((int) $parentEstateId <= 0) {
			return [];
		}

		$pAction = new APIClientActionGeneric($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'idsfromrelation');
		$pAction->setParameters([
			'relationtype' => onOfficeSDK::RELATION_TYPE_COMPLEX_ESTATE_UNITS,
			'parentids' => [(int) $parentEstateId],
		]);
		$pAction->addRequestToQueue()->sendRequests();

		if (!$pAction->getResultStatus()) {
			return [];
		}

		try {
			$records = $pAction->getResultRecords();
		} catch (ApiClientException $pException) {
			return [];
		}

		foreach ($records as $properties) {
			$elements = $properties['elements'] ?? [];
			$childIds = $elements[(int) $parentEstateId] ?? [];
			return array_map('intval', $childIds);
		}

		return [];
	}
}
