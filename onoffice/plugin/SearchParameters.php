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
 * Singleton that holds submitted get parameters for the pagination
 *
 */

class SearchParameters {
	/** @var \onOffice\WPlugin\SearchParameters */
	private static $_pInstance = null;

	/** @var array */
	private $_parameters = array();

	/** @var array */
	private $_defaultLinkParams = array();

	/** @var array */
	private $_allowedGetParameters = array();

	/** @var bool */
	private $_filter = true;

	/** */
	private function __construct() {}

	/** */
	private function __clone() {}


	/**
	 *
	 * @return \onOffice\WPlugin\SearchParameters
	 *
	 */

	public static function getInstance() {
		if ( self::$_pInstance === null ) {
			self::$_pInstance = new static();
		}

		return self::$_pInstance;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getParameters() {
		$parameters = $this->_parameters;

		if ( $this->_filter ) {
			$parameters = array_filter($this->filterParameters( $parameters ));
		}

		return $parameters;
	}


	/**
	 *
	 * @param array $parameters
	 *
	 */

	public function setParameters( array $parameters ) {
		$this->_parameters = $parameters;
	}


	/**
	 *
	 * @param array $parameters
	 * @return array
	 *
	 */

	private function filterParameters( $parameters ) {
		$whitelist = array_merge( $this->_allowedGetParameters, array('oo_formid', 'oo_formno') );
		$whitelistKey = array_flip( $whitelist );

		return array_intersect_key( $parameters, $whitelistKey );
	}


	/**
	 *
	 * @param string $key
	 * @param string $value
	 *
	 */

	public function setParameter( $key, $value ) {
		$this->_parameters[$key] = $value;
	}


	/**
	 *
	 * Generates a pagelink for pagination with given parameters as GET Request
	 *
	 * partly taken from wp_link_pages() and _wp_link_page()
	 *
	 * @global int $page
	 * @global bool $more
	 * @param string $link
	 * @param int $i
	 * @return string
	 *
	 */

	public function linkPagesLink( $link, $i = 1 ) {
		global $page, $more;

		if (count($this->_parameters) === 0)
		{
			return '<a href="' . esc_url( $link ) . '">' . $link . '</a>';

		}

		$linkparams = $this->_defaultLinkParams;
		$output = '';

		if ( 'number' == $linkparams['next_or_number'] ) {
			$link = $linkparams['link_before'] . str_replace( '%', $i, $linkparams['pagelink'] )
				. $linkparams['link_after'];
			if ( $i != $page || ! $more && 1 == $page ) {
				$url = $this->geturl( $i );

				$output .= '<a href="' . esc_url( $url ) . '">' . $link . '</a>';
			} else {
				$output .= $link;
			}
		} elseif ( $more ) {
			$output .= $this->getLinkSnippetForPage( $i, $page );
		}

		return $output;
	}


	/**
	 *
	 * @param int $i
	 * @param int $page
	 * @return string
	 *
	 */

	private function getLinkSnippetForPage( $i, $page ) {
		$linkparams = $this->_defaultLinkParams;

		$key = $i < $page ? 'previouspagelink' : 'nextpagelink';

		return '<a href="'.  esc_url( $this->geturl( $i ) ) .'">'
			. $linkparams['link_before'] . $linkparams[$key]
			. $linkparams['link_after'].'</a>';
	}


	/**
	 *
	 * @param int $i
	 * @return string
	 *
	 */

	private function geturl ($i ) {
		$url = trailingslashit(get_permalink()) . user_trailingslashit($i, 'single_paged');
		return add_query_arg($this->getParameters(), $url);
	}


	/**
	 *
	 * @param array $params
	 * @return array
	 *
	 */

	public function populateDefaultLinkParams( $params ) {
		$this->_defaultLinkParams = $params;
		return $params;
	}


	/**
	 *
	 * @param array $parameters
	 *
	 */

	public function setAllowedGetParameters( array $parameters ) {
		$this->_allowedGetParameters = $parameters;
	}


	/**
	 *
	 * @param string $key
	 *
	 */

	public function addAllowedGetParameter( $key ) {
		$this->_allowedGetParameters []= $key;
	}


	/**
	 *
	 * @param bool $enable
	 *
	 */

	public function enableFilter($enable) {
		$this->_filter = (bool) $enable;
	}
}
