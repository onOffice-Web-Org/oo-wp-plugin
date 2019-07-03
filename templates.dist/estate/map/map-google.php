<?php

/**
 *
 *    Copyright (C) 2018  onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 *
 *  Map template for Google Maps
 *
 */

use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;

/* @var $pEstates EstateList */
$pEstates->resetEstateIterator();
$estateData = array();

while ( $currentEstate = $pEstates->estateIterator( EstateViewFieldModifierTypes::MODIFIER_TYPE_MAP ) ) {
	$virtualAddressSet = (bool)$currentEstate['virtualAddress'];
	$position = array(
		'lat' => (float) $currentEstate['breitengrad'],
		'lng' => (float) $currentEstate['laengengrad'],
	);
	$title = $currentEstate['objekttitel'];
	$visible = ! $virtualAddressSet;

	if ( 0 == $position['lng'] || 0 == $position['lat'] || ! $currentEstate['showGoogleMap'] ) {
		continue;
	}

	$estateData []= array(
		'position' => $position,
		'title' => $title,
		'visible' => $visible,
	);
}
?>

<script type="text/javascript">
(function() {
	var gmapInit = function() {
		var estates = <?php echo json_encode($estateData, JSON_PRETTY_PRINT);?>;
		var settings = {
			zoom: null
		};

		var mapElement = document.getElementById('gmap');
		var map = new google.maps.Map(mapElement, settings.mapConfig);
		var bounds = new google.maps.LatLngBounds();

		map.fitBounds(bounds);
		map.addListener("bounds_changed", function() {
			if (settings.zoom !== null) {
				map.setZoom(settings.zoom);
			}
		});

		for (var i in estates) {
			var estateConfig = estates[i];
			var latLng = new google.maps.LatLng(estateConfig.position.lat, estateConfig.position.lng);
			bounds.extend(latLng);

			if (!estateConfig.visible) {
				// no marker but extended map bounds
				continue;
			}

			new google.maps.Marker({
				position: latLng,
				icon: null,
				map: map,
				title: estateConfig.title
			});
		}
	};

	google.maps.event.addDomListener(window, "load", gmapInit);
})();


</script>

<div id="gmap" style="width: 600px; height: 400px;"></div>