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

abstract class APIClientAction
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
	 *
	 */

	public function __construct(SDKWrapper $pSDKWrapper)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->setSettings();
	}


	abstract protected function setSettings();

	/**
	 *
	 */

	public function addRequestToQueue()
	{
		$this->_requestHandle = $this->_pSDKWrapper->addRequestByApiAction($this);
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getResultRecords()
	{
		return null;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getResultStatus()
	{
		return false;
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
	protected function setResourceId($resourceId)
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
