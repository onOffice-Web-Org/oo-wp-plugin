<?php

/**
 *
 *    Copyright (C) 2016 onOffice Software AG
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

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\SDK\Cache\onOfficeSDKCache;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Cache\DBCache;


/**
 *
 */

class SDKWrapper
{
	/** @var onOfficeSDK */
	private $_pSDK = null;

	/** @var string */
	private $_secret = '';

	/** @var string */
	private $_token = '';

	/** @var array */
	private $_curlOptions = [];

	/** @var string */
	private $_server = null;

	/** @var array */
	private $_callbacksAfterSend = [];


	/**
	 *
	 */

	public function __construct()
	{
		$config = $this->readConfig();

		$this->_pSDK = new onOfficeSDK();
		$this->_pSDK->setApiVersion( $config['apiversion'] );
		$this->_pSDK->setCaches( $config['cache'] );
		$this->_token = $config['token'];
		$this->_secret = $config['secret'];
		$this->_curlOptions = $config['curl_options'];
		$this->_server = $config['server'];
		$this->_pSDK->setApiServer($this->_server);
		$this->_pSDK->setApiCurlOptions($this->_curlOptions);
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function readConfig(): array
	{
		$localconfig = [
			'token' => get_option('onoffice-settings-apikey'),
			'secret' => get_option('onoffice-settings-apisecret'),
			'apiversion' => 'latest',
			'cache' => [
				new DBCache( array('ttl' => 3600) ),
			],
			'server' => 'https://api.onoffice.de/api/',
			'curl_options' => [
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
			],
		];

		$configUser = ConfigWrapper::getInstance()->getConfigByKey('api');

		if ($configUser === null) {
			$configUser = array();
		}

		$config = array_merge($localconfig, $configUser);

		return $config;
	}


	/**
	 *
	 * @deprecated since 2018
	 * Use APIClientActionGeneric instead!
	 *
	 * @param string $actionId
	 * @param string $resourceType
	 * @param array $parameters
	 * @return int handle
	 *
	 */

	public function addRequest(string $actionId, string $resourceType, array $parameters = [])
	{
		return $this->_pSDK->callGeneric( $actionId, $resourceType, $parameters );
	}


	/**
	 *
	 * @param APIClientActionGeneric $pApiAction
	 * @return int
	 *
	 */

	public function addRequestByApiAction(APIClientActionGeneric $pApiAction): int
	{
		$actionId = $pApiAction->getActionId();
		$resourceId = $pApiAction->getResourceId();
		$identifier = null;
		$resourceType = $pApiAction->getResourceType();
		$parameters = $pApiAction->getParameters();
		$callback = $pApiAction->getResultCallback();

		$id = $this->_pSDK->call($actionId, $resourceId, $identifier, $resourceType, $parameters);

		if ($callback !== null) {
			$this->_callbacksAfterSend[$id] = $callback;
		}

		return $id;
	}


	/**
	 *
	 */

	public function sendRequests()
	{
		$this->_pSDK->sendRequests( $this->_token, $this->_secret );
		$errors = $this->_pSDK->getErrors();

		foreach ($this->_callbacksAfterSend as $handle => $callback) {
			$response = $this->_pSDK->getResponseArray($handle) ?? $errors[$handle] ?? [];
			call_user_func($callback, $response);
		}

		$this->_callbacksAfterSend = [];
	}


	/**
	 *
	 * @deprecated since 2018
	 * @param int $handle
	 * @return array
	 *
	 */

	public function getRequestResponse(int $handle): array
	{
		$response = $this->_pSDK->getResponseArray($handle);
		return $response ?? [];
	}


	/**
	 *
	 * @return onOfficeSDKCache[]
	 *
	 */

	public function getCache(): array
	{
		$config = $this->readConfig();
		return $config['cache'];
	}
}
