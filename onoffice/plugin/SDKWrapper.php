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

use onOffice\SDK\onOfficeSDK;


/**
 *
 */

class SDKWrapper {
	/** @var \onOffice\SDK\onOfficeSDK */
	private $_pSDK = null;

	/** @var string */
	private $_secret = '';

	/** @var string */
	private $_token = '';

	/** @var array */
	private $_curlOptions = array();

	/** @var string */
	private $_server = null;


	/**
	 *
	 */

	public function __construct() {
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

	private function readConfig() {
		$localconfig = array(
			'token' => get_option('onoffice-settings-apikey'),
			'secret' => get_option('onoffice-settings-apisecret'),
			'apiversion' => 'latest',
			'cache' => array(
				new \onOffice\WPlugin\Cache\DBCache( array('ttl' => 3600) ),
			),
			'server' => 'https://api.onoffice.de/api/',
			'curl_options' => array
				(
					CURLOPT_SSL_VERIFYPEER => true,
					CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
				),
		);

		$configUser = ConfigWrapper::getInstance()->getConfigByKey('api');

		if ($configUser === null)
		{
			$configUser = array();
		}

		$config = array_merge($localconfig, $configUser);

		return $config;
	}


	/**
	 *
	 * @param string $actionId
	 * @param string $resourceType
	 * @param array $parameters
	 * @return int handle
	 *
	 */

	public function addRequest( $actionId, $resourceType, $parameters = array() ) {
		return $this->_pSDK->callGeneric( $actionId, $resourceType, $parameters );
	}


	/**
	 *
	 * @param string $actionId
	 * @param string $resourceType
	 * @param int $resourceId
	 * @param array $parameters
	 * @param string $identifier
	 * @return handle
	 *
	 */

	public function addFullRequest($actionId, $resourceType, $resourceId, $parameters = array(), $identifier = null) {
		return $this->_pSDK->call($actionId, $resourceId, $identifier, $resourceType, $parameters);
	}


	/**
	 *
	 */

	public function sendRequests() {
		$this->_pSDK->sendRequests( $this->_token, $this->_secret );
	}


	/**
	 *
	 * @param int $handle
	 * @return array
	 *
	 */

	public function getRequestResponse( $handle ) {
		return $this->_pSDK->getResponseArray( $handle );
	}


	/**
	 *
	 * @return \onOffice\SDK\Cache\onOfficeSDKCache[]
	 *
	 */

	public function getCache() {
		$config = $this->readConfig();
		return $config['cache'];
	}


	/**
	 *
	 */

	public function removeCacheInstances() {
		$this->_pSDK->removeCacheInstances();
	}
}
