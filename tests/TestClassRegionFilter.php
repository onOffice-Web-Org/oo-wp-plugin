<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

use onOffice\WPlugin\Region\Region;
use onOffice\WPlugin\Region\RegionFilter;

class TestClassRegionFilter
	extends \WP_UnitTestCase
{
	/** @var RegionFilter */
	private $_pSubject = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pSubject = new RegionFilter;
	}

	/**
	 * @return array
	 */
	public function dataProviderRegions(): array
	{
		$pRegion1_1_1 = new Region('region1.1.1', 'ENG');
		$pRegion1_1_1->setCountry('country1');
		$pRegion1_1_1->setState('State 1');
		$pRegion1_1_1->setname('Region 1.1.1');

		$pRegion1_1_2 = new Region('region1.1.2', 'ENG');
		$pRegion1_1_2->setCountry('country1');
		$pRegion1_1_2->setState('State 1');
		$pRegion1_1_2->setname('Region 1.1.2');

		$pRegion1_1 = new Region('region1.1', 'ENG');
		$pRegion1_1->setCountry('country1');
		$pRegion1_1->setName('Region 1.1');
		$pRegion1_1->setState('State 1');
		$pRegion1_1->setChildren([$pRegion1_1_1, $pRegion1_1_2]);

		$pRegion1 = new Region('region1', 'ENG');
		$pRegion1->setCountry('country1');
		$pRegion1->setName('Region 1');
		$pRegion1->setState('State 1');
		$pRegion1->setChildren([$pRegion1_1]);

		$pRegion2 = new Region('region2', 'ENG');
		$pRegion2->setCountry('country1');
		$pRegion2->setName('Region 2');
		$pRegion2->setState('State 1');
		$pRegion2->setChildren([$pRegion1_1]);

		return [
			[[$pRegion1, $pRegion2]],
		];
	}

	/**
	 * @dataProvider dataProviderRegions
	 * @param array $regions
	 */
	public function testBuildRegions(array $regions)
	{
		$regionLabels = $this->_pSubject->buildRegions($regions);
		$expectedResult = [
			'region1' => ' Region 1, State 1, country1',
			'region1.1' => '– Region 1.1, State 1, country1',
			'region1.1.1' => '–– Region 1.1.1, State 1, country1',
			'region1.1.2' => '–– Region 1.1.2, State 1, country1',
			'region2' => ' Region 2, State 1, country1',
		];
		$this->assertEquals($expectedResult, $regionLabels);
	}

	/**
	 * @dataProvider dataProviderRegions
	 * @param array $regions
	 */
	public function testCollectLabelOnlyValues(array $regions)
	{
		$labelOnlyValues = $this->_pSubject->collectLabelOnlyValues($regions);
		$expectedValues = ['region1', 'region1.1', 'region2', 'region1.1'];
		$this->assertEquals($expectedValues, $labelOnlyValues);
	}
}