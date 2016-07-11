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
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin\Maps;

/**
 *
 */

class GoogleMapMarker {
	/** @var float */
	private $_longitude = null;

	/** @var float */
	private $_latitude = null;

	/** @var string */
	private $_iconUrl = null;

	/** @var string */
	private $_title = null;

	/** @var bool */
	private $_visible = true;


	/**
	 *
	 * @param float $longitude
	 * @param float $latitude
	 *
	 */

	public function __construct( $longitude, $latitude ) {
		$this->_latitude = $latitude;
		$this->_longitude = $longitude;
	}


	/**
	 *
	 * @return array
	 *
	 */

	public function getMarkerConfig() {
		$config = array(
			'lat' => $this->_latitude,
			'lng' => $this->_longitude,
			'icon' => $this->_iconUrl,
			'title' => $this->_title,
			'visible' => $this->_visible,
		);

		return $config;
	}


	/**
	 *
	 * @param string $iconUrl
	 *
	 */

	public function setIconUrl( $iconUrl ) {
		$this->_iconUrl = $iconUrl;
	}


	/**
	 *
	 * @param string $title
	 *
	 */

	public function setTitle( $title ) {
		$this->_title = $title;
	}


	/**
	 *
	 * @return float
	 *
	 */

	public function getLongitude() {
		return $this->_longitude;
	}


	/**
	 *
	 * @return float
	 *
	 */

	public function getLatitude() {
		return $this->_latitude;
	}


	/**
	 *
	 * @param bool $visible
	 *
	 */

	public function setVisible( $visible ) {
		$this->_visible = (bool) $visible;
	}


	/**
	 *
	 * @return bool
	 *
	 */

	public function getVisible() {
		return $this->_visible;
	}
}