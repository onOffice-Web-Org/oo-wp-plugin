<?php

namespace onOffice\SDK\internal;

use onOffice\SDK\Cache\onOfficeSDKCache;
use onOffice\SDK\Exception\ApiCallFaultyResponseException;
use onOffice\SDK\Exception\ApiCallNoActionParametersException;
use onOffice\SDK\Exception\HttpFetchNoResultException;
use onOffice\SDK\internal\ApiAction;
use onOffice\SDK\internal\Request;
use Location\Coordinate;
use Location\Distance\Vincenty;

/**
 * @internal
 */
class ApiCall
{
	/** @var Request[] */
	private $_requestQueue = array();

	/** @var array */
	private $_responses = array();

	/** @var array */
	private $_errors = array();

	/** @var string */
	private $_apiVersion = 'stable';

	/** @var onOfficeSDKCache[] */
	private $_caches = array();

	/** @var string */
	private $_server = null;

	/** @var array */
	private $_curlOptions = array();


	/**
	 * @param string $actionId
	 * @param string $resourceId
	 * @param string $identifier
	 * @param string $resourceType
	 * @param array $parameters
	 *
	 * @return int the request handle
	 */

	public function callByRawData($actionId, $resourceId, $identifier, $resourceType, $parameters = array())
	{
		$pApiAction = new ApiAction($actionId, $resourceType, $parameters, $resourceId, $identifier);

		$pRequest = new Request($pApiAction);
		$requestId = $pRequest->getRequestId();
		$this->_requestQueue[$requestId] = $pRequest;

		return $requestId;
	}
	/**
	 * @param string $actionId
	 * @param string $resourceId
	 * @param string $identifier
	 * @param string $resourceType
	 * @param array $parameters
	 *
	 * @return array values
	 */

	public function callByRawDataFromCache($actionId, $resourceId, $identifier, $resourceType, $parameters = array())
	{
		$pApiAction = new ApiAction($actionId, $resourceType, $parameters, $resourceId, $identifier);

		$pRequest = new Request($pApiAction);

		$usedParameters = $pRequest->getApiAction()->getActionParameters();
		$cachedResponse = $this->getFromCache($usedParameters);

		return $cachedResponse;
	}

	/**
	 * @param string $token
	 * @param string $secret
	 * @param HttpFetch|null $httpFetch
	 *
	 * @throws HttpFetchNoResultException
	 */

	public function sendRequests($token, $secret, HttpFetch $httpFetch = null, bool $saveToCache = true)
	{
		$this->collectOrGatherRequests($token, $secret, $httpFetch, $saveToCache);
	}

	/**
	 * @param string $token
	 * @param array $actionParameters
	 * @param array $actionParametersOrder
	 * @param \onOffice\SDK\internal\HttpFetch|null $httpFetch
	 *
	 * @throws HttpFetchNoResultException
	 */

	private function sendHttpRequests(
		$token,
		array $actionParameters,
		array $actionParametersOrder,
		HttpFetch $httpFetch = null,
		bool $saveToCache = true
	) {
		if (count($actionParameters) === 0)
		{
			return;
		}

		$responseHttp = $this->getFromHttp($token, $actionParameters, $httpFetch);

		$result = json_decode($responseHttp, true);


		if (!isset($result['response']['results']))
		{
			throw new HttpFetchNoResultException;
		}

		$idsForCache = array();

		foreach ($result['response']['results'] as $requestNumber => $resultHttp)
		{
			$pRequest = $actionParametersOrder[$requestNumber];
			$requestId = $pRequest->getRequestId();

			if ($resultHttp['status']['errorcode'] == 0)
			{
				$this->_responses[$requestId] = new Response($pRequest, $resultHttp);
				$idsForCache []= $requestId;
			}
			else
			{
				$this->_errors[$requestId] = $resultHttp;
			}
		}

		if($saveToCache)
			$this->writeCacheForResponses($idsForCache);
	}

