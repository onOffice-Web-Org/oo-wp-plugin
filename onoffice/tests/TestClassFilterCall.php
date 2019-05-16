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
use onOffice\WPlugin\FilterCall;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;
use function json_decode;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class TestClassFilterCall
	extends WP_UnitTestCase
{
	/** @var FilterCall */
	private $_pFilterCall = null;


	/**
	 *
	 */

	public function testConstruct()
	{
		$pFilterCall = new FilterCall(onOfficeSDK::MODULE_ESTATE);
		$this->assertInstanceOf(SDKWrapper::class, $pFilterCall->getSDKWrapper());
		$this->assertEquals(onOfficeSDK::MODULE_ESTATE, $pFilterCall->getModule());
	}


	/**
	 *
	 */

	public function testGetFilters()
	{
		$expected = [
			87 => 'Einheiten',
			97 => 'Katharina',
			95 => 'Stammobjekte',
			1 => '100.000 € - 300.000 €',
			6 => 'aktive Objekte',
			7 => 'Archivierte Immobilien',
			18 => 'Billigwohnungen',
			4 => 'Favoriten Objekte',
			12 => 'Favoriten Objekte',
			19 => 'Inaktive Immobilien',
			14 => 'Meine Immobilien',
		];
		$this->assertEquals($expected, $this->_pFilterCall->getFilters());
	}


	/**
	 *
	 */

	public function testGetFilternameById()
	{
		$this->assertEquals('Meine Immobilien', $this->_pFilterCall->getFilternameById(14));
		$this->assertEquals('Stammobjekte', $this->_pFilterCall->getFilternameById(95));
		$this->assertEquals('Favoriten Objekte', $this->_pFilterCall->getFilternameById(4));
		$this->assertEquals('Favoriten Objekte', $this->_pFilterCall->getFilternameById(12));
	}


	/**
	 *
	 * @expectedException onOffice\WPlugin\Controller\Exception\UnknownFilterException
	 *
	 */

	public function testGetFilternameByIdUnknown()
	{
		$this->_pFilterCall->getFilternameById(1337);
	}


	/**
	 *
	 * @before
	 *
	 */

	public function setupNewInstance()
	{
		$pSDKWrapperMocker = new SDKWrapperMocker();
		$responseJson = file_get_contents(__DIR__.'/resources/ApiResponseGetFilters.json');
		$response = json_decode($responseJson, true);

		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'filters', '',
			['module' => onOfficeSDK::MODULE_ESTATE], null, $response);
		$this->_pFilterCall = new FilterCall(onOfficeSDK::MODULE_ESTATE, $pSDKWrapperMocker);
	}
}
