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
 *  Map template for Google Maps
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
				'position' => $position,
				'title' => $title,
				'visible' => true,
			];
		}
	}


	if ($addressData === []) {
		return;
	}
	$listViewId = $pAddressClone->getListViewId();
	$mapId = 'oo_gmap_' . $listViewId;
	?>
    <script type="text/javascript">
        (function () {
            var gmapInit = function () {
                var addresses = <?php echo json_encode($addressData, JSON_PRETTY_PRINT); ?>;
                var settings = {zoom: null};

                var mapElement = document.getElementById('<?php echo esc_js($mapId); ?>');
                var map = new google.maps.Map(mapElement, settings.mapConfig);
                var bounds = new google.maps.LatLngBounds();

                map.fitBounds(bounds);
                map.addListener("bounds_changed", function () {
                    if (settings.zoom !== null) {
                        map.setZoom(settings.zoom);
                    }
                });

                for (var i in addresses) {
                    var addressConfig = addresses[i];
                    var latLng = new google.maps.LatLng(addressConfig.position.lat, addressConfig.position.lng);
                    bounds.extend(latLng);

                    if (addressConfig.visible) {
                        // no marker but extended map bounds
                        new google.maps.Marker({
                            position: latLng,
                            icon: null,
                            map: map,
                            title: addressConfig.title
                        });
                    }
                }
            };

            google.maps.event.addDomListener(window, "load", gmapInit);
        })();
    </script>
    <div class="oo-gmap" id="<?php echo esc_attr($mapId) ?>" style="width: 100%; height: 100%;"></div>
	<?php
});