	/**
	 * @param string $token
	 * @param string $secret
	 * @param HttpFetch|null $httpFetch
	 *
	 * @throws HttpFetchNoResultException
	 */

	private function collectOrGatherRequests($token, $secret, HttpFetch $httpFetch = null, bool $saveToCache = true)
	{
		$saveToCache = true;
		$actionParameters = array();
		$actionParametersOrder = array();

		foreach ($this->_requestQueue as $requestId => $pRequest)
		{
			$usedParameters = $pRequest->getApiAction()->getActionParameters();
			$cachedResponse = $this->getFromCache($usedParameters);
			$params = $usedParameters["parameters"];

			if ($cachedResponse === null)
			{
				$parametersThisAction = $pRequest->createRequest($token, $secret);

				$actionParameters[] = $parametersThisAction;
				$actionParametersOrder[] = $pRequest;
			}
			else
			{
				if($cachedResponse != null && isset($params['listname']))
				{
					if($params["formatoutput"] == true)
					{
						$cachedResponse["data"]["records"] = $this->filterRecords($cachedResponse["data"]["records"], $params["filter"], $params['outputlanguage']);
						if($params["sortby"] != null)
							$cachedResponse["data"]["records"] = $this->sortRecords($cachedResponse["data"]["records"], $params["filter"], $params["sortby"], $params["sortorder"] ?? 'ASC');
						$cachedResponse["data"]["meta"]["cntabsolute"] = count($cachedResponse["data"]["records"]);

						$cachedResponse["data"]["records"] =
							$this->recordsPerPage($cachedResponse["data"]["records"], intval($params["listlimit"] ?? 20), intval($params["listoffset"] ?? 0));
					}
				}
				$this->_responses[$requestId] = new Response($pRequest, $cachedResponse);
				$saveToCache = false;
			}
		}

		$this->sendHttpRequests($token, $actionParameters, $actionParametersOrder, $httpFetch, $saveToCache);
		$this->_requestQueue = array();
	}

	private function sortRecords(array $records, array $filter, string $sortby, string $sortorder)
	{
		$newRecords = $records;
		$sortBy = (array_key_exists('geo', $filter) && array_key_exists('loc', $filter['geo'][0])) ? 'geo_distance' : $sortby;
		$sortOrder =  (array_key_exists('geo', $filter) && array_key_exists('loc', $filter['geo'][0])) ? 'ASC' : $sortorder;
		if(isset($sortby))
		{
			usort($newRecords, function ($a, $b) use ($sortBy, $sortOrder) {
				$sortA = $a['elements'][$sortBy];
				$sortB = $b['elements'][$sortBy];
				if ($sortOrder === 'ASC') {
					return ($sortA > $sortB) ? 1 : -1;
				}
				else {
					return ($sortA > $sortB) ? -1 : 1;
				}
			});
		}
		return $newRecords;
	}
	private function recordsPerPage(array $records, int $limit, int $offset)
	{
		return array_slice($records, $offset, $limit);
	}

