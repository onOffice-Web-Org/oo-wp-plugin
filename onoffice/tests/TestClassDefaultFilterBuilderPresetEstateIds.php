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

use onOffice\WPlugin\Filter\DefaultFilterBuilderPresetEstateIds;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassDefaultFilterBuilderPresetEstateIds
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetEstateIds()
	{
		$pDefaultFilterBuilder = new DefaultFilterBuilderPresetEstateIds([3, 4, 5, 29]);
		$this->assertEquals([3, 4, 5, 29], $pDefaultFilterBuilder->getEstateIds());
	}


	/**
	 *
	 */

	public function testBuildFilter()
	{
		$pDefaultFilterBuilder = new DefaultFilterBuilderPresetEstateIds([1337, 123, 5432]);
		$result = $pDefaultFilterBuilder->buildFilter();
		$expectation = [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'Id' => [
				['op' => 'in', 'val' => [1337, 123, 5432]],
			],
		];
		$this->assertEquals($expectation, $result);
	}


	/**
	 *
	 */

	public function testBuildEmptyFilter()
	{
		$pDefaultFilterBuilder = new DefaultFilterBuilderPresetEstateIds([]);
		$result = $pDefaultFilterBuilder->buildFilter();
		$expectation = [
			'veroeffentlichen' => [
				['op' => '=', 'val' => 1],
			],
			'Id' => [
				['op' => 'in', 'val' => []],
			],
		];
		$this->assertEquals($expectation, $result);
	}
}
