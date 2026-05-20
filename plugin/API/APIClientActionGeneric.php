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

declare(strict_types=1);

namespace onOffice\WPlugin\API;

use onOffice\WPlugin\SDKWrapper;

/**
 *
 */
class APIClientActionGeneric
{
	/** @var SDKWrapper */
	private $_pSDKWrapper = null;

	/** @var int */
	private $_requestHandle = null;

	/** @var string */
	private $_actionId = '';

	/** @var string */
	private $_resourceType = '';

	/** @var string */
	private $_resourceId = '';

	/** @var array */
	private $_parameters = [];

	/** @var callable */
	private $_resultCallback = null;

	/** @var array */
	private $_result = [];


	/**
	 * @param SDKWrapper $pSDKWrapper
	 * @param string $actionId
	 * @param string $resourceType
	 */
	public function __construct(SDKWrapper $pSDKWrapper, string $actionId, string $resourceType)
	{
		$this->_pSDKWrapper = $pSDKWrapper;
		$this->setActionId($actionId);
		$this->setResourceType($resourceType);
		$this->setResultCallback([$this, 'onAfterExecution']);
	}

	/**
	 *
	 */
	public function __clone()
	{
		$this->setResultCallback([$this, 'onAfterExecution']);
	}

	/**
	 * @return ApiClientException
	 */
	private function generateException(): ApiClientException
	{
		$pExceptionFactory = new APIClientExceptionFactory();
		return $pExceptionFactory->createExceptionByAPIClientAction($this);
	}

	/**
	 * @return $this
	 */
	public function addRequestToQueue(): APIClientActionGeneric
	{
		$this->_requestHandle = $this->_pSDKWrapper->addRequestByApiAction($this);
		return $this;
	}

	/**
	 * @param bool $saveToCache
	 * call addRequestToQueue() first
	 */
	public function sendRequests(bool $saveToCache = true)
	{
		$this->_pSDKWrapper->sendRequests($saveToCache);
	}

	/**
	 * @return bool
	 */
	public function getResultStatus(): bool
	{
		$resultApi = $this->getResult();
		$result = $this->getErrorCode() === 0 && isset($resultApi['data']['records']);

		return $result;
	}

	/**
	 * @return int
	 */
	public function getErrorCode(): int
	{
		$resultApi = $this->getResult();
		$errorCode = $resultApi['status']['errorcode'] ?? 500;
		
		// Handle case where errorcode is an array (multiple API requests)
		if (is_array($errorCode)) {
			// If array is empty, return 500
			if (empty($errorCode)) {
				return 500;
			}
			
			// Return the first non-zero error code, or 0 if all are successful
			foreach ($errorCode as $code) {
				$intCode = (int)$code;
				if ($intCode !== 0) {
					return $intCode;
				}
			}
			
			// All codes are 0 (success)
			return 0;
		}
		
		return (int)$errorCode;
	}

	/**
	 * @return array
	 * @throws ApiClientException
	 */
	public function getResultRecords(): array
	{
		if ($this->getResultStatus()) {
			$result = $this->getResult();
			return $result['data']['records'];
		}

		throw $this->generateException();
	}

	/**
	 * @return array
	 * @throws ApiClientException
	 */
	public function getResultMeta(): array
	{
		if ($this->getResultStatus()) {
			$result = $this->getResult();
			return $result['data']['meta'] ?? [];
		}

		throw $this->generateException();
	}

	/**
	 * @param array $result
	 */
	public function onAfterExecution(array $result)
	{
		$this->setResult($result);
	}

	/**
	 * @param string $actionId
	 * @param string $resourceType
	 * @return self
	 */
	public function withActionIdAndResourceType(string $actionId, string $resourceType): self
	{
		$pAPIClientAction = clone $this;
		$pAPIClientAction->setActionId($actionId);
		$pAPIClientAction->setResourceType($resourceType);
		return $pAPIClientAction;
	}

	/** @return string */
	public function getActionId(): string
		{ return $this->_actionId; }

	/** @return string */
	public function getResourceType(): string
		{ return $this->_resourceType; }

	/** @return string */
	public function getResourceId(): string
		{ return $this->_resourceId; }

	/** @param string $actionId */
	protected function setActionId(string $actionId)
		{ $this->_actionId = $actionId; }

	/** @param string $resourceType */
	protected function setResourceType(string $resourceType)
		{ $this->_resourceType = $resourceType; }

	/** @param string $resourceId */
	public function setResourceId(string $resourceId)
		{ $this->_resourceId = $resourceId; }

	/** @param array $parameters */
	public function setParameters(array $parameters)
		{ $this->_parameters = $parameters; }

	/** @return array */
	public function getParameters(): array
		{ return $this->_parameters; }

	/** @param array $result */
	protected function setResult(array $result)
		{ $this->_result = $result; }

	/** @return array */
	public function getResult(): array
		{ return $this->_result; }

    /** @return string */
    public function getErrorMessage(): string
    { return $this->_result['status']['message'] ?? ''; }

	/** @return callable */
	public function getResultCallback(): callable
		{ return $this->_resultCallback; }

	/** @param callable $resultCallback */
	protected function setResultCallback(callable $resultCallback)
		{ $this->_resultCallback = $resultCallback; }

	/** @return SDKWrapper */
	public function getSDKWrapper(): SDKWrapper
		{ return $this->_pSDKWrapper; }
}
