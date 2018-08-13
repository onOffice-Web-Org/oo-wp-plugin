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

use Exception;
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
	 * @return mixed
	 *
	 */

	public function current() {
		$callback = Escape::getCallbackByEscaping( $this->_escaping );
		$value = parent::current();
		if (is_array($value)) {
			return array_map($callback, $value);
		}

		return call_user_func( $callback, $value);
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

		$callback = Escape::getCallbackByEscaping( $escaping );
		return call_user_func( $callback, parent::getValue( $key ) );
	}
}
