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

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\InputVariableReader;
use onOffice\WPlugin\Filter\DefaultFilterBuilderListViewEnvironmentDefault;
use onOffice\WPlugin\Filter\FilterBuilderInputVariables;
use onOffice\WPlugin\Region\RegionController;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassDefaultFilterBuilderListViewEnvironmentDefault
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testGetFilterBuilderInputVariables()
	{
		$pInstance = new DefaultFilterBuilderListViewEnvironmentDefault();
		$pFilterBuilderInputVariables = $pInstance->getFilterBuilderInputVariables();
		$this->assertInstanceOf(FilterBuilderInputVariables::class, $pFilterBuilderInputVariables);
		$this->assertEquals(onOfficeSDK::MODULE_ESTATE, $pFilterBuilderInputVariables->getModule());
	}


	/**
	 *
	 */

	public function testGetInputVariableReader()
	{
		$pInstance = new DefaultFilterBuilderListViewEnvironmentDefault();
		$this->assertInstanceOf(InputVariableReader::class, $pInstance->getInputVariableReader());
	}


	/**
	 *
	 */

	public function testGetRegionController()
	{
		$pInstance = new DefaultFilterBuilderListViewEnvironmentDefault();
		$this->assertInstanceOf(RegionController::class, $pInstance->getRegionController());
	}
}
