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

namespace onOffice\WPlugin\Region;

/**
 *
 */

class RegionFilter
{
	/**
	 * @param Region[] $regions
	 * @param int $level
	 * @return array
	 */
	public function buildRegions(array $regions, int $level = 1): array
	{
		$result = [];

		foreach ($regions as $pRegion) {
			$regionParts = [$pRegion->getName(), $pRegion->getState(), $pRegion->getCountry()];
			$result[$pRegion->getId()] = str_repeat('–', $level - 1)
				.' '.implode(', ', array_filter($regionParts));
			$result = array_merge($result, $this->buildRegions($pRegion->getChildren(), $level + 1));
		}
		return $result;
	}


	/**
	 * @param Region[] $regions
	 * @return array
	 */
	public function collectLabelOnlyValues(array $regions): array
	{
		$results = [];
		foreach ($regions as $pRegion) {
			if ($pRegion->getChildren() !== []) {
				$results[] = $pRegion->getId();
				$results = array_merge($results, $this->collectLabelOnlyValues($pRegion->getChildren()));
			}
		}
		return $results;
	}
}