<?php

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