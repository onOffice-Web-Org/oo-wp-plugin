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
use onOffice\WPlugin\API\APIAvailabilityChecker;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Cache\DBCache;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;

/**
 *
 */

class TestClassAPIAvailabilityChecker
	extends WP_UnitTestCase
{
	/**
	 *
	 */

	public function testCheckAvailability()
	{
		$_pSDKWrapper = $this->getMockBuilder(SDKWrapper::class)
			->getMock();
		$_pSDKWrapper->expects($this->once())->method('withCurlOptions')->will($this->returnValue(
			$this->buildSDKWrapper()
		));
		$apiChecker = new APIAvailabilityChecker($_pSDKWrapper);
		$_pAPIClientAction = $this->getMockBuilder(APIClientActionGeneric::class)
			->setConstructorArgs([$_pSDKWrapper, onOfficeSDK::ACTION_ID_READ, 'basicsettings'])
			->getMock();

		$_pAPIClientAction->method('addRequestToQueue')->willReturn($_pAPIClientAction);
		$_pAPIClientAction->method('sendRequests');
		$_pAPIClientAction->method('getResultRecords')->willReturn(
			[
				0 => [
					'elements' => [
						"basicData" => [
							"characteristicsCi" => [
								"logo" => "",
								"color" => "80acd3",
								"color2" => "eeeeee",
								"textcolorMail" => "666666",
								"claim" => ""
							]
						]
					]
				]
			]
		);
		$apiChecker->isAvailable();
	}


	/**
	 *
	 */

	public function buildSDKWrapper()
	{
		$sdkWrapperMocker = new SDKWrapperMocker();
		$_pSDK = new onOfficeSDK();
		$_pSDK->setCaches(
			[
				new DBCache(['ttl' => 3600]),
			]
		);
		$_pSDK->setApiServer('https://api.onoffice.de/api/');
		$_pSDK->setApiVersion('latest');
		$_pSDK->setApiCurlOptions(
			[
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
				CURLOPT_CONNECTTIMEOUT => 1,
			]
		);

		$dataReadEstateFormatted = json_decode(
			file_get_contents(__DIR__ . '/resources/ApiResponseReadBasicSetting.json'),
			true
		);
		$responseReadEstate = $dataReadEstateFormatted['response'];
		$parametersReadEstate = $dataReadEstateFormatted['parameters'];

		$sdkWrapperMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ,
			'basicsettings',
			'',
			$parametersReadEstate,
			null,
			$responseReadEstate
		);
		$sdkWrapperMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ,
			'basicsettings',
			'',
			[],
			null,
			$responseReadEstate
		);

		$sdkWrapperMocker->addFullRequest(onOfficeSDK::ACTION_ID_READ, 'basicsettings', '', $parametersReadEstate, null);
		return $sdkWrapperMocker;
	}
}