	private function filterRecords(array $records, array $filter, string $lang = 'de_DE')
	{
		$filtredArray = [];
		$calculator = new Vincenty();
		$isGeoAndMin = 0;
		$isGeoAndMax = 0;

		foreach($records as $index => $item) {
			$filtredArray[$index] = $item;
			foreach($filter as $key => $value) {
				if($key == "veroeffentlichen" || $key == "referenz" || $key == "homepage_veroeffentlichen")
					continue;
				$op = $value[0]["op"];
				$val = $value[0]["val"];
				if($val === null || (is_array($val) && empty($val)) || (is_string($val) && trim($val) === ''))
					continue;

				if(strtolower($op) === '=')
				{
					if(!array_key_exists($key,$item["elements"]) || strtolower($item["elements"][$key]) != strtolower($val)){
						unset($filtredArray[$index]);
						break;
					}
				}
				elseif (strtolower($op) === 'like')
				{
					$val = str_replace('%','',$val);
					if(!array_key_exists($key,$item["elements"]) || !str_contains($item["elements"][$key], $val)){
						unset($filtredArray[$index]);
						break;
					}
				}
				elseif (strtolower($op) === 'in')
				{
					$elVal = strtolower($item["elements"][$key]);
					if($key === "Id") {
						$elVal = str_replace(',','',$elVal);
						$elVal = str_replace('.','',$elVal);
					}

					if(!in_array($elVal, $val)){
						unset($filtredArray[$index]);
						break;
					}
				}
				elseif ($op === '<=')
				{
					if($this->isBigger($key, $val, $item["elements"], $lang)){
						unset($filtredArray[$index]);
						break;
					}
				}
				elseif ($op === '>=')
				{
					if($this->isSmaller($key, $val, $item["elements"], $lang)){
						unset($filtredArray[$index]);
						break;
					}
				}
				elseif (strtolower($op) === 'geo')
				{
					$km = intval($val);
					$min = $value[0]["min"];
					$max = $value[0]["max"];

					$loc = $value[0]["loc"] ?? '';
					if($loc != '' && $min != null && intval($min) > 0)
						$isGeoAndMin = $min;
					if($loc != '' && $max != null && intval($max) > 0)
						$isGeoAndMax = $max;

					$selectedCoordinates = explode(",", $loc);
					if(!array_key_exists('laengengrad',$item["elements"]) || !array_key_exists('breitengrad',$item["elements"])
						|| !is_array($selectedCoordinates) || count($selectedCoordinates) != 2)
						continue;
					if($item["elements"]['laengengrad'] == null || $item["elements"]['breitengrad'] == null){
						unset($filtredArray[$index]);
						break;
					}

					$coordinate1 = new Coordinate($item["elements"]['laengengrad'], $item["elements"]['breitengrad']);
					$coordinate2 = new Coordinate($selectedCoordinates[0], $selectedCoordinates[1]);
					$distance = $calculator->getDistance($coordinate1, $coordinate2);

					if(intval($distance/1000) > $km){
						unset($filtredArray[$index]);
						break;
					} else {
						$filtredArray[$index]["elements"]['geo_distance'] = intval($distance);
					}
				}
			}
		}
		if($isGeoAndMin > 0 && count($filtredArray) < $isGeoAndMin)
		{
			$newFilter = $filter;
			$newFilter['geo'][0]["val"] = 1000; //km
			$newFilter['geo'][0]["min"] = 0;
			$filtredArray = $this->filterRecords($records, $newFilter, $lang);
			$filtredArray = $this->sortRecords($filtredArray, $newFilter, 'geo_distance', 'ASC');
			if($filtredArray > $isGeoAndMin)
				$filtredArray = array_slice($filtredArray, 0, $isGeoAndMin);
		}
		if($isGeoAndMax > 0 && count($filtredArray) > $isGeoAndMax)
		{
			$filtredArray = $this->sortRecords($filtredArray, $filter, 'geo_distance', 'ASC');
			$filtredArray = array_slice($filtredArray, 0, $isGeoAndMax);
		}
		return $filtredArray;
	}
	/**
	 * @param array $responses
	 */
	private function isBigger(string $key, mixed $val, array $elements, string $lang = 'de_DE')
	{
		return $this->isSmaller($key, $val, $elements, $lang, false);
	}
	/**
	 * @param array $responses
	 */
	private function isSmaller(string $key, mixed $val, array $elements, string $lang = 'de_DE', bool $isSmaller = true)
	{
		$elVal = $elements[$key];
		if(is_string($elements[$key]) && (str_contains($elements[$key],'€') || str_contains($elements[$key],'$'))) {
			$curr = "USD";
			$formatter = new \NumberFormatter($lang, \NumberFormatter::CURRENCY);
			// $elVal = str_replace(' €',"\xc2\xa0$",$elVal);
			// $elVal = str_replace(' $',"\xc2\xa0$",$elVal);
			$elVal = preg_replace("/[^0-9]/", '', $elVal);
			$elVal = trim($elVal);
			$elVal = $formatter->parseCurrency($elVal, $curr);
		}
		$dateVal = date_create_from_format('Y-m-d H:i:s', $val); //1955-12-29
		if($dateVal === false){ //compare Int
			$elVal = preg_replace("/[^0-9 ]/", '', $elVal);
			if($isSmaller){
				return (floatval($elVal) < floatval($val));
			} else {
				return (floatval($elVal) > floatval($val));
			}
		} else { //compare Dates
			$compare = date_create_from_format('d.m.Y', $elVal);
			if($isSmaller) {
				return ($compare != false && $compare < $dateVal);
			} else {
				return ($compare != false && $compare > $dateVal);
			}
		}
		return false;
	}
	/**
	 * @param array $responses
	 */
	private function writeCacheForResponses(array $responses)
	{
		if (count($this->_caches) === 0)
		{
			return;
		}

		$responseObjects = array_intersect_key($this->_responses, array_flip($responses));

		foreach ($responseObjects as $pResponse)
		{
			/* @var $pResponse Response */
			if ($pResponse->isCacheable())
			{
				$responseData = $pResponse->getResponseData();
				$requestParameters = $pResponse->getRequest()->getApiAction()->getActionParameters();
				$this->writeCache(serialize($responseData), $requestParameters);
			}
		}
	}


