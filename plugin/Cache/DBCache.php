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

namespace onOffice\WPlugin\Cache;

use onOffice\SDK\Cache\onOfficeSDKCache;
use wpdb;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\SDKWrapper;

/**
 *
 */

class DBCache
	implements onOfficeSDKCache
{
	/** @var wpdb */
	private $_pWpdb = null;

	/** @var array */
	private $_options = array();

	/** @var bool */
	private $_cleanCache = false;

	/**
	 *
	 * @param array $options
	 * @global wpdb $wpdb
	 *
	 */

	public function __construct(array $options)
	{
		global $wpdb;
		$this->_pWpdb = $wpdb;
		$this->_options = $options;
	}


	/**
	 *
	 * @return int UNIX Timestamp
	 *
	 */

	private function getCacheMaxAge()
	{
		$onofficeSettingsCache = get_option('onoffice-settings-duration-cache');
		$interval = !empty($onofficeSettingsCache) && !$this->_cleanCache ? wp_get_schedules()[$onofficeSettingsCache]["interval"] : $this->_options['ttl'];
		return time() - $interval;
	}


	/**
	 *
	 * @param array $parameters
	 * @return string
	 *
	 */

	private function getParametersHashed( array $parameters )
	{
		$parametersSerialized = $this->getParametersSerialized( $parameters );
		$parametersHashed = md5( $parametersSerialized );
		return $parametersHashed;
	}


	/**
	 *
	 * @param array $parameters
	 * @return string
	 *
	 */

	private function getParametersSerialized( array $parameters )
	{
		ksort( $parameters );
		$parametersSerialized = serialize( $parameters );
		return $parametersSerialized;
	}


	/**
	 *
	 * @param array $parameters
	 * @return string
	 *
	 */

	public function getHttpResponseByParameterArray( array $parameters )
	{
		if (get_option('cron-job-running-process') == 1) {
			return null;
		}
		$parametersHashed = $this->getParametersHashed( $parameters );
		$cacheMaxAge = $this->getCacheMaxAge();

		$record = $this->_pWpdb->get_var( $this->_pWpdb->prepare( "
				SELECT cache_response
				FROM {$this->_pWpdb->prefix}oo_plugin_cache
				WHERE cache_parameters_hashed = %s AND UNIX_TIMESTAMP(cache_created) > %d
				", $parametersHashed, $cacheMaxAge )
		);

		return $record;
	}

	/**
	 *
	 * @param array $parameters
	 * @param string $value
	 * @return bool
	 */
	public function write( array $parameters, $value )
	{
		$parametersHashed = $this->getParametersHashed( $parameters );
		$parametersSerialized = $this->getParametersSerialized( $parameters );

		return $this->_pWpdb->replace(
			"{$this->_pWpdb->prefix}oo_plugin_cache", [
				'cache_parameters' => $parametersSerialized,
				'cache_parameters_hashed' => $parametersHashed,
				'cache_response' => $value,
			], ['%s', '%s', '%s']) > 0;
	}


	/**
	 *
	 */

	public function cleanup()
	{
		$cacheMaxAge = $this->getCacheMaxAge();
		$this->_pWpdb->query( $this->_pWpdb->prepare( "
				DELETE
				FROM {$this->_pWpdb->prefix}oo_plugin_cache
				WHERE UNIX_TIMESTAMP(cache_created) < %d
				", $cacheMaxAge )
		);
	}


	/**
	 *
	 */

	public function clearAll()
	{
		$oldTtl = $this->_options['ttl'];
		$this->_options['ttl'] = 0;
		$this->_cleanCache = true;
		$this->cleanup();
		$this->_options['ttl'] = $oldTtl;
	}

	/**
	 * @param SDKWrapper $pSDKWrapper
	 * @return void
	 */

	public function updateResponseColumnWithLatestData(SDKWrapper $pSDKWrapper)
	{
		$cachedRecords = $this->getCacheRecords();
		update_option('cron-job-running-process', 0);

		if (empty($cachedRecords)) {
			return;
		}

		$result = [];
		$pAPIClientAction = null;

		foreach ($cachedRecords as $record) {
			$parameters = unserialize($record['cache_parameters']);
			$pAPIClientAction = new APIClientActionGeneric
				($pSDKWrapper, $parameters['actionid'], $parameters['resourcetype']);
			$pAPIClientAction->setResourceId($parameters['resourceid']);
			$pAPIClientAction->setParameters($parameters['parameters']);
			$pAPIClientAction->addRequestToQueue();
			$result[$record['cache_parameters_hashed']] = $pAPIClientAction;
		}

		$responseData = [];
		if (!empty($result)) {
			$pAPIClientAction->sendRequests();
			foreach ($result as $key => $record) {
				if (empty($record->getResultResponseData())) {
					continue;
				}
				$responseData[$key] = $record->getResultResponseData();
			}
		}

		if (!empty($responseData)) {
			$this->updateDataResponsesColumn($responseData);
		}
	}

	/**
	 * @param array $responseData
	 * @return void
	 */
	private function updateDataResponsesColumn(array $responseData)
	{
		$conditions = [];
		$parametersHashedValues = [];
		foreach ($responseData as $parametersHashed => $response) {
			$serializedResponse = $this->getParametersSerialized($response);
			$conditions[] = "WHEN cache_parameters_hashed = %s THEN %s";
			$parametersHashedValues[] = $parametersHashed;
			$parametersHashedValues[] = $serializedResponse;
		}

		$conditionsSql = implode(' ', $conditions);
		$placeholders = implode(',', array_fill(0, count($responseData), '%s'));
		$sql = "UPDATE {$this->_pWpdb->prefix}oo_plugin_cache SET cache_response = CASE {$conditionsSql} END WHERE cache_parameters_hashed IN ({$placeholders})";
		$preparedSql = $this->_pWpdb->prepare($sql, array_merge($parametersHashedValues, array_keys($responseData)));

		$this->_pWpdb->query($preparedSql);
	}

	/**
	 * @return array
	 */

	private function getCacheRecords(): array
	{
		$cacheMaxAge = $this->getCacheMaxAge();
		$records = $this->_pWpdb->get_results( $this->_pWpdb->prepare("
				SELECT *
				FROM {$this->_pWpdb->prefix}oo_plugin_cache
				WHERE UNIX_TIMESTAMP(cache_created) < %d
				", $cacheMaxAge), ARRAY_A );
	
		return $records;
	}
}
