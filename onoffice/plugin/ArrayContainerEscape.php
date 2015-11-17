<?php

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2015, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin;

use onOffice\WPlugin\Escape;

/**
 *
 */

class ArrayContainerEscape extends ArrayContainer {
	/** @var string */
	private $_escaping = null;


	/**
	 *
	 * @param array $keyValues
	 * @param string $escaping
	 *
	 */

	public function __construct( array $keyValues, $escaping = Escape::HTML ) {
		$this->_escaping = $escaping;
		parent::__construct( $keyValues );
	}


	/**
	 *
	 * @param mixed $offset
	 * @throws Exception
	 *
	 */

	public function offsetGet( $offset ) {
		$callback = Escape::getCallbackByEscaping( $this->_escaping );
		return call_user_func( $callback, parent::offsetGet( $offset ) );
	}


	/**
	 *
	 * @param mixed $key
	 * @param string $escaping
	 * @return string
	 *
	 */

	public function getValue( $key, $escaping = null ) {
		if (null === $escaping) {
			$escaping = $this->_escaping;
		}

		$callback = Escape::getCallbackByEscaping( $this->_escaping );
		return call_user_func( $callback, parent::getValue( $key ) );
	}
}
