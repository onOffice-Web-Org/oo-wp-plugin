<?php

/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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

use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypes;

return (function (AddressList $pAddressClone) {
	$pAddressClone->resetAddressesIterator();
	$addressData = [];
	foreach ($pAddressClone->getRows(AddressViewFieldModifierTypes::MODIFIER_TYPE_MAP) as $escapedValues) {
		$position = [
			'lat' => (float) $escapedValues['breitengrad'],
			'lng' => (float) $escapedValues['laengengrad'],
		];

		$title = AddressList::createAddressTitle($escapedValues['Vorname'], $escapedValues['Name'], $escapedValues['Zusatz1']);
		if (.0 !== $position['lng'] && .0 !== $position['lat']) {
			$addressData[] = [
				'latlng' => $position,
				'options' => ['title' => $title],
				'visible' => true,
			];
		}
	}

	if ($addressData === []) {
		return;
	}
	$listViewId = $pAddressClone->getListViewId();
	$mapId = 'oo_map_' . $listViewId;
    ?>
    <div class="oo-map" id="<?php echo esc_attr($mapId) ?>" style="width: 100%; height: 100%;"></div>
    <script>
        (function () {
            var addressMarkers = <?php echo json_encode($addressData); ?>;
            let mapId = <?php echo esc_js($mapId); ?>;
            var map = L.map(mapId, {
                center: [50.8, 10.0],
                zoom: 5
            });
            L.tileLayer('https://tiles.onoffice.de/tiles/{z}/{x}/{y}.png').addTo(map);

            var group = new L.featureGroup();

            for (i in addressMarkers) {
                var address = addressMarkers[i];
                var marker = L.marker(address.latlng, address.options);
                marker.bindPopup(address.options.title);
                group.addLayer(marker);
                if (address.visible) {
                    marker.addTo(map);
                }
            }
            map.fitBounds(group.getBounds());
        })();
    </script>
    <?php
});