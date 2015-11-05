<?php

/**
 *
 * @author Jakob Jungmann <j.jungmann@onoffice.de>
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */


namespace onOffice\SDK\internal;


/**
 *
 */

class Request
{
	/** @var array */
	private $_actionParameters = array();


	/**
	 *
	 * @param \onOffice\SDK\internal\ApiAction $pApiAction
	 *
	 */

	public function __construct(ApiAction $pApiAction)
	{
		$this->_actionParameters = $pApiAction->getActionParameters();
	}


	/**
	 *
	 * @param string $token
	 * @param string $secret
	 * @return array
	 *
	 */

	public function createRequest($token, $secret)
	{
		$this->_actionParameters['timestamp'] = time();
		$timestamp = $this->_actionParameters['timestamp'];

		$id = $this->_actionParameters['resourceid'];
		$identifier = $this->_actionParameters['identifier'];
		$parameters = $this->_actionParameters['parameters'];
		$actionId = $this->_actionParameters['actionid'];
		$type = $this->_actionParameters['resourcetype'];
		$hmac = $this->createHmac($id, $token, $secret, $timestamp, $identifier, $type, $parameters, $actionId);
		$this->_actionParameters['hmac'] = $hmac;

		return $this->_actionParameters;
	}


	/**
	 *
	 * @param string $id
	 * @param string $token
	 * @param string $secret
	 * @param string $timestamp
	 * @param string $identifier
	 * @param string $type
	 * @param string $parameters
	 * @param string $actionId
	 * @return string
	 *
	 */

	private function createHmac($id, $token, $secret, $timestamp, $identifier, $type, $parameters, $actionId)
	{
		// in alphabetical order
		$fields['accesstoken'] = $token;
		$fields['actionid'] = $actionId;
		$fields['identifier'] = $identifier;
		$fields['resourceid'] = $id;
		$fields['secret'] = $secret;
		$fields['timestamp'] = $timestamp;
		$fields['type'] = $type;

		ksort($parameters);

		$parametersBundled = json_encode($parameters);
		$fieldsBundled = implode(',', $fields);
		$allParams = $parametersBundled.','.$fieldsBundled;
		$hmac = md5($secret.md5($allParams));

		return $hmac;
	}
}
