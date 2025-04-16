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
use onOffice\WPlugin\API\APIAvailabilityChecker;
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\AddressList;

use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;


/**
 *
 */

class CacheHandler
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var APIAvailabilityChecker */
	private $_pApiChecker = null;


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param APIAvailabilityChecker $pApiChecker
	 */

	public function __construct(SDKWrapper $pSDKWrapper, APIAvailabilityChecker $pApiChecker)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->_pApiChecker = $pApiChecker;
		// $pDIContainerBuilder = new ContainerBuilder();
		// $pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		// $this->_pContainer = $pDIContainerBuilder->build();
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
	 * @throws ApiClientException
	 */

	public function clean()
	{
		if($this->_pApiChecker->isAvailable()){
			foreach ($this->_pSDKWrapper->getCache() as $pCache) {
				/* @var $pCache onOfficeSDKCache */
				$pCache->cleanup();
			}
		}
	}

	/**
	 * cleans Cache and create new Cache from all lists
	 * (does not create cache for detail pages and similar objects)
	 * @throws ApiClientException
	 */

	public function renew()
	{
		$this->clear();
		$this->_pSDKWrapper->renewCache();
	}
}
