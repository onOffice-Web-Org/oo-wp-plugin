<?php

namespace onOffice\SDK;

use onOffice\SDK\Cache\onOfficeSDKCache;
use onOffice\SDK\internal\ApiCall;

class onOfficeSDK
{
	const ACTION_ID_READ = 'urn:onoffice-de-ns:smart:2.5:smartml:action:read';

	const ACTION_ID_CREATE = 'urn:onoffice-de-ns:smart:2.5:smartml:action:create';

	const ACTION_ID_MODIFY = 'urn:onoffice-de-ns:smart:2.5:smartml:action:modify';

	const ACTION_ID_GET = 'urn:onoffice-de-ns:smart:2.5:smartml:action:get';

	const ACTION_ID_DO = 'urn:onoffice-de-ns:smart:2.5:smartml:action:do';

	const ACTION_ID_DELETE = 'urn:onoffice-de-ns:smart:2.5:smartml:action:delete';

	const RELATION_TYPE_BUYER = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:buyer';

	const RELATION_TYPE_TENANT = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:renter';

	const RELATION_TYPE_OWNER = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:owner';

	const MODULE_ADDRESS = 'address';

	const MODULE_ESTATE = 'estate';

	const MODULE_SEARCHCRITERIA = 'searchcriteria';

	const RELATION_TYPE_CONTACT_BROKER = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:contactPerson';

	/** use with caution: retrieves every contact person, not just brokers! */
	const RELATION_TYPE_CONTACT_PERSON = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:contactPersonAll';

	/** Units of complex (Immobilienanlage) (complex = parent, estate = child) */
	const RELATION_TYPE_COMPLEX_ESTATE_UNITS = 'urn:onoffice-de-ns:smart:2.5:relationTypes:complex:estate:units';

	/** Owner of estate (estate = parent record, address = child record) */
	const RELATION_TYPE_ESTATE_ADDRESS_OWNER = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:owner';

	/** @var ApiCall */
	private $apiCall = null;

	/**
	 * @param ApiCall|null $apiCall
	 */
	public function __construct(ApiCall $apiCall = null)
	{
		if (null === $apiCall) {
			$apiCall = new ApiCall();
		}
		$this->apiCall = $apiCall;
		$this->apiCall->setServer('https://api.onoffice.de/api/');
	}


	/**
	 * @param string $apiVersion
	 */
	public function setApiVersion($apiVersion)
	{
		$this->apiCall->setApiVersion($apiVersion);
	}



	/**
	 * @param string $server
	 */
	public function setApiServer($server)
	{
		$this->apiCall->setServer($server);
	}



	/**
	 * @param array  $curlOptions
	 */
	public function setApiCurlOptions($curlOptions)
	{
		$this->apiCall->setCurlOptions($curlOptions);
	}


	/**
	 * @param string $actionId from constant above
	 * @param string $resourceType
	 * @param array $parameters
	 *
	 * @return int
	 */
	public function callGeneric($actionId, $resourceType, $parameters)
	{
		return $this->apiCall->callByRawData($actionId, '', '', $resourceType, $parameters);
	}


	/**
	 * @param string $actionId
	 * @param string $resourceId
	 * @param string $identifier
	 * @param string $resourceType
	 * @param array $parameters
	 *
	 * @return int
	 */
	public function call($actionId, $resourceId, $identifier, $resourceType, $parameters)
	{
		return $this->apiCall->callByRawData
			($actionId, $resourceId, $identifier, $resourceType, $parameters);
	}
	/**
	 * @param string $actionId
	 * @param string $resourceId
	 * @param string $identifier
	 * @param string $resourceType
	 * @param array $parameters
	 *
	 * @return int
	 */
	public function callFromCache($actionId, $resourceId, $identifier, $resourceType, $parameters)
	{
		return $this->apiCall->callByRawDataFromCache
			($actionId, $resourceId, $identifier, $resourceType, $parameters);
	}

	/**
	 * @param string $token
	 * @param string $secret
	 *
	 * @throws Exception\HttpFetchNoResultException
	 */
	public function sendRequests($token, $secret, bool $saveToCache = true)
	{
		$this->apiCall->sendRequests($token, $secret, null, $saveToCache);
	}

	/**
	 * @param int $number
	 * @return array
	 *
	 * @throws Exception\ApiCallFaultyResponseException
	 */
	public function getResponseArray($number)
	{
		return $this->apiCall->getResponse($number);
	}


	/**
	 * @param onOfficeSDKCache $pCache
	 */
	public function addCache(onOfficeSDKCache $pCache)
	{
		$this->apiCall->addCache($pCache);
	}


	/**
	 * @param array $cacheInstances
	 */
	public function setCaches(array $cacheInstances)
	{
		array_map(array($this->apiCall, 'addCache'), $cacheInstances);
	}

	public function removeCacheInstances()
	{
		$this->apiCall->removeCacheInstances();
	}


	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->apiCall->getErrors();
	}
}
