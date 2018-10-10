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

namespace onOffice\tests;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\EstateUnitsConfigurationBase;
use onOffice\WPlugin\Controller\EstateUnitsConfigurationTest;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\EstateUnits;
use onOffice\WPlugin\Filter\DefaultFilterBuilderPresetEstateIds;
use WP_UnitTestCase;
use function json_decode;



/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassEstateUnits
	extends WP_UnitTestCase
{
	/** @var EstateUnitsConfigurationBase */
	private $_pEstateUnitsConfiguration = null;

	/** @var EstateUnits */
	private $_pEstateUnits = null;

	/** @var array */
	private $_estateData = [
		5533 => ['Id' => 5533, 'Ort' => 'Aachen', 'objekttitel' => 'Haus in Aachen'],
		5539 => ['Id' => 5539, 'Ort' => 'Burtscheid', 'objekttitel' => 'Haus in Burtscheid'],
		5545 => ['Id' => 5545, 'Ort' => 'Köln', 'objekttitel' => 'Wohnung in Köln'],
		15 => ['Id' => 15, 'Ort' => 'Hamburg', 'objekttitel' => 'Villa in Hamburg'],
		1078 => ['Id' => 1078, 'Ort' => 'Berlin', 'objekttitel' => 'Stadthaus in Berlin'],
		1087 => ['Id' => 1087, 'Ort' => 'München', 'objekttitel' => 'Brauhaus in München'],
		14 => ['Id' => 14, 'Ort' => 'Frankfurt', 'objekttitel' => 'Bürogebäude in Frankfurt'],
	];

	/** @var string Expected result for the first five records */
	private $_templateResult = 'Id: 5533
Ort: Aachen
objekttitel: Haus in Aachen
--
Id: 5539
Ort: Burtscheid
objekttitel: Haus in Burtscheid
--
Id: 5545
Ort: Köln
objekttitel: Wohnung in Köln
--
Id: 15
Ort: Hamburg
objekttitel: Villa in Hamburg
--
Id: 1078
Ort: Berlin
objekttitel: Stadthaus in Berlin
--
Id: 1087
Ort: München
objekttitel: Brauhaus in München
--
Id: 14
Ort: Frankfurt
objekttitel: Bürogebäude in Frankfurt
--
';


	/**
	 *
	 */

	public function setUp()
	{
		parent::setUp();

		$apiResponseGetUnits = file_get_contents(__DIR__.'/resources/ApiResponseGetRelationUnits.json');
		$apiResponseGetUnitsArray = json_decode($apiResponseGetUnits, true);

		$pDataView = new DataListView(1, 'test_units_view');
		$pDataView->setTemplate('resources/templates/unitlist.php');
		$this->_pEstateUnitsConfiguration = new EstateUnitsConfigurationTest($pDataView);
		$pSDKWrapperMocker = $this->_pEstateUnitsConfiguration->getSDKWrapper();
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'idsfromrelation', '', [
			'relationtype' => onOfficeSDK::RELATION_TYPE_COMPLEX_ESTATE_UNITS,
			'parentids' => [300, 303],
		], null, $apiResponseGetUnitsArray);


		$this->_pEstateUnits = new EstateUnits($pDataView, $this->_pEstateUnitsConfiguration);
		$pEstateList = new EstateListMocker(new DataListView(1, 'test'));
		$pEstateList->loadEstates();
		$pEstateList->setEstateData([300 => ['Id' => 300], 303 => ['Id' => 303]]);
		$this->_pEstateUnits->loadByMainEstates($pEstateList);
	}


	/**
	 *
	 */

	public function testSubEstateIds()
	{
		$expectedIds = [5533, 5539, 5545, 15, 1078, 1087, 14];
		$this->assertEquals($expectedIds, $this->_pEstateUnits->getSubEstateIds(300));
		$this->assertEquals([5500], $this->_pEstateUnits->getSubEstateIds(303));

		// unknown estate
		$this->assertEquals([], $this->_pEstateUnits->getSubEstateIds(400));
	}


	/**
	 *
	 */

	public function testSubEstateCount()
	{
		$this->assertEquals(7, $this->_pEstateUnits->getSubEstateCount(300));
		$this->assertEquals(1, $this->_pEstateUnits->getSubEstateCount(303));
		$this->assertEquals(0, $this->_pEstateUnits->getSubEstateCount(500));
	}


	/**
	 *
	 */

	public function testGenerateHtmlOutput()
	{
		$pEstateList = $this->_pEstateUnitsConfiguration->getEstateList();
		$pEstateList->setEstateData($this->_estateData);
		$output = $this->_pEstateUnits->generateHtmlOutput(300);

		$this->assertInstanceOf(DefaultFilterBuilderPresetEstateIds::class,
			$pEstateList->getDefaultFilterBuilder());
		$this->assertEquals($this->_templateResult, $output);
	}
}
