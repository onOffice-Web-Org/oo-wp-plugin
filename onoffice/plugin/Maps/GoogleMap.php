<?php

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2016, onOffice(R) Software AG
 *
 */

namespace onOffice\WPlugin\Maps;

use onOffice\WPlugin\Renderable;

/**
 *
 */

class GoogleMap implements Renderable {
	/** @var int */
	private $_zoom = null;

	/** @var int */
	private $_width = 400;

	/** @var int */
	private $_height = 300;


	/**
	 *
	 * https://developers.google.com/maps/tutorials/customizing/custom-markers
	 *
	 * @var GoogleMapMarker[]
	 *
	 */

	private $_markers = array();


	/**
	 *
	 * @param int $zoom
	 *
	 */

	public function setZoom( $zoom ) {
		$this->_zoom = $zoom;
	}


	/**
	 *
	 * @return array
	 *
	 */

	private function generateMapConfig() {
		$config = array(
			'zoom' => $this->_zoom,
		);

		return $config;
	}


	/**
	 *
	 * @param int $width
	 *
	 */

	public function setWidth( $width ) {
		$this->_width = $width;
	}


	/**
	 *
	 * @param int $height
	 *
	 */

	public function setHeight( $height ) {
		$this->_height = $height;
	}


	/**
	 *
	 * @param float $longitude
	 * @param float $latitude
	 * @param bool $visible
	 * @param string $title
	 * @param string $icon
	 *
	 */

	public function addNewMarker( $longitude, $latitude, $visible = true, $title = null, $icon = null ) {
		$pMarker = new GoogleMapMarker( $longitude, $latitude );
		$pMarker->setIconUrl( $icon );
		$pMarker->setTitle( $title );
		$pMarker->setVisible($visible);

		$this->_markers[] = $pMarker;
	}


	/**
	 *
	 * @param \onOffice\WPlugin\Maps\GoogleMapMarker $pMarker
	 *
	 */

	public function addMarker( GoogleMapMarker $pMarker ) {
		$this->_markers[] = $pMarker;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function getMapVariableName() {
		$instanceHash = spl_object_hash( $this );
		return 'map'.$instanceHash;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function renderHtml() {
		$output = '<div'
				.' id="'.$this->getMapVariableName().'"'
				.' style="width:'.$this->_width.'px;'
				.' height:'.$this->_height.'px"'
				.'></div>'."\n"
				.'<script type="application/javascript">'."\n"
					.$this->renderJs()."\n"
				.'</script>';
		return $output;
	}


	/**
	 *
	 * @return string
	 *
	 */

	public function render() {
		return $this->renderHtml();
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function renderJs() {
		$jsonConfig = array(
			'mapElementId' => $this->getMapVariableName(),
			'mapElement' => null,
			'mapConfig' => $this->generateMapConfig(),
			'mapInstance' => null,
			'bounds' => null,
		);

		$output = 'function '.$this->getMapVariableName().'() {'."\n"
				.'var settings = '.json_encode( $jsonConfig ).';'."\n"
				.'settings.mapElement = document.getElementById(settings.mapElementId);'."\n"
				.'settings.mapInstance = initGoogleMaps(settings.mapElement, settings.mapConfig);'."\n"
				.'settings.bounds = new google.maps.LatLngBounds();'."\n"
				.$this->renderMarkers()."\n"
				.'settings.mapInstance.fitBounds(settings.bounds);'
				.'var boundsListener = google.maps.event.addListener(settings.mapInstance, "bounds_changed", function(event) {
					if (settings.mapConfig.zoom !== null) {
						this.setZoom(settings.mapConfig.zoom);
					}
					google.maps.event.removeListener(boundsListener);
				});'
			.'}'."\n"
			.'google.maps.event.addDomListener(window, "load", '.$this->getMapVariableName().');';
		return $output;
	}


	/**
	 *
	 * @return string
	 *
	 */

	private function renderMarkers() {
		$output = '';

		foreach ( $this->_markers as $markerId => $pMarker ) {
			$markerVarName = 'marker'.$markerId;
			$config = $pMarker->getMarkerConfig();
			$output .= 'var config = getMarkerConfig('.json_encode( $config ).', settings.mapInstance);'."\n";
			$output .= 'settings.bounds.extend(config.position);'."\n";

			if ( false === $pMarker->getVisible() ) {
				continue;
			}

			$output .= 'var '.$markerVarName.' = new google.maps.Marker(config);'."\n";
		}

		return $output;
	}
}
