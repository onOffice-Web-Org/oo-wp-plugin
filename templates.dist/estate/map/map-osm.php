<?php

/**
 *
 *    Copyright (C) 2018-2020 onOffice GmbH
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
 *  Map template for OSM
 */
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;

/* @var $pEstates EstateList */
return (function(EstateList $pEstatesClone) {
	$pEstatesClone->resetEstateIterator();
	$estateData = [];

	while ($currentEstateMap = $pEstatesClone->estateIterator
		(EstateViewFieldModifierTypes::MODIFIER_TYPE_MAP)) {
		$virtualAddressSet = (bool)$currentEstateMap['virtualAddress'];
		$position = [
			'lat' => (float)$currentEstateMap['breitengrad'],
			'lng' => (float)$currentEstateMap['laengengrad'],
		];
		$title = $currentEstateMap['objekttitel'];
		$visible = !$virtualAddressSet;

		if (.0 !== $position['lng'] && .0 !== $position['lat'] && $currentEstateMap['showGoogleMap']) {
			$estateData[] = [
				'latlng' => $position,
				'options' => ['title' => $title],
				'visible' => $visible,
			];
		}
	}

	if ($estateData === []) {
		return;
	} ?>
    <div id="map" style="width: 100%; height: 100%;"></div>
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
        }
        map.fitBounds(group.getBounds());
    })();
    </script>
<?php
});