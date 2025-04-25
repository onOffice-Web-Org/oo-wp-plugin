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

    while ($currentEstateMap = $pEstatesClone->estateIterator(EstateViewFieldModifierTypes::MODIFIER_TYPE_MAP)) {
        $rawValues = $pEstatesClone->getRawValues();
        $virtualAddressSet = (bool)$rawValues->getValueRaw($estateId)['elements']['virtualAddress'];
        $visible = !$virtualAddressSet;

        $position = [
            'lat' => (float)$currentEstateMap['breitengrad'],
            'lng' => (float)$currentEstateMap['laengengrad'],
        ];
        $originalTitle = $currentEstateMap['objekttitel'];
        $title = mb_strlen($originalTitle) > 60 ? preg_replace('/\s+\S*[\s\pZ\pC]*$/u', '', mb_substr($originalTitle, 0, 60)) . '...' : $originalTitle;
        $street = $currentEstateMap['strasse'];
        $number = $currentEstateMap['hausnummer'];
        $zip = $currentEstateMap['plz'];
        $city = $currentEstateMap['ort'];
        $country = $currentEstateMap['land'];

        $addressParts = [];
        if (!empty($street) || !empty($number)) {
            $addressParts[] = trim($street . ' ' . $number);
        }
        if (!empty($zip) || !empty($city)) {
            $addressParts[] = trim($zip . ' ' . $city);
        }
        if (!empty($country)) {
            $addressParts[] = $country;
        }
        $address = implode('<br>', $addressParts);

        $estateId = $pEstatesClone->getCurrentEstateId();
        $estateLink = esc_url($pEstatesClone->getEstateLink());
        $reference = filter_var($rawValues->getValueRaw($estateId)['elements']['referenz'], FILTER_VALIDATE_BOOLEAN);
        $restrictedView = $pEstatesClone->getViewRestrict();
        if ( $reference && $restrictedView ) {
            $link = '';
        } else {
            $link = $estateLink;
        }

        $listViewId = $pEstatesClone->getListViewId();
        $mapId = 'oo_gmap_' . $listViewId;
        $showInfoWindow = false;
        if ($listViewId === 'estate_detail') {
            $showInfoWindow = true;
        }

        if (.0 !== $position['lng'] && .0 !== $position['lat'] && $currentEstateMap['showGoogleMap']) {
            $estateData[] = [
                'position' => $position,
                'title' => $title,
                'address' => $address,
                'link' => $link,
                'visible' => $visible,
                'showInfoWindow' => $showInfoWindow 
            ];
        }
    }

    if ($estateData === []) {
        return;
    }
    ?>
    <script type="text/javascript">
    (function() {
        var gmapInit = function() {
            var estates = <?php echo json_encode($estateData, JSON_PRETTY_PRINT); ?>;
            var settings = {zoom: null};

            var mapElement = document.getElementById('<?php echo esc_js($mapId); ?>');
            var map = new google.maps.Map(mapElement, settings.mapConfig);
            var bounds = new google.maps.LatLngBounds();

            map.fitBounds(bounds);
            map.addListener("bounds_changed", function() {
                if (settings.zoom !== null) {
                    map.setZoom(settings.zoom);
                }
            });

            const infowindow = new google.maps.InfoWindow();

            for (var i in estates) {
                var estate = estates[i];
                var latLng = new google.maps.LatLng(estate.position.lat, estate.position.lng);
                bounds.extend(latLng);
                if (estate.visible) {
                    // no marker but extended map bounds
                    const marker = new google.maps.Marker({
                        position: latLng,
                        icon: null,
                        map: map,
                        title: estate.title
                    });

                    if (!estate.showInfoWindow) {
                        const infoWindowHeader = document.createElement('p');
                        infoWindowHeader.className = 'oo-infowindowtitle';
                        infoWindowHeader.innerHTML = estate.title;

                        const infoWindowContent = `
                            <div class="oo-infowindow">
                                ${estate.address ? `<p class="oo-infowindowaddress">${estate.address}</p>` : ''}
                                ${estate.link ? `<div class="oo-detailslink"><a class="oo-details-btn" href="${estate.link}"><?php echo esc_html__('Show Details', 'onoffice-for-wp-websites'); ?></a></div>` : ''}
                            </div>
                        `;
                        marker.addListener('click', () => {
                            infowindow.setOptions({
                                ariaLabel: estate.title,
                                headerContent: infoWindowHeader,
                                content: infoWindowContent,
                                minWidth: 250,
                                maxWidth: 350,
                            });
                            infowindow.open(map, marker);
                        });
                    }
                }
            }
        };

        google.maps.event.addDomListener(window, "load", gmapInit);
    })();
    </script>
    <div class="oo-gmap" id="<?php echo esc_attr($mapId) ?>" style="width: 100%; height: 100%;"></div>
<?php
});