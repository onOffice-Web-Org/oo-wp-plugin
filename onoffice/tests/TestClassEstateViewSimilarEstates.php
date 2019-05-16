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

use onOffice\tests\EstateListMocker;
use onOffice\WPlugin\Controller\EstateViewSimilarEstates;
use onOffice\WPlugin\Controller\EstateViewSimilarEstatesEnvironmentTest;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataViewSimilarEstates;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassEstateViewSimilarEstates
	extends WP_UnitTestCase
{
	/** @var array */
	private $_resultSubRecords = [
		305 => [
			'Id' => 305,
			'laengengrad' => 50.3333,
			'breitengrad' => 13.777,
			'strasse' => 'Teststreet',
			'plz' => '12345',
			'land' => 'Testcountry',
		],
		309 => [
			'Id' => 309,
			'laengengrad' => 50.3273,
			'breitengrad' => 13.2222,
			'strasse' => 'Testotherstreet',
			'plz' => '12347',
			'land' => 'Testcountry',
		],
	];

	/** @var array */
	private $_mainRecords = [
		300 => [
			'Id' => 300,
			'laengengrad' => 50.24584,
			'breitengrad' => 13.3847,
			'strasse' => '',
			'plz' => '',
			'land' => '',
			'vermarktungsart' => 'kauf',
			'objektart' => 'haus',
		]
	];

	/** @var string */
	private $_expectedTemplateOutput = 'Id: 305
laengengrad: 50.3333
breitengrad: 13.777
strasse: Teststreet
plz: 12345
land: Testcountry
--
Id: 309
laengengrad: 50.3273
breitengrad: 13.2222
strasse: Testotherstreet
plz: 12347
land: Testcountry
--
';

	/** @var EstateViewSimilarEstates */
	private $_pEstateViewSimilarEstates = null;


	/**
	 *
	 */

	public function setUp()
	{
		parent::setUp();
		$pDataDetailView = new DataViewSimilarEstates();
		$pDataDetailView->setTemplate('resources/templates/unitlist.php');
		$pEnvironment = new EstateViewSimilarEstatesEnvironmentTest($pDataDetailView);
		$pEnvironment->getEstateList()->setEstateData($this->_resultSubRecords);
		$pEnvironment->getEstateList()->loadEstates();

		$pDataView = new DataListView(1, 'test');
		$pDataView->setFields(['Id', 'laengengrad', 'breitengrad', 'strasse', 'plz', 'land']);
		$pEstateListBase = new EstateListMocker($pDataView);
		$pEstateListBase->setEstateData($this->_mainRecords);
		$pEstateListBase->loadEstates();

		$this->_pEstateViewSimilarEstates = new EstateViewSimilarEstates
			($pDataDetailView, $pEnvironment);
		$this->_pEstateViewSimilarEstates->loadByMainEstates($pEstateListBase);
	}


	/**
	 *
	 */

	public function testSubEstateCount()
	{
		$this->assertEquals(2, $this->_pEstateViewSimilarEstates->getSubEstateCount(300));
		$this->assertEquals(0, $this->_pEstateViewSimilarEstates->getSubEstateCount(2));
	}


	/**
	 *
	 */

	public function testGetSubEstateIds()
	{
		$this->assertEquals([305, 309], $this->_pEstateViewSimilarEstates->getSubEstateIds(300));
		$this->assertEquals([], $this->_pEstateViewSimilarEstates->getSubEstateIds(8));
	}


	/**
	 *
	 */

	public function testGenerateHtmlOutput()
	{
		$output = $this->_pEstateViewSimilarEstates->generateHtmlOutput(300);
		$this->assertEquals($this->_expectedTemplateOutput, $output);
	}
}