	/**
	 * @param array $parameters
	 * @return array
	 */
	private function getFromCache($parameters)
	{
		foreach ($this->_caches as $pCache)
		{
			$resultCache = $pCache->getHttpResponseByParameterArray($parameters);

			if ($resultCache != null)
			{
				return unserialize($resultCache);
			}
		}

		return null;
	}

	/**
	 * @param string $result
	 * @param $actionParameters
	 */
	private function writeCache($result, $actionParameters)
	{
		foreach ($this->_caches as $pCache)
		{
			$pCache->write($actionParameters, $result);
		}
	}


	/**
	 * @param array $curlOptions
	 */
	public function setCurlOptions($curlOptions)
	{
		$this->_curlOptions = $curlOptions;
	}

	/**
	 * @param string $token
	 * @param array $actionParameters
	 * @param \onOffice\SDK\internal\HttpFetch|null $httpFetch
	 * @return string
	 *
	 * @throws HttpFetchNoResultException
	 */
	private function getFromHttp(
		$token,
		$actionParameters,
		HttpFetch $httpFetch = null
	) {

		$request = array
			(
				'token' => $token,
				'request' => array('actions' => $actionParameters),
			);

		if (null === $httpFetch) {
			$httpFetch = new HttpFetch($this->getApiUrl(), json_encode($request));
			$httpFetch->setCurlOptions($this->_curlOptions);
		}

		$response = $httpFetch->send();

		return $response;
	}


	/**
	 * @param int $handle
	 * @return array
	 * @throws ApiCallFaultyResponseException
	 */
	public function getResponse($handle)
	{
		if (array_key_exists($handle, $this->_responses))
		{
			/* @var $pResponse Response */
			$pResponse = $this->_responses[$handle];

			if (!$pResponse->isValid())
			{
				throw new ApiCallFaultyResponseException('Handle: '.$handle);
			}

			unset($this->_responses[$handle]);

			// do not return $pResponse itself
			return $pResponse->getResponseData();
		}
	}


	/**
	 * @return string
	 */
	private function getApiUrl()
	{
		return $this->_server.urlencode($this->_apiVersion).'/api.php';
	}


	/**
	 * @param string $apiVersion
	 */
	public function setApiVersion($apiVersion)
	{
		$this->_apiVersion = $apiVersion;
	}


	/**
	 * @param string $server
	 */
	public function setServer($server)
	{
		$this->_server = $server;
	}

	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_errors;
	}


	/**
	 * @param onOfficeSDKCache $pCache
	 */
	public function addCache(onOfficeSDKCache $pCache)
	{
		$this->_caches []= $pCache;
	}

	public function removeCacheInstances() {
		$this->_caches = array();
	}
}
