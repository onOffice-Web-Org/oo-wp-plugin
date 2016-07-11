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

/**
 *
 */

class ArrayContainer implements \ArrayAccess, \Iterator {
	/** @var array */
	private $_subject = array();

	/**
	 *
	 * @param array $keyValues
	 *
	 */

	public function __construct( array $keyValues ) {
		$this->_subject = $keyValues;
	}


	/**
	 *
	 * Forced by ArrayAccess interface
	 *
	 * @param mixed $offset
	 * @return bool
	 *
	 */

	public function offsetExists( $offset ) {
		return isset( $this->_subject[$offset] );
	}


	/**
	 *
	 * Forced by ArrayAccess interface
	 *
	 * @param mixed $offset
	 * @return mixed
	 *
	 */

	public function offsetGet( $offset ) {
		return $this->getValue( $offset );
	}


	/**
	 *
	 * Forced by ArrayAccess interface
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 *
	 */

	public function offsetSet( $offset, $value ) {
		if ( is_null( $offset ) ) {
			$this->_subject[] = $value;
		} else {
			$this->_subject[$offset] = $value;
		}
	}


	/**
	 *
	 * Forced by ArrayAccess interface
	 *
	 * @param mixed $offset
	 *
	 */

	public function offsetUnset( $offset ) {
		unset( $this->_subject[$offset] );
	}


	/**
	 *
	 * @param mixed $key
	 * @return string
	 *
	 */

	public function getValue( $key ) {
		if ( isset( $this->_subject[$key] ) ) {
			return $this->_subject[$key];
		}

		return null;
	}


	/**
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 */

	public function getValueRaw( $key ) {
		if ( isset( $this->_subject[$key] ) ) {
			return $this->_subject[$key];
		}

		return null;
	}


	/**
	 * Forced by Iterator interface
	 */

	public function rewind() {
		reset($this->_subject);
	}


	/**
	 *
	 * Forced by Iterator interface
	 *
	 * @return type
	 *
	 */

	public function current() {
		return current($this->_subject);
	}


	/**
	 *
	 * Forced by Iterator interface
	 *
	 * @return mixed
	 *
	 */

	public function key() {
		return key($this->_subject);
	}


	/**
	 *
	 * Forced by Iterator interface
	 *
	 */

	public function next() {
		next($this->_subject);
	}


	/**
	 *
	 * Forced by Iterator interface
	 *
	 * @return bool
	 *
	 */

	public function valid() {
		return key($this->_subject) !== null;
	}
}
