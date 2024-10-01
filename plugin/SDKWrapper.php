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
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\DataView\DataListViewFactoryAddress;
use onOffice\WPlugin\Record\RecordManagerReadListViewEstate;
use onOffice\WPlugin\Record\RecordManagerReadListViewAddress;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;

use onOffice\SDK\internal\ApiAction;
use onOffice\SDK\internal\Request;

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

	/** @var Container */
	private $_pContainer = null;


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
		$this->_pContainer = $pDIContainerBuilder->build();
		$this->_encrypter = $this->_pContainer->make(SymmetricEncryption::class);
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


		//1 check is call for a list
		if(isset($parameters['listname']) && array_key_exists('params_list_cache',$parameters))
		{
			//2 check is list-data in Cache
			$cacheResponse = $this->_pSDK->callFromCache($actionId, $resourceId, $identifier, $resourceType, $parameters['params_list_cache']);
			// $cacheResponse = $this->callFromCache($actionId, $resourceId, $identifier, $resourceType, $parameters['params_list_cache']);
			// error_log("Try to get data from Cache: ".$parameters['listname']);
			if($cacheResponse == null)
			{
				error_log("ERROR: Tried to get data from Cache (and renew): ".$parameters['listname']." -->".$parameters['formatoutput']);
				$this->renewCache($parameters['listname']);
			}
		}

		$id = $this->_pSDK->call($actionId, $resourceId, $identifier, $resourceType, $parameters);

		if ($callback !== null) {
			$this->_callbacksAfterSend[$id] = $callback;
		}

		return $id;
	}


	/**
	 *
	 */
	public function sendRequests(bool $saveToCache = true)
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
		$this->_pSDK->sendRequests($token, $secret, $saveToCache);
		$errors = $this->_pSDK->getErrors();

		foreach ($this->_callbacksAfterSend as $handle => $callback) {
			$response = $this->_pSDK->getResponseArray($handle) ?? $errors[$handle] ?? [];
			call_user_func($callback, $response);
		}

		$this->_callbacksAfterSend = [];
	}

	private function createCacheForList($parameters, int $page = 1)
	{
		//1 get first page
		$pApiClientAction = new APIClientActionGeneric($this, onOfficeSDK::ACTION_ID_READ, 'estate');
		$pApiClientAction->setParameters($parameters);
		$pApiClientAction->addRequestToQueue()->sendRequests(false);
		$records = $pApiClientAction->getResult();
		$resultMeta = $pApiClientAction->getResultMeta();

		$numpages = ceil($resultMeta['cntabsolute']/500);
		//2 loop over other pages
		if($page == 1 && $numpages > 1)
		{
			for ($curPage = 2; $curPage < $numpages+1; $curPage++)
			{
				$parameters['offset'] = $curPage-1;
				$tmpRecords = $this->createCacheForList($parameters, $curPage);
				$records = array_merge_recursive($records, $tmpRecords);
			}
		}
		return $records;
	}
	/**
	 * cleans Cache and create new Cache from all lists
	 * (does not create cache for detail pages and similar objects)
	 * @throws ApiClientException
	 */
	 public function renewCache(string $listName = null)
	 {
			//1 get all lists
			//2 clean Cache
			//3 create cache for every list
			//3.1 for every language RecordManagerReadListViewEstate
			$pDataListViewFactory = $this->_pContainer->get(DataListViewFactory::class);
			$pDataListViewFactoryAddress = $this->_pContainer->get(DataListViewFactoryAddress::class);
			$pDefaultFilterBuilderFactory = $this->_pContainer->get(DefaultFilterBuilderFactory::class);

			$languages = [Language::getDefault()]; //TODO
			$lists = $this->get_estate_lists($listName);
			error_log("COUNT:".count($lists));
			foreach ($this->getCache() as $pCache) {
				foreach ($lists as $list) {
					$pListView = $pDataListViewFactory->getListViewByName($list->name);
					$pEstateList = new EstateList($pListView);

					$pListViewFilterBuilder = $pDefaultFilterBuilderFactory->buildDefaultListViewFilter($pListView);
					$pEstateList->setDefaultFilterBuilder($pListViewFilterBuilder);
					foreach ($languages as $lang) {
						foreach ([true,false] as $value) { //call one for raw and one for formattedOutput
							$params = $pEstateList->getEstateParametersForCache($lang, $value);
							$response = $this->createCacheForList($params);
							$pApiAction = new ApiAction(onOfficeSDK::ACTION_ID_READ, 'estate', $params, '', null);
							$pRequest = new Request($pApiAction);
							$usedParameters = $pRequest->getApiAction()->getActionParameters();
							$pCache->write($usedParameters,serialize($response));

							error_log("CREATE CACHE FOR E:".var_export($list->name,true)." LAN:".$lang." SIZE ".count($response));
						}
					}
				}
				foreach ($this->get_address_lists() as $list) {
					// error_log("CREATE CACHE FOR A:".var_export($list->name,true));
					// $pListView = $pDataListViewFactoryAddress->getListViewByName($list->name);
					// $pAddressList = new AddressList($pListView);
					// foreach ($languages as $lang) {
					// 	// $pCache->createCacheForList($requestParams,$lang);
					// }
				}
			}
	 }

	 private function get_request_params()
	 {

	 }

	 private function get_estate_lists(string $listName = null) : array
	 {
		 $pRecordRead = new RecordManagerReadListViewEstate();
		 $pRecordRead->setLimit(100);
		 $pRecordRead->setOffset(0);
		 $pRecordRead->addColumn('listview_id', 'ID');
		 $pRecordRead->addColumn('name');
		 $pRecordRead->addColumn('filterId');
		 $pRecordRead->addColumn('template');
		 $pRecordRead->addColumn('list_type');
		 $pRecordRead->addColumn('name', 'shortcode');
		 $pRecordRead->addColumn('page_shortcode');
		 $pRecordRead->addWhere("`list_type` IN('default', 'reference', 'favorites')");
		 if($listName != null)
		 	$pRecordRead->addWhere("`name` = '".$listName."'");

		 return $pRecordRead->getRecordsSortedAlphabetically();
	 }

	 private function get_address_lists() : array
	 {
		 $pRecordRead = new RecordManagerReadListViewAddress();
		 $pRecordRead->setLimit(100);
		 $pRecordRead->setOffset(0);
		 $pRecordRead->addColumn('listview_address_id', 'ID');
		 $pRecordRead->addColumn('name');
		 $pRecordRead->addColumn('filterId');
		 $pRecordRead->addColumn('template');
		 $pRecordRead->addColumn('name', 'shortcode');
		 $pRecordRead->addColumn('page_shortcode');

		 return $pRecordRead->getRecordsSortedAlphabetically();
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
