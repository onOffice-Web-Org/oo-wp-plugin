<?php

/**
 *
 * @author Jakob Jungmann <j.jungmann@onoffice.de>
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */


namespace onOffice\SDK\internal;

use onOffice\SDK\internal\ApiAction;
use onOffice\SDK\internal\Request;
use onOffice\SDK\internal\HttpFetch;


/**
 *
 */

class ApiCall
{
	/** @var array */
	private $_requestQueue = array();

	/** @var array */
	private $_responses = array();

	/** @var array */
	private $_errors = array();

	/** @var string */
	private $_apiVersion = '1.4';


	/**
	 *
	 * @param string $actionId
	 * @param string $resourceId
	 * @param string $identifier
	 * @param string $resourceType
	 * @param array $parameters
	 *
	 * @return int
	 *
	 */

	public function callByRawData($actionId, $resourceId, $identifier, $resourceType, $parameters)
	{
		$pApiAction = new ApiAction($actionId, $resourceType, $parameters, $resourceId, $identifier);

		$requestId = count($this->_requestQueue);
		$this->_requestQueue []= $pApiAction;

		return $requestId;
	}


	/**
	 *
	 * @param type $token
	 * @param type $secret
	 * @throws \Exception
	 *
	 */

	public function sendRequests($token, $secret)
	{
		$actionParameters = array();

		foreach ($this->_requestQueue as $pApiAction)
		{
			$pRequest = new Request($pApiAction);
			$parametersThisAction = $pRequest->createRequest($token, $secret);

			$actionParameters []= $parametersThisAction;
		}

		if (count($actionParameters) === 0)
		{
			throw new \Exception('no requests!');
		}

		$request = array
			(
				'token' => $token,
				'request' => array('actions' => $actionParameters),
			);

		$pHttpFetch = new HttpFetch($this->getApiUrl(), json_encode($request));
		$result = json_decode($pHttpFetch->send(), true);

		if ($result['status']['code'] != 200)
		{
			$this->_errors []= $result;
		}

		$this->_requestQueue = array();
		$this->_responses = (array) ($result['response']['results']);
	}


	/**
	 *
	 * @param int $number
	 * @return array
	 *
	 */

	public function getResponse($number)
	{
		if (array_key_exists($number, $this->_responses))
		{
			$response = $this->_responses[$number];
			unset($this->_responses[$number]);

			return $response;
		}
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getApiUrl()
	{
		return 'https://api.onoffice.de/api/'.urlencode($this->_apiVersion).'/api.php';
	}


	/**
	 *
	 * @param string $apiVersion
	 *
	 */

	public function setApiVersion($apiVersion)
	{
		$this->_apiVersion = $apiVersion;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getAndRemoveErrors()
	{
		$errors = $this->_errors;
		$this->_errors = array();

		return $errors;
	}
}
