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
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\Region\Region;
use onOffice\WPlugin\Region\RegionController;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;
use function json_decode;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassRegionController
	extends WP_UnitTestCase
{
	/** @var RegionController */
	private $_pRegionController = null;


	/**
	 *
	 */

	public function testConstruct()
	{
		$pRegionController = new RegionController(false);
		$this->assertEquals([], $pRegionController->getRegions());
		$this->assertInstanceOf(SDKWrapper::class, $pRegionController->getSDKWrapper());
	}


	/**
	 *
	 */

	public function testGetRegions()
	{
		$result = $this->_pRegionController->getRegions();
		$this->assertEquals(33, count($result));

		foreach ($result as $pRegion) {
			$this->assertInstanceOf(Region::class, $pRegion);
		}
	}


	/**
	 *
	 */

	public function testGetRegionByKey()
	{
		$pRegion = $this->_pRegionController->getRegionByKey('openGeoDb_Region_14400');
		$this->assertEquals('Berumbur (Gemeinde)', $pRegion->getName());
		$this->assertCount(2, $pRegion->getChildren());
		$this->assertEquals('openGeoDb_Region_14400', $pRegion->getId());
		$this->assertEquals('', $pRegion->getDescription());
		$this->assertEquals('Lower Saxony', $pRegion->getState());
		$this->assertEquals('Germany', $pRegion->getCountry());
		$this->assertEquals(['26524'], $pRegion->getPostalCodes());
		$this->assertEquals('ENG', $pRegion->getLanguage());
	}


	/**
	 *
	 */

	public function testGetSubRegionsByParentRegion()
	{
		$subRegions = $this->_pRegionController->getSubRegionsByParentRegion('TUeRichtungStuttgart');
		$this->assertCount(9, $subRegions);
		$this->assertEquals([
			'TUeRichtungStuttgart',
			'Altenburg',
			'Doernach',
			'Gniebel',
			'Kirchentellinsfurt',
			'Pliezhausen',
			'Ruebgarten',
			'WalddorfHaeslach',
			'Wannweil',
		], $subRegions);

		$this->assertEquals([], $this->_pRegionController->getSubRegionsByParentRegion('Unknown'));
	}


	/**
	 *
	 * @before
	 *
	 */

	public function prepareTest()
	{
		$pSDKWrapper = new SDKWrapperMocker();
		$responseJson = file_get_contents(__DIR__.'/resources/ApiResponseGetRegionsENG.json');
		$response = json_decode($responseJson, true);
		$parameters = ['language' => 'ENG'];

		$pSDKWrapper->addResponseByParameters
			(onOfficeSDK::ACTION_ID_GET, 'regions', '', $parameters, null, $response);

		$this->_pRegionController = new RegionController(true, $pSDKWrapper);
	}
}
