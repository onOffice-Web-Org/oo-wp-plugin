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
use onOffice\WPlugin\Filter\DefaultFilterBuilderListViewAddressFactory;

use onOffice\SDK\internal\ApiAction;
use onOffice\SDK\internal\Request;
use PhpParser\Node\Stmt\TryCatch;
use onOffice\WPlugin\Field\UnknownFieldException;

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

	/** @var array<string, array> */
	private $_cachedFieldTypesByLanguage = [];

	/** @var array<string, bool> */
	private $_renewedListCacheByKey = [];

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

	/** @var \DI\Container Shared container instance to avoid rebuilding */
	private static $_pSharedContainer = null;

	public function __construct(
		onOfficeSDK $pSDK = null,
		WPOptionWrapperBase $pWPOptionWrapper = null)
	{
		$this->_pSDK = $pSDK ?? new onOfficeSDK();
		$this->_pWPOptionWrapper = $pWPOptionWrapper ?? new WPOptionWrapperDefault();

		$this->_caches = [
			new DBCache(['ttl' => 3600]),
		];

		if (self::$_pSharedContainer === null) {
			$pDIContainerBuilder = new ContainerBuilder();
			$pDIContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
			self::$_pSharedContainer = $pDIContainerBuilder->build();
		}
		$this->_pContainer = self::$_pSharedContainer;
		$this->_encrypter = $this->_pContainer->make(SymmetricEncryption::class);
		$this->_pSDK->setCaches($this->_caches);
		$this->_pSDK->setApiServer(ONOFFICE_API_SERVER);
		$this->_pSDK->setApiVersion('latest');
		$this->_pSDK->setApiCurlOptions(
			[
				CURLOPT_SSL_VERIFYPEER => true,
				CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS
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

		$this->ensureListCacheWarmIfNeeded($actionId, $resourceId, $identifier, $resourceType, $parameters);

		$id = $this->_pSDK->call($actionId, $resourceId, $identifier, $resourceType, $parameters);

		if ($callback !== null) {
			$this->_callbacksAfterSend[$id] = $callback;
		}

		return $id;
	}

	/**
	 * Warm list cache once per list/language when cache is missing or incomplete.
	 *
	 * @param string $actionId
	 * @param string $resourceId
	 * @param string|null $identifier
	 * @param string $resourceType
	 * @param array $parameters
	 */
	private function ensureListCacheWarmIfNeeded(
		string $actionId,
		string $resourceId,
		$identifier,
		string $resourceType,
		array $parameters
	): void {
		if (!isset($parameters['listname']) || !array_key_exists('params_list_cache', $parameters)) {
			return;
		}

		$cacheResponse = $this->_pSDK->callFromCache(
			$actionId,
			$resourceId,
			$identifier,
			$resourceType,
			$parameters['params_list_cache']
		);

		$needsDerivedListData = ($parameters['formatoutput'] ?? false) === true;
		$hasDerivedListData = is_array($cacheResponse)
			&& isset($cacheResponse['raw']['data']['records'])
			&& is_array($cacheResponse['raw']['data']['records'])
			&& isset($cacheResponse['types'])
			&& is_array($cacheResponse['types']);

		if ($cacheResponse != null && (!$needsDerivedListData || $hasDerivedListData)) {
			return;
		}

		$language = $parameters['outputlanguage'] ?? Language::getDefault();
		$listCacheKey = sprintf('%s|%s', (string)$parameters['listname'], (string)$language);
		if (isset($this->_renewedListCacheByKey[$listCacheKey])) {
			return;
		}

		$this->renewCache($parameters['listname'], [$language]);
		$this->_renewedListCacheByKey[$listCacheKey] = true;
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

	private function createCacheForList($parameters, string $module, int $page = 1)
	{
		//1 get first page
		$pApiClientAction = new APIClientActionGeneric($this, onOfficeSDK::ACTION_ID_READ, $module);
		$pApiClientAction->setParameters($parameters);
		$pApiClientAction->addRequestToQueue()->sendRequests(false);
		$records = $pApiClientAction->getResult();
		$resultMeta = $pApiClientAction->getResultMeta();

		$cntAbsolute = $resultMeta['cntabsolute'] ?? 0;
		if (is_array($cntAbsolute)) {
			$cntAbsolute = $cntAbsolute[0] ?? 0;
		}
		$numpages = ceil(intval($cntAbsolute) / 500);
		//2 loop over other pages
		if($page == 1 && $numpages > 1)
		{
			for ($curPage = 2; $curPage < $numpages+1; $curPage++)
			{
				$parameters['listoffset'] = (500 * ($curPage -1));
				$tmpRecords = $this->createCacheForList($parameters, $module, $curPage);
				$records = array_merge_recursive($records, $tmpRecords);
			}
		}
		return $records;
	}
	/**
	 * cleans Cache and create new Cache from all lists
	 * (does not create cache for detail pages and similar objects)
	 *
	 */
	 public function renewCache(string $listName = null, array $languages = null)
	 {
			//1 get all lists
			//2 clean Cache
			//3 create cache for every list
			//3.1 for every language RecordManagerReadListViewEstate
			$pDataListViewFactory = $this->_pContainer->get(DataListViewFactory::class);
			$pDataListViewFactoryAddress = $this->_pContainer->get(DataListViewFactoryAddress::class);
			$pDefaultFilterBuilderFactory = $this->_pContainer->get(DefaultFilterBuilderFactory::class);
			$pDefaultFilterBuilderListViewAddressFactory = $this->_pContainer->get(DefaultFilterBuilderListViewAddressFactory::class);

			if ($languages === null || $languages === []) {
				$languages = Language::getAllWPMLLanguages();
			} else {
				$languages = array_values(array_unique($languages));
			}
			$estateLists = $this->getEstateLists($listName);
			$addressLists = $this->getAddressLists($listName);
			$this->_caches = [new DBCache(['ttl' => 3600])];
			$fieldsInformation = $this->getAllFields($languages);

			foreach ($this->_caches as $pCache) {
				foreach ($estateLists as $list) {
					$pListView = $pDataListViewFactory->getListViewByName($list->name);
					$pEstateList = new EstateList($pListView);

					$pListViewFilterBuilder = $pDefaultFilterBuilderFactory->buildDefaultListViewFilter($pListView);
					$pEstateList->setDefaultFilterBuilder($pListViewFilterBuilder);
					foreach ($languages as $lang) {
						$paramsRaw = $pEstateList->getEstateListParametersForCache(false, $lang); // raw
						$responseRaw = $this->createCacheForList($paramsRaw, 'estate');
						$pApiActionRaw = new ApiAction(onOfficeSDK::ACTION_ID_READ, 'estate', $paramsRaw, '', null);
						$pRequest = new Request($pApiActionRaw);
						$usedParametersRaw = $pRequest->getApiAction()->getActionParameters();
						$pCache->write($usedParametersRaw,serialize($responseRaw));

						$params = $pEstateList->getEstateListParametersForCache(true, $lang); // formatted
						$response = $this->createCacheForList($params, 'estate');
						$pApiAction = new ApiAction(onOfficeSDK::ACTION_ID_READ, 'estate', $params, '', null);
						$pRequest = new Request($pApiAction);
						$usedParameters = $pRequest->getApiAction()->getActionParameters();
						$response['raw'] = $responseRaw;
						$response['types'] = $fieldsInformation['estate'];
						$pCache->write($usedParameters,serialize($response));
					}
				}
				foreach ($addressLists as $list) {
					$pListView = $pDataListViewFactoryAddress->getListViewByName($list->name);
					$addressList = new AddressList($pListView);

					$pListViewFilterBuilder = $pDefaultFilterBuilderListViewAddressFactory->create($pListView);
					$addressList->setDefaultFilterBuilder($pListViewFilterBuilder);
					foreach ($languages as $lang) {
						$paramsRaw = $addressList->getAddressListParametersForCache(false, $lang); // raw
						$responseRaw = $this->createCacheForList($paramsRaw, 'address');
						$pApiActionRaw = new ApiAction(onOfficeSDK::ACTION_ID_READ, 'address', $paramsRaw, '', null);
						$pRequestRaw = new Request($pApiActionRaw);
						$usedParametersRaw = $pRequestRaw->getApiAction()->getActionParameters();
						$pCache->write($usedParametersRaw,serialize($responseRaw));

						$params = $addressList->getAddressListParametersForCache(true, $lang); // formatted
						$response = $this->createCacheForList($params, 'address');
						$pApiAction = new ApiAction(onOfficeSDK::ACTION_ID_READ, 'address', $params, '', null);
						$pRequest = new Request($pApiAction);
						$usedParameters = $pRequest->getApiAction()->getActionParameters();
						$response['raw'] = $responseRaw;
						$response['types'] = $fieldsInformation['address'];
						$pCache->write($usedParameters,serialize($response));
					}
				}
			}
	 }
	/**
	 * return all Fields from Api, to save in Cache
	 * @param array $language
	 * @return $fieldsByModule
	 */
	 private function getAllFields(array $languages)
	 {
		$fieldsByModule = [];
		foreach ($languages as $lang) {
			if (isset($this->_cachedFieldTypesByLanguage[$lang])) {
				foreach ($this->_cachedFieldTypesByLanguage[$lang] as $module => $moduleFields) {
					$fieldsByModule[$module] = $moduleFields;
				}
				continue;
			}

			$parametersGetFieldList = [
				'labels' => true,
				'showContent' => true,
				'showTable' => true,
				'language' => $lang,
				'modules' => [onOfficeSDK::MODULE_ADDRESS, onOfficeSDK::MODULE_ESTATE],
				'realDataTypes' => true,
			];

			$pApiClientActionFields = new APIClientActionGeneric($this, onOfficeSDK::ACTION_ID_GET, 'fields');
			$pApiClientActionFields->setParameters($parametersGetFieldList);
			$pApiClientActionFields->addRequestToQueue()->sendRequests();

			$records = $pApiClientActionFields->getResultRecords();
			$fieldTypesForLanguage = [];
			foreach ($records as $moduleProperties) {
				foreach ($moduleProperties['elements'] as $fieldName => $fieldProperties) {
					if ($fieldName != 'label') {
						$fieldTypesForLanguage[$moduleProperties['id']][$fieldName] = $fieldProperties['type'];
						$fieldsByModule[$moduleProperties['id']][$fieldName] = $fieldProperties['type'];
					}
				}
			}
			$this->_cachedFieldTypesByLanguage[$lang] = $fieldTypesForLanguage;
		}
		return $fieldsByModule;
	}

	private function getEstateLists(string $listName = null) : array
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
		if($listName != null) {
			$pRecordRead->addWhere("`name` = '".esc_sql($listName)."'");
		}

		return $pRecordRead->getRecordsSortedAlphabetically();
	}

	private function getAddressLists(string $listName = null) : array
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
		if($listName != null) {
			$pRecordRead->addWhere("`name` = '".esc_sql($listName)."'");
		}

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
