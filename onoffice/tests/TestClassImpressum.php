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

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Impressum;
use onOffice\WPlugin\ImpressumConfigurationTest;
use onOffice\tests\SDKWrapperMocker;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class TestClassImpressum
	extends WP_UnitTestCase
{
	/** @var Impressum */
	private $_pImpressum = null;


	/**
	 *
	 */

	public function setUp()
	{
		$readImpressum = file_get_contents
			(__DIR__.'/resources/ApiResponseReadImpressum.json');
		$response = json_decode($readImpressum, true);

		$pSDKWrapperMocker = new SDKWrapperMocker();

		$pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'impressum', '', ['language' => 'ENG'], null, $response);

		$pImpressumConfigurationTest = new ImpressumConfigurationTest();
		$pImpressumConfigurationTest->setSDKWrapper($pSDKWrapperMocker);

		$this->_pImpressum = new Impressum($pImpressumConfigurationTest);
	}


	/**
	 *
	 */

	public function testGetData()
	{
		$result = $this->_pImpressum->load()->getData();

		$expectedValues = [
			'title' => 'Frau',
			'firstname' => 'Frauke',
			'lastname' => 'Musterfrau',
			'firma' => 'asd-Gruppe A',
			'postcode' => '52078',
			'city' => 'Aachen',
			'street' => 'Charlottenburger Allee',
			'housenumber' => '5',
			'state' => 'Nordrhein-Westfalen',
			'country' => 'Deutschland',
			'phone' => '0241 123 456',
			'mobil' => '0179 123 456',
			'fax' => '0241 789 456',
			'email' => 'asd@onoffice.de',
			'homepage' => 'www.asd.de',
			'vertretungsberechtigter' => 'Vertretungsberechtigter',
			'berufsaufsichtsbehoerde' => 'Berufsaufsichtsbehoerde',
			'handelsregister' => 'HR12',
			'handelsregisterNr' => '123',
			'ustId' => 'DE123456789',
			'bank' => 'Musterbank',
			'iban' => 'DE123456789',
			'bic' => 'DETESTACD123',
			'chamber' => '12345',
		];

		foreach ($expectedValues as $key => $value) {
			$this->assertEquals($value, $result[$key]);
		}
	}


	/**
	 *
	 */

	public function testGetDataByKey()
	{
		$this->_pImpressum->load();
		$expectedValues = [
			'title' => 'Frau',
			'firstname' => 'Frauke',
			'lastname' => 'Musterfrau',
			'firma' => 'asd-Gruppe A',
			'postcode' => '52078',
			'city' => 'Aachen',
			'street' => 'Charlottenburger Allee',
			'housenumber' => '5',
			'state' => 'Nordrhein-Westfalen',
			'country' => 'Deutschland',
			'phone' => '0241 123 456',
			'mobil' => '0179 123 456',
			'fax' => '0241 789 456',
			'email' => 'asd@onoffice.de',
			'homepage' => 'www.asd.de',
			'vertretungsberechtigter' => 'Vertretungsberechtigter',
			'berufsaufsichtsbehoerde' => 'Berufsaufsichtsbehoerde',
			'handelsregister' => 'HR12',
			'handelsregisterNr' => '123',
			'ustId' => 'DE123456789',
			'bank' => 'Musterbank',
			'iban' => 'DE123456789',
			'bic' => 'DETESTACD123',
			'chamber' => '12345',
		];

		foreach ($expectedValues as $key => $value) {
			$expectedValue = $value;
			$result = $this->_pImpressum->getDataByKey($key);

			$this->assertEquals($expectedValue, $result);
		}
	}


	/**
	 *
	 */

	public function testUnknownValue()
	{
		$this->_pImpressum->load();
		$result = $this->_pImpressum->getDataByKey('unknownValue');
		$this->assertEquals('', $result);
	}
}