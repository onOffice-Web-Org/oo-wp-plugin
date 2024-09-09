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

use DI\ContainerBuilder;
use onOffice\SDK\Cache\onOfficeSDKCache;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Cache\DBCache;
use onOffice\WPlugin\Utility\SymmetricEncryption;
use onOffice\WPlugin\WP\WPOptionWrapperBase;
use onOffice\WPlugin\WP\WPOptionWrapperDefault;


/**
 *
 */

class SDKWrapper
{
	/** @var onOfficeSDK */
	private $_pSDK = null;

	/** @var array */
	private $_callbacksAfterSend = [];

	/** @var WPOptionWrapperDefault */
	private $_pWPOptionWrapper = null;

	/** @var array */
	private $_caches = [];

	/**
	 * @var  SymmetricEncryption
	 */
	private $_encrypter;


	/**
	 *
	 * @param onOfficeSDK $pSDK
	 * @param WPOptionWrapperBase $pWPOptionWrapper
	 *
	 */

	public function __construct(
		onOfficeSDK $pSDK = null,
		WPOptionWrapperBase $pWPOptionWrapper = null)
	{
		$this->_pSDK = $pSDK ?? new onOfficeSDK();
		$this->_pWPOptionWrapper = $pWPOptionWrapper ?? new WPOptionWrapperDefault();

		$this->_caches = [
			new DBCache(['ttl' => 3600]),
		];

		$pDIContainerBuilder = new ContainerBuilder();
		$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$pContainer = $pDIContainerBuilder->build();
		$this->_encrypter = $pContainer->make(SymmetricEncryption::class);
		$this->_pSDK->setCaches($this->_caches);
		$this->_pSDK->setApiServer('https://api.onoffice.de/api/');
		$this->_pSDK->setApiVersion('latest');
		$this->_pSDK->setApiCurlOptions(
			[
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
			]
		);
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
		$pOptionsWrapper = $this->_pWPOptionWrapper;
		$token = $pOptionsWrapper->getOption('onoffice-settings-apikey');
		$secret = $pOptionsWrapper->getOption('onoffice-settings-apisecret');
		if (defined('ONOFFICE_CREDENTIALS_ENC_KEY')) {
			try {
				$secretDecrypt = $this->_encrypter->decrypt($secret, ONOFFICE_CREDENTIALS_ENC_KEY);
				$tokenDecrypt = $this->_encrypter->decrypt($token, ONOFFICE_CREDENTIALS_ENC_KEY);
			}catch (\RuntimeException $exception){
				$this->_pSDK->removeCacheInstances();
				$secretDecrypt = $secret;
				$tokenDecrypt = $token;
			}
			$secret = $secretDecrypt;
			$token = $tokenDecrypt;
		}
		$this->_pSDK->sendRequests($token, $secret);
		$errors = $this->_pSDK->getErrors();

		foreach ($this->_callbacksAfterSend as $handle => $callback) {
			$response = $this->_pSDK->getResponseArray($handle) ?? $errors[$handle] ?? [];
			call_user_func($callback, $response);
		}

		$this->_callbacksAfterSend = [];
	}


	/**
	 *
	 * @return onOfficeSDKCache[]
	 *
	 */

	public function getCache(): array
	{
		return $this->_caches;
	}


	/**
	 *
	 * @return onOfficeSDK
	 *
	 */

	public function getSDK(): onOfficeSDK
	{
		return $this->_pSDK;
	}


	/**
	 *
	 * @return WPOptionWrapperBase
	 *
	 */

	public function getWPOptionWrapper(): WPOptionWrapperBase
	{
		return $this->_pWPOptionWrapper;
	}


	/**
	 * @param array $curlOptions
	 * @return SDKWrapper
	 */

	public function withCurlOptions(array $curlOptions): self
	{
		$pClone = clone $this;
		$pClone->_pSDK->setApiCurlOptions($curlOptions);
		return $pClone;
	}


	/**
	 *
	 */

	public function __clone()
	{
		$this->_pSDK = clone $this->_pSDK;
		$this->_callbacksAfterSend = [];
	}
}
