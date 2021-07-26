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
use onOffice\WPlugin\API\APIAvailabilityChecker;
use onOffice\WPlugin\Cache\CacheHandler;
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

    /** @var APIAvailabilityChecker */
    private $_pApiChecker = null;
	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pSDKWrapper = $this->getMockBuilder(SDKWrapper::class)
			->getMock();
        $this->_pApiChecker = $this->getMockBuilder(APIAvailabilityChecker::class)->getMock();
        $this->_pCacheHandler = new CacheHandler($this->_pSDKWrapper, $this->_pApiChecker);
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
		$this->_pApiChecker->expects($this->exactly(1))->method('checkAvailability')->will($this->returnValue(['data' => 'data']));
		$pCache = $this->getMockBuilder(onOfficeSDKCache::class)->getMock();
		$pCache->expects($this->exactly(1))->method('cleanup');
		$cacheInstance = [$pCache];
		$this->_pSDKWrapper->expects($this->exactly(1))->method('getCache')->will($this->returnValue($cacheInstance));
		$this->_pCacheHandler->clean();
	}
}
