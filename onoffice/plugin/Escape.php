<?php

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
