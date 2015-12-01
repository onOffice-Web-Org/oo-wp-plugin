<?php

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
}
