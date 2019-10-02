<?php

/**
 *
 *    Copyright (C) 2019  onOffice Software
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
 *  Map template for OSM
 *
 */

$estateData = [];

$virtualAddressSet = (bool)$currentEstate['virtualAddress'];
$position = [
	'lat' => (float) $currentEstate['breitengrad'],
	'lng' => (float) $currentEstate['laengengrad'],
];
$title = $currentEstate['objekttitel'];
$visible = !$virtualAddressSet;

if (.0 !== $position['lng'] && .0 !== $position['lat'] && $currentEstate['showGoogleMap']) {
	$estateData []= [
		'latlng' => $position,
		'options' => [
			'title' => $title,
		],
	'visible' => $visible,
	];
}
?>

<div id="map" style="width: 600px; height: 400px;"></div>

<script>
(function() {
	var estateMarkers = <?php echo json_encode($estateData); ?>;
	var map = L.map('map', {
		center: [50.8, 10.0],
		zoom: 5
	});
	L.tileLayer('https://tiles.onoffice.de/tiles/{z}/{x}/{y}.png').addTo(map);

	var group = new L.featureGroup();

	for (i in estateMarkers) {
		var estate = estateMarkers[i];
		var marker = L.marker(estate.latlng, estate.options);
		marker.bindPopup(estate.options.title);
		group.addLayer(marker);
		if (estate.visible) {
			marker.addTo(map);
		}
		// only extend map boundaries by new coordinates, if not visible
	}

	map.fitBounds(group.getBounds());
})();
</script>
