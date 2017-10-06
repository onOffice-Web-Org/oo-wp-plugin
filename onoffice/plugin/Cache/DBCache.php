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

/**
 *
 */

class DBCache implements onOfficeSDKCache {
	/** @var wpdb */
	private $_pWpdb = null;

	/** @var array */
	private $_options = array();


	/**
	 *
	 * @param array $options
	 * @global \wpdb $wpdb
	 *
	 */

	public function __construct(array $options) {
		global $wpdb;
		$this->_pWpdb = $wpdb;
		$this->_options = $options;
	}


	/**
	 *
	 * @return int UNIX Timestamp
	 *
	 */

	private function getCacheMaxAge() {
		return time() - $this->_options['ttl'];
	}


	/**
	 *
	 * @param array $parameters
	 * @return string
	 *
	 */

	private function getParametersHashed( array $parameters ) {
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

	private function getParametersSerialized( array $parameters ) {
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

	public function getHttpResponseByParameterArray( array $parameters ) {
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
	 *
	 */

	public function write( array $parameters, $value ) {
		$parametersHashed = $this->getParametersHashed( $parameters );
		$parametersSerialized = $this->getParametersSerialized( $parameters );

		$this->_pWpdb->replace(
			"{$this->_pWpdb->prefix}oo_plugin_cache",
			array(
				'cache_parameters' => $parametersSerialized,
				'cache_parameters_hashed' => $parametersHashed,
				'cache_response' => $value,
			),
			array(
				'%s',
				'%s',
				'%s',
			)
		);
	}


	/**
	 *
	 */

	public function cleanup() {
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
		$this->cleanup();
		$this->_options['ttl'] = $oldTtl;
	}
}
