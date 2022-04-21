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

use onOffice\SDK\onOfficeSDK;
use onOffice\tests\SDKWrapperMocker;
use onOffice\WPlugin\Impressum;
use WP_UnitTestCase;
use function json_decode;

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

	public function set_up()
	{
		$readImpressum = file_get_contents
			(__DIR__.'/resources/ApiResponseReadImpressum.json');
		$response = json_decode($readImpressum, true);

		$pSDKWrapperMocker = new SDKWrapperMocker();

		$pSDKWrapperMocker->addResponseByParameters
			(onOfficeSDK::ACTION_ID_READ, 'impressum', '', ['language' => 'ENG'], null, $response);

		$this->_pImpressum = new Impressum($pSDKWrapperMocker);
	}


	/**
	 *
	 */

	public function testGetData()
	{
		$result = $this->_pImpressum->load();

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

		$this->assertEquals($expectedValues, $result);
	}
}