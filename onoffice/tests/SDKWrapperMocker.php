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

namespace onOffice\tests;

use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Cache\DBCache;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class SDKWrapperMocker
	extends SDKWrapper
{
	/** @var array */
	private $_requests = array();

	/** @var array */
	private $_responses = array();

	/** @var array */
	private $_callbacksAfterSend = array();

	/** @var bool */
	private $_requestSent = false;


	/**
	 *
	 */

	public function __construct()
	{
		// prevent parent constructor from being called
	}


	/**
	 *
	 * @param string $actionId
	 * @param string $resourceType
	 * @param array $parameters
	 * @return int
	 *
	 */

	public function addRequest($actionId, $resourceType, $parameters = array())
	{
		return $this->addFullRequest($actionId, $resourceType, '', $parameters);
	}


	/**
	 *
	 * @param string $actionId
	 * @param string $resourceType
	 * @param string $resourceId
	 * @param array $parameters
	 * @param string $identifier
	 * @return int
	 *
	 */

	public function addFullRequest($actionId, $resourceType, $resourceId, $parameters = array(), $identifier = null)
	{
		$requestStr = $this->stringifyRequest
			($actionId, $resourceType, $resourceId, $parameters, $identifier);
		$this->_requests []= $requestStr;
		$this->_requestSent = false;

		return array_search($requestStr, $this->_requests);
	}


	/**
	 *
	 * @param APIClientActionGeneric $pApiAction
	 * @return int
	 *
	 */

	public function addRequestByApiAction(APIClientActionGeneric $pApiAction)
	{
		$actionId = $pApiAction->getActionId();
		$resourceId = $pApiAction->getResourceId();
		$identifier = null;
		$resourceType = $pApiAction->getResourceType();
		$parameters = $pApiAction->getParameters();
		$callback = $pApiAction->getResultCallback();

		$id = $this->addFullRequest($actionId, $resourceType, $resourceId, $parameters, $identifier);

		if ($callback !== null) {
			$this->_callbacksAfterSend[$id] = $callback;
		}

		return $id;
	}


	/**
	 *
	 * @param string $actionId
	 * @param string $resourceType
	 * @param string $resourceId
	 * @param array $parameters
	 * @param string $identifier
	 * @return string
	 *
	 */

	private function stringifyRequest($actionId, $resourceType, $resourceId, $parameters, $identifier)
	{
		$params = array($actionId, $resourceType, $resourceId, $parameters, $identifier);
		return json_encode($params);
	}


	/**
	 *
	 * @param APIClientActionGeneric $pApiAction
	 * @param array $response
	 *
	 */

	public function addResponseByApiAction(APIClientActionGeneric $pApiAction, array $response)
	{
		$actionId = $pApiAction->getActionId();
		$resourceId = $pApiAction->getResourceId();
		$identifier = null;
		$resourceType = $pApiAction->getResourceType();
		$parameters = $pApiAction->getParameters();

		$requestStr = $this->stringifyRequest
			($actionId, $resourceType, $resourceId, $parameters, $identifier);
		$this->_responses[$requestStr] = $response;
	}


	/**
	 *
	 * @param string $actionId
	 * @param string $resourceType
	 * @param string $resourceId
	 * @param array $parameters
	 * @param string $identifier
	 * @param array $response
	 *
	 */

	public function addResponseByParameters(
		$actionId, $resourceType, $resourceId, array $parameters, $identifier, array $response)
	{
		$requestStr = $this->stringifyRequest
			($actionId, $resourceType, $resourceId, $parameters, $identifier);
		$this->_responses[$requestStr] = $response;
	}


	/**
	 *
	 * For Debug purposes only
	 *
	 * @return array
	 *
	 */

	public function getRequestArray()
	{
		return $this->_requests;
	}


	/**
	 *
	 * For Debug purposes only
	 *
	 * @return array
	 *
	 */

	public function getResponseArray()
	{
		return $this->_responses;
	}


	/**
	 *
	 */

	public function sendRequests()
	{
		$this->_requestSent = true;

		foreach ($this->_callbacksAfterSend as $handle => $callback) {
			$response = $this->getRequestResponse($handle);
			call_user_func($callback, $response);
		}
	}


	/**
	 *
	 * @param int $handle
	 * @return array
	 *
	 */

	public function getRequestResponse($handle)
	{
		$response = null;

		if ($this->_requestSent) {
			$requestParameters = $this->_requests[$handle];
			$response = $this->_responses[$requestParameters];
		}

		return $response;
	}


	/**
	 *
	 * @return DBCache
	 *
	 */

	public function getCache()
	{
		return new DBCache(array('ttl' => 3600));
	}
}
