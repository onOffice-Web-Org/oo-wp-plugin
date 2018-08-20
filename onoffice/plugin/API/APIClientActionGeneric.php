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

namespace onOffice\WPlugin\API;

use onOffice\WPlugin\SDKWrapper;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2018, onOffice(R) GmbH
 *
 */

class APIClientActionGeneric
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var int */
	private $_requestHandle = null;

	/** @var string */
	private $_actionId = null;

	/** @var string */
	private $_resourceType = null;

	/** @var string */
	private $_resourceId = '';

	/** @var array */
	private $_parameters = array();

	/** @var callable */
	private $_resultCallback = null;

	/** @var array */
	private $_result = array();


	/**
	 *
	 * @param SDKWrapper $pSDKWrapper
	 * @param string $actionId
	 * @param string $resourceType
	 *
	 */

	public function __construct(SDKWrapper $pSDKWrapper, $actionId, $resourceType)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->setResultCallback(array($this, 'onAfterExecution'));
		$this->setActionId($actionId);
		$this->setResourceType($resourceType);
		$this->setSettings();
	}


	/**
	 *
	 */

	protected function setSettings() {}

	/**
	 *
	 */

	public function addRequestToQueue()
	{
		$this->_requestHandle = $this->_pSDKWrapper->addRequestByApiAction($this);
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getResultStatus()
	{
		$resultApi = $this->getResult();
		$result = false;

		if (is_array($resultApi)) {
			$errorcode = $resultApi['status']['errorcode'] ?? 500;
			$result = $errorcode === 0 && isset($resultApi['data']['records']);
		}

		return $result;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getResultRecords()
	{
		if ($this->getResultStatus()) {
			$result = $this->getResult();
			return $result['data']['records'];
		}
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getResultMeta()
	{
		if ($this->getResultStatus()) {
			$result = $this->getResult();
			return $result['data']['meta'] ?? null;
		}
	}


	/**
	 *
	 * @param array $result
	 *
	 */

	public function onAfterExecution($result)
	{
		$this->setResult($result);
	}


	/** @return string */
	public function getActionId()
		{ return $this->_actionId; }

	/** @return string */
	public function getResourceType()
		{ return $this->_resourceType; }

	/** @return string */
	public function getResourceId()
		{ return $this->_resourceId; }

	/** @param string $actionId */
	protected function setActionId($actionId)
		{ $this->_actionId = $actionId; }

	/** @param string $resourceType */
	protected function setResourceType($resourceType)
		{ $this->_resourceType = $resourceType; }

	/** @param string $resourceId */
	public function setResourceId($resourceId)
		{ $this->_resourceId = $resourceId; }

	/** @param array $parameters */
	public function setParameters(array $parameters)
		{ $this->_parameters = $parameters; }

	/** @return array */
	public function getParameters()
		{ return $this->_parameters; }

	/** @return SDKWrapper */
	protected function getSDKWrapper()
		{ return $this->_pSDKWrapper; }

	/** @param array $result */
	protected function setResult($result)
		{ $this->_result = $result; }

	/** @return array */
	protected function getResult()
		{ return $this->_result; }

	/** @return callable */
	public function getResultCallback()
		{ return $this->_resultCallback; }

	/** @param callable $resultCallback */
	protected function setResultCallback($resultCallback)
		{ $this->_resultCallback = $resultCallback; }
}
