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

namespace onOffice\WPlugin\Cache;

use onOffice\SDK\Cache\onOfficeSDKCache;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\SDKWrapperAddCurlOption;


/**
 *
 */

class CacheHandler
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var SDKWrapperAddCurlOption */
	private $_pSDKWrapperAddCurlOption = null;


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param SDKWrapperAddCurlOption $pSDKWrapperAddCurlOption
	 */

	public function __construct(SDKWrapper $pSDKWrapper, SDKWrapperAddCurlOption $pSDKWrapperAddCurlOption)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->_pSDKWrapperAddCurlOption = $pSDKWrapperAddCurlOption;
	}


	/**
	 *
	 */

	public function clear()
	{
		foreach ($this->_pSDKWrapper->getCache() as $pCache) {
			/* @var $pCache onOfficeSDKCache */
			$pCache->clearAll();
		}
	}


	/**
	 *
	 */

	public function clean()
	{
		$pApiCall = new APIClientActionGeneric($this->_pSDKWrapperAddCurlOption, onOfficeSDK::ACTION_ID_READ,'estate');
		$pApiCall->addRequestToQueue()->sendRequests();
		$records = $pApiCall->getResultRecords();
		if(!empty($records)){
			foreach ($this->_pSDKWrapperAddCurlOption->getCache() as $pCache) {
				/* @var $pCache onOfficeSDKCache */
				$pCache->cleanup();
			}
		}
	}
}