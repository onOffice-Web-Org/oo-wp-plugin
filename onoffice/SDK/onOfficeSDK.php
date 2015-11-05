<?php

/**
 *
 * @author Jakob Jungmann <j.jungmann@onoffice.de>
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */


namespace onOffice\SDK;

use onOffice\SDK\internal\ApiCall;


/**
 *
 */

class onOfficeSDK
{
	/** */
	const ACTION_ID_READ = 'urn:onoffice-de-ns:smart:2.5:smartml:action:read';

	/** */
	const ACTION_ID_CREATE = 'urn:onoffice-de-ns:smart:2.5:smartml:action:create';

	/** */
	const ACTION_ID_MODIFY = 'urn:onoffice-de-ns:smart:2.5:smartml:action:modify';

	/** */
	const ACTION_ID_GET = 'urn:onoffice-de-ns:smart:2.5:smartml:action:get';

	/** */
	const ACTION_ID_DO = 'urn:onoffice-de-ns:smart:2.5:smartml:action:do';


	/** @var \onOffice\SDK\internal\ApiCall */
	private $_pApiCall = null;


	/**
	 *
	 */

	public function __construct()
	{
		$this->_pApiCall = new ApiCall();
	}


	/**
	 *
	 * @param string $apiVersion
	 *
	 */

	public function setApiVersion($apiVersion)
	{
		$this->_pApiCall->setApiVersion($apiVersion);
	}


	/**
	 *
	 * @param string $actionId from constant above
	 * @param string $resourceType
	 * @param array $parameters
	 *
	 * @return int
	 *
	 */

	public function callGeneric($actionId, $resourceType, $parameters)
	{
		return $this->_pApiCall->callByRawData($actionId, '', '', $resourceType, $parameters);
	}


	/**
	 *
	 * @param string $token
	 * @param string $secret
	 *
	 */

	public function sendRequests($token, $secret)
	{
		$this->_pApiCall->sendRequests($token, $secret);
	}


	/**
	 *
	 * @param int $number
	 * @return array
	 *
	 */

	public function getResponseArray($number)
	{
		return $this->_pApiCall->getResponse($number);
	}
}
