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
	 * In-memory cache for API responses within a single PHP request.
	 * Prevents duplicate identical API calls (e.g. fields, idsfromrelation)
	 * from hitting the network multiple times per page load.
	 * @var array<string, array>
	 */
	private static $_inMemoryCache = array();


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

	public function sendRequests($token, $secret, HttpFetch $httpFetch = null, bool $saveToCache = true, $claim = null)
	{
		$this->collectOrGatherRequests($token, $secret, $httpFetch, $saveToCache, $claim);
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
		if($saveToCache === true) {
			$this->writeCacheForResponses($idsForCache);
		}
	}

	/**
	 * @param string $token
	 * @param string $secret
	 * @param HttpFetch|null $httpFetch
	 *
	 * @throws HttpFetchNoResultException
	 */

	private function collectOrGatherRequests($token, $secret, HttpFetch $httpFetch = null, bool $saveToCache = true, $claim = null)
	{
		$actionParameters = array();
		$actionParametersOrder = array();
		$sourceRequestIdByCacheKey = array();
		$duplicateRequestsBySourceRequestId = array();

		foreach ($this->_requestQueue as $requestId => $pRequest)
		{
			$usedParameters = $this->normalizeFieldDependencyParameters(
				$pRequest->getApiAction()->getActionParameters()
			);
			$params = $usedParameters["parameters"];

			// 1. Check in-memory cache first (catches ALL duplicate calls within this PHP request)
			$inMemoryKey = $this->getInMemoryCacheKey($usedParameters);
			if (isset(self::$_inMemoryCache[$inMemoryKey]))
			{
				$inMemoryResponse = self::$_inMemoryCache[$inMemoryKey];
				// Apply list-specific filtering/sorting if needed (same logic as DB cache hit)
				if (isset($params['listname']))
				{
					$inMemoryResponse = $this->applyListCacheFiltering($inMemoryResponse, $params);
				}
				$this->_responses[$requestId] = new Response($pRequest, $inMemoryResponse);
				continue;
			}

			// 2. Check DB cache (existing logic for list-based caching)
			$cachedResponse = $this->getFromCache($usedParameters);

			if ($cachedResponse === null)
			{
				if (isset($sourceRequestIdByCacheKey[$inMemoryKey]))
				{
					$sourceRequestId = $sourceRequestIdByCacheKey[$inMemoryKey];
					if (!isset($duplicateRequestsBySourceRequestId[$sourceRequestId]))
					{
						$duplicateRequestsBySourceRequestId[$sourceRequestId] = array();
					}
					$duplicateRequestsBySourceRequestId[$sourceRequestId][$requestId] = $pRequest;
					continue;
				}

				$parametersThisAction = $this->normalizeFieldDependencyParameters(
					$pRequest->createRequest($token, $secret)
				);
				
				if($claim){
					if(!isset($parametersThisAction['parameters'])){
						$parametersThisAction['parameters'] = array();
					}
					$parametersThisAction['parameters']['extendedclaim'] = $claim;
				}

				$actionParameters[] = $parametersThisAction;
				$actionParametersOrder[] = $pRequest;
				$sourceRequestIdByCacheKey[$inMemoryKey] = $requestId;
			}
			else
			{
				// Store in in-memory cache for subsequent identical calls
				self::$_inMemoryCache[$inMemoryKey] = $cachedResponse;

				if($cachedResponse != null && isset($params['listname']))
				{
					$cachedResponse = $this->applyListCacheFiltering($cachedResponse, $params);
				}
				$this->_responses[$requestId] = new Response($pRequest, $cachedResponse);
			}
		}

		$this->sendHttpRequests($token, $actionParameters, $actionParametersOrder, $httpFetch, $saveToCache);

		// Reuse source results for duplicate requests that were queued in the same send cycle.
		foreach ($duplicateRequestsBySourceRequestId as $sourceRequestId => $duplicateRequests)
		{
			if (isset($this->_responses[$sourceRequestId]))
			{
				$sourceResponseData = $this->_responses[$sourceRequestId]->getResponseData();
				foreach ($duplicateRequests as $duplicateRequestId => $duplicateRequest)
				{
					$this->_responses[$duplicateRequestId] = new Response($duplicateRequest, $sourceResponseData);
				}
			}
			elseif (isset($this->_errors[$sourceRequestId]))
			{
				$sourceError = $this->_errors[$sourceRequestId];
				foreach (array_keys($duplicateRequests) as $duplicateRequestId)
				{
					$this->_errors[$duplicateRequestId] = $sourceError;
				}
			}
		}

		// Store HTTP responses in in-memory cache for deduplication
		// Only cache when saveToCache is true — renewCache() passes false and its
		// intermediate results (individual pages) should not pollute the cache.
		if ($saveToCache) {
			foreach ($actionParametersOrder as $idx => $pRequest)
			{
				$reqId = $pRequest->getRequestId();
				if (isset($this->_responses[$reqId]))
				{
					$pResponse = $this->_responses[$reqId];
					$responseData = $pResponse->getResponseData();
					$usedParams = $this->normalizeFieldDependencyParameters(
						$pRequest->getApiAction()->getActionParameters()
					);
					$key = $this->getInMemoryCacheKey($usedParams);
					self::$_inMemoryCache[$key] = $responseData;
				}
			}
		}

		$this->_requestQueue = array();
	}

	/**
	 * Apply list-specific filtering, sorting and pagination to a cached response.
	 * Extracted from collectOrGatherRequests to be reusable for both DB and in-memory cache hits.
	 *
	 * @param array $cachedResponse
	 * @param array $params
	 * @return array
	 */
	private function applyListCacheFiltering(array $cachedResponse, array $params): array
	{
		if ($params["formatoutput"] == true)
		{
			$filter = (isset($params["filter"]) && is_array($params["filter"]))
				? $params["filter"]
				: [];

			if (
				!isset($cachedResponse["data"]["records"]) ||
				!is_array($cachedResponse["data"]["records"]) ||
				!isset($cachedResponse["raw"]["data"]["records"]) ||
				!is_array($cachedResponse["raw"]["data"]["records"]) ||
				!isset($cachedResponse["types"]) ||
				!is_array($cachedResponse["types"])
			) {
				return $cachedResponse;
			}

			$this->filterRecords($cachedResponse, $filter);
			if(array_key_exists("sortby", $params) && $params["sortby"] != null)
				$cachedResponse["data"]["records"] = $this->sortRecords($cachedResponse, $filter, $params["sortby"], $params["sortorder"] ?? 'ASC');
			$cachedResponse["data"]["meta"]["cntabsolute"] = count($cachedResponse["data"]["records"]);

			$cachedResponse["data"]["records"] =
				$this->recordsPerPage($cachedResponse["data"]["records"], intval($params["listlimit"] ?? 20), intval($params["listoffset"] ?? 0));
		}
		return $cachedResponse;
	}

	/**
	 * Generate a cache key for the in-memory cache.
	 * Excludes timestamp to ensure identical logical calls produce the same key.
	 *
	 * @param array $actionParameters
	 * @return string
	 */
	private function getInMemoryCacheKey(array $actionParameters): string
	{
		$keyData = $actionParameters;
		unset($keyData['timestamp']); // timestamp varies per call, not relevant for dedup

		// Keep optional default flags stable for semantically identical requests.
		if (
			isset($keyData['resourcetype']) &&
			$keyData['resourcetype'] === 'fields' &&
			isset($keyData['parameters']) &&
			is_array($keyData['parameters']) &&
			!array_key_exists('showfielddependencies', $keyData['parameters'])
		) {
			$keyData['parameters']['showfielddependencies'] = false;
		}

		$keyData = $this->normalizeForInMemoryCacheKey($keyData);
		return md5(serialize($keyData));
	}

	/**
	 * Recursively normalize values for stable in-memory cache keys.
	 * Sorts associative array keys while preserving numeric-indexed list order.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	private function normalizeForInMemoryCacheKey($value)
	{
		if (!is_array($value)) {
			return $value;
		}

		if ($this->isAssocArray($value)) {
			ksort($value);
		}

		foreach ($value as $key => $item) {
			$value[$key] = $this->normalizeForInMemoryCacheKey($item);
		}

		return $value;
	}

	/**
	 * @param array $value
	 * @return bool
	 */
	private function isAssocArray(array $value): bool
	{
		if ($value === array()) {
			return false;
		}

		return array_keys($value) !== range(0, count($value) - 1);
	}

	/**
	 * For fields requests, always request dependencies to keep one canonical payload.
	 * This allows later fields requests (with/without explicit showfielddependencies)
	 * to reuse the same in-memory cache entry.
	 *
	 * @param array $actionParameters
	 * @return array
	 */
	private function normalizeFieldDependencyParameters(array $actionParameters): array
	{
		if (
			isset($actionParameters['resourcetype']) &&
			$actionParameters['resourcetype'] === 'fields'
		) {
			if (!isset($actionParameters['parameters']) || !is_array($actionParameters['parameters'])) {
				$actionParameters['parameters'] = [];
			}
			$actionParameters['parameters']['showfielddependencies'] = true;
			ksort($actionParameters['parameters']);
		}

		return $actionParameters;
	}
	private function tofloat (string $num)
	{
		$dotPos = strrpos($num, '.');
		$commaPos = strrpos($num, ',');
		$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos : ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

		if (!$sep) {
				return floatval(preg_replace("/[^0-9]/", "", $num));
		}

		return floatval(
				preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
				preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
		);
	}
	private function sortRecords(array $cachedResponse, array $filter, $sortby, string $sortorder)
	{
		$newRecords = $cachedResponse["data"]["records"];
		$newRecordsRaw = $cachedResponse["raw"]["data"]["records"];
		foreach ($newRecords as $index => &$record) {
			$record['elementsRaw'] = isset($newRecordsRaw[$index]['elements']) ? $newRecordsRaw[$index]['elements'] : null;
		}
		$fieldTypes = $cachedResponse["types"];
		$sortBy = (array_key_exists('geo', $filter) && array_key_exists('loc', $filter['geo'][0])) ? 'geo_distance' : $sortby;
		$sortOrder = (array_key_exists('geo', $filter) && array_key_exists('loc', $filter['geo'][0])) ? 'ASC' : $sortorder;
		if(isset($sortby))
		{
			$compareRecords = function ($a, $b, $sortBy, $sortOrder, $fieldTypes) {
				$fieldType = $fieldTypes[$sortBy] ?? null;
				if(in_array($fieldType, ['boolean', 'date', 'datetime', 'float', 'integer'])){
					$sortA = isset($a['elementsRaw'][$sortBy]) ? $a['elementsRaw'][$sortBy]
						: (isset($a['elements'][$sortBy]) ? $a['elements'][$sortBy] : '');
					$sortB = isset($b['elementsRaw'][$sortBy]) ? $b['elementsRaw'][$sortBy]
						: (isset($b['elements'][$sortBy]) ? $b['elements'][$sortBy] : '');
				}
				else{
					$sortA = isset($a['elements'][$sortBy]) ? $a['elements'][$sortBy] : '';
					$sortB = isset($b['elements'][$sortBy]) ? $b['elements'][$sortBy] : '';
				}
				if($fieldType === "integer" || $fieldType === "float")
				{
					$sortA = $this->tofloat($sortA ?? "");
					$sortB = $this->tofloat($sortB ?? "");
				}
				if($fieldType === "date")
				{
					$sortA = strtotime($sortA);
					$sortB = strtotime($sortB);
				}
				if($fieldType === "boolean")
				{
					$sortA = $sortA === "" ? "0" : $sortA;
					$sortB = $sortB === "" ? "0" : $sortB;
				}
				if($sortA == $sortB){
					return 0;
				}
				if ($sortOrder === 'ASC') {
					return ($sortA > $sortB) ? 1 : -1;
				}
				else {
					return ($sortA > $sortB) ? -1 : 1;
				}
			};
			if (is_string($sortBy)) {
				usort($newRecords, function ($a, $b) use ($compareRecords, $sortBy, $sortOrder, $fieldTypes) {
					return $compareRecords($a, $b, $sortBy, $sortOrder, $fieldTypes);
				});
			}
			elseif (is_array($sortBy)) {
				usort($newRecords, function ($a, $b) use ($compareRecords, $sortBy, $fieldTypes) {
					foreach ($sortBy as $field => $order) {
						$result = $compareRecords($a, $b, $field, $order, $fieldTypes);
						if ($result !== 0) {
							return $result;
						}
					}
					return 0; // All fields equal
				});
			}
		}
		return $newRecords;
	}
	private function recordsPerPage(array $records, int $limit, int $offset)
	{
		return array_slice($records, $offset, $limit);
	}

	private function filterRecords(array &$cachedResponse, array $filter)
	{
		if (
			!isset($cachedResponse["data"]["records"]) ||
			!is_array($cachedResponse["data"]["records"]) ||
			!isset($cachedResponse["raw"]["data"]["records"]) ||
			!is_array($cachedResponse["raw"]["data"]["records"]) ||
			!isset($cachedResponse["types"]) ||
			!is_array($cachedResponse["types"])
		) {
			return;
		}

		$records = $cachedResponse["data"]["records"];
		$filteredArray = $records;
		$filteredArrayRaw = $cachedResponse["raw"]["data"]["records"];
		$fieldTypes = $cachedResponse["types"];

		$calculator = new Vincenty();
		$isGeoAndMin = 0;
		$isGeoAndMax = 0;

		foreach($filteredArray as $index => $item) {
			$k = array_search($item["id"], array_column($filteredArrayRaw, "id"));
			$itemRaw = $filteredArrayRaw[$k];
			if($itemRaw == null)
			{
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Logging critical API errors
    			error_log("Error in ApiCall by filtering records: ItemRaw is null");
				continue;
			}
			foreach($filter as $fieldName => $value) {
				if($fieldName == "veroeffentlichen" || $fieldName == "referenz" || $fieldName == "homepage_veroeffentlichen")
					continue;
				foreach ($value as $fieldValue) {
					$op = $fieldValue["op"];
					$val = $fieldValue["val"];
					if($val === null || (is_string($val) && trim($val) === ''))
						continue;

					if(strtolower($op) === '=')
					{
						if(!array_key_exists($fieldName,$item["elements"]))
						{
							unset($filteredArray[$index]);
							break 2;
						}
						if(is_array($val))
						{
							if(!in_array($item["elements"][$fieldName],$val)){
								unset($filteredArray[$index]);
								break 2;
							}

						} else {
							//int compare
							if($fieldTypes[$fieldName] === "integer"
								&& intval($itemRaw["elements"][$fieldName]) != intval($val))
							{
								unset($filteredArray[$index]);
								break 2;
							}
							//float compare
							if($fieldTypes[$fieldName] === "float"
								&& floatval($itemRaw["elements"][$fieldName]) != floatval($val))
							{
								unset($filteredArray[$index]);
								break 2;
							}
							//boolean compare
							if($fieldTypes[$fieldName] === "boolean"
								&& (intval($itemRaw["elements"][$fieldName]) != intval($val)))
							{
								unset($filteredArray[$index]);
								break 2;
							}

							//string compare
							if(($fieldTypes[$fieldName] === "varchar" || $fieldTypes[$fieldName] === "varchar")
								&& mb_strtolower($item["elements"][$fieldName]) != mb_strtolower($val)){
								unset($filteredArray[$index]);
								break 2;
							}
						}
					}
					elseif(strtolower($op) === '!=')
					{
						if(is_array($val))
						{
							if(!in_array($item["elements"][$fieldName],$val)){
								unset($filteredArray[$index]);
								break 2;
							}

						} else {
							//int compare
							if($fieldTypes[$fieldName] === "integer"
								&& intval($itemRaw["elements"][$fieldName]) == intval($val))
							{
								unset($filteredArray[$index]);
								break 2;
							}
							//float compare
							if($fieldTypes[$fieldName] === "float"
								&& floatval($itemRaw["elements"][$fieldName]) == floatval($val))
							{
								unset($filteredArray[$index]);
								break 2;
							}
							//boolean compare
							if($fieldTypes[$fieldName] === "boolean"
								&& boolval($itemRaw["elements"][$fieldName]) == boolval($val))
							{
								unset($filteredArray[$index]);
								break 2;
							}

							//string compare
							if(mb_strtolower($item["elements"][$fieldName]) == mb_strtolower($val)){
								unset($filteredArray[$index]);
								break 2;
							}
						}
					}
					elseif (strtolower($op) === 'like')
					{
						$val = str_replace('%','',$val);

						if($fieldName === 'multiParkingLot') {
							$parkingLots = $itemRaw['elements'][$fieldName] ?? [];

							$hasValidParkingLot = array_filter($parkingLots, function ($lot) {
								return !in_array(null, $lot, true)
									&& !in_array(0, $lot, true)
									&& !in_array(0.0, $lot, true)
									&& !in_array('', $lot, true);
							});

							if (!$hasValidParkingLot) {
								unset($filteredArray[$index]);
								break 2;
							}
						} elseif(!array_key_exists($fieldName,$itemRaw["elements"]) || stripos($itemRaw["elements"][$fieldName], $val) === false){
							unset($filteredArray[$index]);
							break 2;
						}
					}
					elseif (strtolower($op) === 'in')
					{
						//Objekttyp verhält sich nicht wie erwartet
						$elVal = $itemRaw["elements"][$fieldName];
						if($fieldName === "Id") {
							$elVal = str_replace(',','',$elVal);
							$elVal = str_replace('.','',$elVal);
						}

						if(!is_array($val)) {
							$val = array($val);
						}
						$lowerVal = array_map('mb_strtolower', $val);

						//Needed for nested array (like fahrstuhl)
						if(is_array($elVal)) {
							if (empty($elVal) || count(array_intersect(array_map('strtolower', $elVal), $lowerVal)) === 0) {
								unset($filteredArray[$index]);
								break 2;
							}
						}elseif(!in_array(mb_strtolower($elVal ?? ''), $lowerVal)){
							unset($filteredArray[$index]);
							break 2;
						}
					}
					elseif ($op === '<=')
					{
						if(!array_key_exists($fieldName,$itemRaw["elements"])
							|| $this->isBigger($val, $itemRaw["elements"][$fieldName], $fieldTypes[$fieldName])) {
							unset($filteredArray[$index]);
							break 2;
						}
					}
					elseif ($op === '>=')
					{
						if(!array_key_exists($fieldName,$itemRaw["elements"])
							|| $this->isSmaller($val, $itemRaw["elements"][$fieldName], $fieldTypes[$fieldName])) {
							unset($filteredArray[$index]);
							break 2;
						}
					}
					elseif (strtolower($op) === 'geo')
					{
						$km = intval($val);
						$min = $value[0]["min"];
						$max = $value[0]["max"];

						$loc = $value[0]["loc"] ?? '';
						if(str_starts_with($loc, "0-")) {
							$loc = substr($loc, 2);
						}
						if($loc != '' && $min != null && intval($min) > 0)
							$isGeoAndMin = $min;
						if($loc != '' && $max != null && intval($max) > 0)
							$isGeoAndMax = $max;

						$selectedCoordinates = explode(",", $loc);
						if(!array_key_exists('laengengrad',$item["elements"]) || !array_key_exists('breitengrad',$item["elements"])
							|| !is_array($selectedCoordinates) || count($selectedCoordinates) != 2)
							continue;
						if($item["elements"]['laengengrad'] == null || $item["elements"]['breitengrad'] == null){
							unset($filteredArray[$index]);
							break 2;
						}
						$longitude = floatval($selectedCoordinates[0]);
						$latitude = floatval($selectedCoordinates[1]);

						$coordinate1 = new Coordinate($item["elements"]['laengengrad'], $item["elements"]['breitengrad']);
						$coordinate2 = new Coordinate($longitude, $latitude);
						$distance = $calculator->getDistance($coordinate1, $coordinate2);

						if(intval($distance/1000) > $km){
							unset($filteredArray[$index]);
							break 2;
						} else {
							$filteredArray[$index]["elements"]['geo_distance'] = intval($distance);
							$filteredArrayRaw[$k]["elements"]['geo_distance'] = intval($distance);
						}
					}
				}
			}
		}
		if($isGeoAndMin > 0 && count($filteredArray) < $isGeoAndMin)
		{
			$newFilter = $filter;
			$newFilter['geo'][0]["val"] = 1000; //km
			$newFilter['geo'][0]["min"] = 0;
			$cachedResponse["data"]["records"] = $filteredArray;
			$cachedResponse["raw"]["data"]["records"] = $filteredArrayRaw;
			$this->filterRecords($cachedResponse, $newFilter);
			$filteredArray = $this->sortRecords($cachedResponse, $newFilter, 'geo_distance', 'ASC');
			if($filteredArray > $isGeoAndMin)
				$filteredArray = array_slice($filteredArray, 0, $isGeoAndMin);
		}
		else if($isGeoAndMax > 0 && count($filteredArray) > $isGeoAndMax)
		{
			$cachedResponse["data"]["records"] = $filteredArray;
			$cachedResponse["raw"]["data"]["records"] = $filteredArrayRaw;
			$filteredArray = $this->sortRecords($cachedResponse, $filter, 'geo_distance', 'ASC');
			$filteredArray = array_slice($filteredArray, 0, $isGeoAndMax);
		}
		$cachedResponse["data"]["records"] = $filteredArray;
		$cachedResponse["raw"]["data"]["records"] = $filteredArrayRaw;
	}
	/**
	 * @param mixed $filterVal
	 * @param mixed $rawValue
	 */
	private function isBigger($filterVal, $rawValue, string $type): bool
	{
		return $this->isSmaller($filterVal, $rawValue, $type, false);
	}
	/**
	 * @param mixed $filterVal
	 * @param mixed $rawValue
	 */
	private function isSmaller($filterVal, $rawValue, string $type, bool $isSmaller = true): bool
	{
		if($type === 'float' || $type === 'integer') {
			if($isSmaller){
				return (floatval($rawValue) < floatval($filterVal));
			} else {
				return (floatval($rawValue) > floatval($filterVal));
			}
		} else if($type === 'date') {
			$dateVal = date_create_from_format('Y-m-d H:i:s', $filterVal); //1955-12-29
			$compare = date_create_from_format('Y-m-d', $rawValue);
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
				$requestParameters = $this->normalizeFieldDependencyParameters(
					$pResponse->getRequest()->getApiAction()->getActionParameters()
				);
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
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message is for internal debugging only
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
