<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */


namespace onOffice\WPlugin\Region;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 */

class RegionController
{
	/** @var array */
	private $_regions = [];

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;


	/**
	 *
	 * @param bool $init
	 * @param SDKWrapper $pSDKWrapper
	 *
	 */

	public function __construct(bool $init = true, SDKWrapper $pSDKWrapper = null)
	{
		$this->_pSDKWrapper = $pSDKWrapper ?? new SDKWrapper;

		if ($init) {
			$this->fetchRegions();
		}
	}


	/**
	 *
	 */

	public function fetchRegions()
	{
		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'regions');
		$pApiClientAction->setParameters(['language' => Language::getDefault()]);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$this->_regions = $this->createRegionObjects($pApiClientAction->getResultRecords());
	}


	/**
	 *
	 * @param array $regionList
	 * @param bool $level1
	 * @return Region[]
	 *
	 */

	private function createRegionObjects(array $regionList, bool $level1 = true): array
	{
		$regions = [];
		foreach ($regionList as $regionProperties) {
			$elements = $regionProperties;
			if ($level1) {
				$elements = $regionProperties['elements'];
			}

			$pRegion = new Region($elements['id'], Language::getDefault());
			$pRegion->setName($elements['name'] ?? '');
			$pRegion->setDescription($elements['description'] ?? '');
			$pRegion->setPostalCodes($elements['postalcodes'] ?? []);
			$pRegion->setState($elements['state'] ?? '');
			$pRegion->setCountry($elements['country'] ?? '');

			$children = $this->createRegionObjects($elements['children'], false);
			$pRegion->setChildren($children);

			$regions []= $pRegion;
		}

		return $regions;
	}


	/**
	 *
	 * @return Region[]
	 *
	 */

	public function getRegions(): array
	{
		return $this->_regions;
	}


	/**
	 *
	 * @param string $key
	 * @param Region $pParentRegion
	 * @return Region
	 *
	 */

	public function getRegionByKey(string $key, Region $pParentRegion = null)
	{
		if ($pParentRegion === null) {
			$outerLevel = $this->_regions;
		} else {
			$outerLevel = $pParentRegion->getChildren();
		}

		foreach ($outerLevel as $pRegion) {
			if ($pRegion->getId() === $key) {
				return $pRegion;
			}
		}

		$pResult = null;
		foreach ($outerLevel as $pRegion) {
			$pResult = $this->getRegionByKey($key, $pRegion);
			if (!is_null($pResult)) {
				return $pResult;
			}
		}
	}


	/**
	 *
	 * @param string $key
	 * @return string[]
	 *
	 */

	public function getSubRegionsByParentRegion(string $key): array
	{
		$pRegion = $this->getRegionByKey($key);
		if ($pRegion !== null) {
			return $this->getRegionNamesOfChildRegions($pRegion);
		}

		return [];
	}


	/**
	 *
	 * @param Region $pRegion
	 * @return string[]
	 *
	 */

	private function getRegionNamesOfChildRegions(Region $pRegion): array
	{
		$childRegions = array($pRegion->getId());
		foreach ($pRegion->getChildren() as $pChildRegion) {
			$childRegions = array_merge($childRegions,
				$this->getRegionNamesOfChildRegions($pChildRegion));
		}

		return $childRegions;
	}


	/**
	 *
	 * @return SDKWrapper
	 *
	 */

	public function getSDKWrapper(): SDKWrapper
	{
		return $this->_pSDKWrapper;
	}
}
