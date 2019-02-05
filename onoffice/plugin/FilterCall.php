<?php

/**
 *
 *    Copyright (C) 2016-2019 onOffice GmbH
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

namespace onOffice\WPlugin;

use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Controller\Exception\UnknownFilterException;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2019, onOffice(R) GmbH
 *
 */

class FilterCall
{
	/** @var array */
	private $_filters = null;

	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var string */
	private $_module = null;


	/**
	 *
	 * @param string $module
	 * @param SDKWrapper $pSDKWrapper
	 *
	 */

	public function __construct(string $module, SDKWrapper $pSDKWrapper = null)
	{
		$this->_pSDKWrapper = $pSDKWrapper ?? new SDKWrapper();
		$this->_module = $module;
	}


	/**
	 *
	 */

	private function load()
	{
		$pApiClientAction = new APIClientActionGeneric
			($this->_pSDKWrapper, onOfficeSDK::ACTION_ID_GET, 'filters');
		$pApiClientAction->setParameters(['module' => $this->_module]);
		$pApiClientAction->addRequestToQueue()->sendRequests();
		$this->extractResponse($pApiClientAction);
	}


	/**
	 *
	 * @param APIClientActionGeneric $pApiClientAction
	 *
	 */

	private function extractResponse(APIClientActionGeneric $pApiClientAction)
	{
		$filters = $pApiClientAction->getResultRecords();
		$this->_filters = [];

		foreach ($filters as $filter) {
			$elements = $filter['elements'];
			$this->_filters[$filter['id']] = $elements['name'];
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getFilters(): array
	{
		if ($this->_filters === null) {
			$this->load();
		}

		return $this->_filters;
	}


	/**
	 *
	 * @param int $id
	 * @return string
	 * @throws UnknownFilterException
	 *
	 */

	public function getFilternameById(int $id): string
	{
		$filters = $this->getFilters();
		if (isset($filters[$id])) {
			return $filters[$id];
		}

		$pException = new UnknownFilterException();
		$pException->setFilterId($id);
		throw $pException;
	}


	/** @return string */
	public function getModule(): string
		{ return $this->_module; }

	/** @return SDKWrapper */
	public function getSDKWrapper(): SDKWrapper
		{ return $this->_pSDKWrapper; }
}
