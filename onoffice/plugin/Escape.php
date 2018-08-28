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

/**
 *
 */

class Escape {
	/** */
	const HTML = 'html';

	/** */
	const JS = 'js';

	/** */
	const URL = 'url';

	/** */
	const TEXTAREA = 'textarea';

	/** */
	const ATTR = 'attr';

	/** @var array */
	private static $_callbacks = array(
		self::HTML => 'esc_html',
		self::JS => 'esc_js',
		self::URL => 'esc_url',
		self::TEXTAREA => 'esc_textarea',
		self::ATTR => 'esc_attr',
	);


	/**
	 *
	 * @param string $escaping
	 * @return string
	 *
	 */

	public static function getCallbackByEscaping( $escaping ) {
		if ( ! isset( self::$_callbacks[$escaping] ) ) {
			throw new Exception('Escaping!');
		}

		return self::$_callbacks[$escaping];
	}
}
