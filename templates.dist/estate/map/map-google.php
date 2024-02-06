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
 *  Map template for Google Maps
 */
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\ViewFieldModifier\EstateViewFieldModifierTypes;

/* @var $pEstates EstateList */
return (function (EstateList $pEstatesClone) {
	$pEstatesClone->resetEstateIterator();
	$estateData = [];

	while ($currentEstateMap = $pEstatesClone->estateIterator
        (EstateViewFieldModifierTypes::MODIFIER_TYPE_MAP)) {
		$virtualAddressSet = (bool)$currentEstateMap['virtualAddress'];
		$position = [
			'lat' => (float)$currentEstateMap['breitengrad'],
			'lng' => (float)$currentEstateMap['laengengrad'],
		];
		$title = $currentEstateMap ['objekttitel'];
		$visible = !$virtualAddressSet;

		if (.0 !== $position['lng'] && .0 !== $position['lat'] && $currentEstateMap['showGoogleMap']) {
            $estateData[] = [
                'position' => $position,
                'title' => $title,
                'visible' => $visible,
            ];
		}
	}

	if ($estateData === []) {
	    return;
    } ?>

    <div id="gmap"></div>
    <script type="text/javascript">
        async function gmapInit() {
            var estates = <?php echo json_encode($estateData, JSON_PRETTY_PRINT); ?>;
            var settings = {zoom: null};

            var mapElement = document.getElementById('gmap');
            const { Map } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
            const defaultPosition = estates.length > 0 ? { lat: estates[0].position.lat, lng: estates[0].position.lng } : { lat: 37.42, lng: -122.1 };
            const map = new Map(mapElement, {
                center: defaultPosition,
                zoom: 14,
                mapId:  <?php echo json_encode(get_option('onoffice-settings-googlemaps-id'))?> ?? ''
            });
            for (var i in estates) {
                var estateConfig = estates[i];
                const position = { lat: estateConfig.position.lat, lng: estateConfig.position.lng };
                const marker = new AdvancedMarkerElement({
                    map: map,
                    position,
                    title: estateConfig.title,
                });
            }
        };

        gmapInit();
    </script>
<?php
});