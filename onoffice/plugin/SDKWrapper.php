<?php

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
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function readConfig() {
		$config = array(
			'token' => '',
			'secret' => '',
			'apiversion' => '1.5',
			'cache' => array(),
		);

		include plugin_dir_path(__FILE__).'../api-config.php';

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

	public function addRequest( $actionId, $resourceType, $parameters ) {
		return $this->_pSDK->callGeneric( $actionId, $resourceType, $parameters );
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
