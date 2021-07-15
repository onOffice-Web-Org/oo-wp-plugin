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

use onOffice\SDK\Cache\onOfficeSDKCache;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Cache\CacheHandler;
use onOffice\WPlugin\Cache\DBCache;
use onOffice\WPlugin\SDKWrapper;
use WP_UnitTestCase;


/**
 *
 */

class TestClassCacheHandler
	extends WP_UnitTestCase
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var CacheHandler */
	private $_pCacheHandler = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSDKWrapper = $this->getMockBuilder(SDKWrapper::class)
			->getMock();
		$this->_pCacheHandler = new CacheHandler($this->_pSDKWrapper);
	}


	/**
	 *
	 */

	public function testClear()
	{
		$pCache = $this->getMockBuilder(onOfficeSDKCache::class)->getMock();
		$pCache->expects($this->once())->method('clearAll');
		$cacheInstance = [$pCache];
		$this->_pSDKWrapper->expects($this->once())->method('getCache')->will($this->returnValue($cacheInstance));
		$this->_pCacheHandler->clear();
	}


	/**
	 *
	 */

	public function testClean()
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
			]
		);
		$sdkWrapperMocker->setSDK($_pSDK);

		$dataReadEstateFormatted = json_decode(
			file_get_contents(__DIR__ . '/resources/ApiResponseReadEstatesPublishedENG.json'),
			true
		);
		$responseReadEstate = $dataReadEstateFormatted['response'];
		$parametersReadEstate = $dataReadEstateFormatted['parameters'];

		$sdkWrapperMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ,
			'estate',
			'',
			$parametersReadEstate,
			null,
			$responseReadEstate
		);
		$sdkWrapperMocker->addResponseByParameters(
			onOfficeSDK::ACTION_ID_READ,
			'estate',
			'',
			[],
			null,
			$responseReadEstate
		);

		$sdkWrapperMocker->addFullRequest(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parametersReadEstate, null);
		$pCacheHandler = new CacheHandler($sdkWrapperMocker);
		$pCacheHandler->clean();
		$pCaches = $sdkWrapperMocker->getCache();
		$this->assertNotEmpty($pCaches);
		$this->assertNotNull($sdkWrapperMocker->getSDK());
		$this->assertEmpty($sdkWrapperMocker->getSDK()->getErrors());
	}
}
