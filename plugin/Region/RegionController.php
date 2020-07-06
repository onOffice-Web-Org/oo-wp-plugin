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
use onOffice\WPlugin\API\ApiClientException;
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
	 * @throws ApiClientException
	 */
	public function fetchRegions()
	{
		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'regions');
		$pApiClientAction->setParameters(['language' => Language::getDefault()]);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$this->_regions = $this->createRegionObjectsRegionOne($pApiClientAction->getResultRecords());
	}

	/**
	 * @param array $regionList
	 * @param Region|null $pParent
	 * @return Region[]
	 */
	private function createRegionObjects(array $regionList, Region $pParent): array
	{
		$regions = [];
		foreach ($regionList as $regionProperties) {
			$pRegion = $this->buildBaseRegionElement($regionProperties);
			$pRegion->setChildren($this->createRegionObjects($regionProperties['children'], $pRegion));
			$pRegion->setParent($pParent);
			$regions []= $pRegion;
		}
		return $regions;
	}

	/**
	 * @param array $regionList
	 * @return array
	 */
	private function createRegionObjectsRegionOne(array $regionList): array
	{
		$regions = [];
		foreach ($regionList as $regionProperties) {
			$pRegion = $this->buildBaseRegionElement($regionProperties['elements']);
			$children = $this->createRegionObjects($regionProperties['elements']['children'], $pRegion);
			$pRegion->setChildren($children);
			$regions []= $pRegion;
		}
		return $regions;
	}

	/**
	 * @param array $elements
	 * @return Region
	 */
	private function buildBaseRegionElement(array $elements): Region
	{
		$pRegion = new Region($elements['id'], Language::getDefault());
		$pRegion->setName($elements['name'] ?? '');
		$pRegion->setDescription($elements['description'] ?? '');
		$pRegion->setPostalCodes($elements['postalcodes'] ?? []);
		$pRegion->setState($elements['state'] ?? '');
		$pRegion->setCountry($elements['country'] ?? '');
		return $pRegion;
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
	 * @return Region|null
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
		return null;
	}

	/**
	 * @param array $childRegions
	 * @return array
	 * @throws ApiClientException
	 */
	public function getParentRegionsByChildRegionKeys(array $childRegions): array
	{
		$newPermitted = [];
		$this->fetchRegions();
		// collect allowed (parent) regions
		foreach ($childRegions as $permittedRegion) {
			$pRegionPermitted = $this->getRegionByKey($permittedRegion);
			while ($pRegionPermitted !== null) {
				$newPermitted[$pRegionPermitted->getId()] = $pRegionPermitted;
				$pRegionPermitted = $pRegionPermitted->getParent();
			}
		}

		// cleanup child regions
		foreach ($newPermitted as $pRegion) {
			do {
				$regionChildren = $pRegion->getChildren();
				$regionChildren = array_filter($regionChildren, static function(Region $pChild) use ($newPermitted) {
					return isset($newPermitted[$pChild->getId()]);
				});
				$pRegion->setChildren($regionChildren);
			} while ($pRegion = $pRegion->getParent());
		}

		// leave only root elements
		$newPermitted = array_filter($newPermitted, static function($pEntry) {
			return $pEntry->getParent() === null;
		});
		usort($newPermitted, function (Region $pA, Region $pB) {
			return strnatcasecmp($pA->getName(), $pB->getName());
		});
		return $newPermitted;
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
