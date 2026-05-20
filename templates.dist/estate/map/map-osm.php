<?php

if ( ! defined( 'ABSPATH' ) ) exit;

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

    while ($currentEstateMap = $pEstatesClone->estateIterator(EstateViewFieldModifierTypes::MODIFIER_TYPE_MAP)) {
        $estateId = $pEstatesClone->getCurrentEstateId();
        $rawValues = $pEstatesClone->getRawValues();
        $currentEstateRawValue = $rawValues->getValueRaw($estateId);
        $virtualAddressSet = (bool)($currentEstateRawValue['elements']['virtualAddress'] ?? false);
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

        $estateLink = esc_url($pEstatesClone->getEstateLink());
        $reference = filter_var($currentEstateRawValue['elements']['referenz'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $restrictedView = $pEstatesClone->getViewRestrict();
        if ( $reference && $restrictedView ) {
            $link = '';
        } else {
            $link = $estateLink;
        }

        $listViewId = $pEstatesClone->getListViewId();
        $mapId = 'oo_map_' . $listViewId;
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
                'showInfoWindow' => $showInfoWindow,
                'id' => $estateId
            ];
        }
    }

    if ($estateData === []) {
        return;
    }
    ?>
    <div class="oo-map" role="region" id="<?php echo esc_attr($mapId) ?>" style="width: 100%; height: 100%;" aria-label="<?php echo esc_html__(
    'Map with properties','onoffice-for-wp-websites'); ?>"></div>
    <script>
(function() {
    var estateMarkers = <?php echo json_encode($estateData); ?>;
    let mapId = <?php echo esc_js($mapId); ?>;

    var map = L.map(mapId, {
        a11yPlugin: true,
        center: [50.8, 10.0],
        zoom: 5
    });

    L.tileLayer('https://tiles.onoffice.de/tiles/{z}/{x}/{y}.png').addTo(map);

    var markers = L.markerClusterGroup();

    const translations = {
        ariaLabelTemplate: "<?php 
            /* translators: %s: real estate ID number */
            echo esc_js(esc_html_x('Show Details for Real Estate No. %s', 'template', 'onoffice-for-wp-websites')); ?>"
    };

    for (let i in estateMarkers) {
        var estate = estateMarkers[i];
        var marker = L.marker(estate.position, { title: estate.title });
        var id = estate.id;

        const ariaLabel = translations.ariaLabelTemplate.replace('%s', estate.id);

        if (!estate.showInfoWindow) {
            const popupContent = `
                <div class="oo-infowindow">
                    <p class="oo-infowindowtitle">${estate.title}</p>
                    ${estate.address ? `<p class="oo-infowindowaddress">${estate.address}</p>` : ''}
                    ${estate.link ? `<div class="oo-detailslink"><a class="oo-details-btn" href="${estate.link}" aria-label="${ariaLabel}"><?php echo esc_html__('Show Details', 'onoffice-for-wp-websites'); ?></a></div>` : ''}
                </div>
            `;

            var popup = L.popup({
                content: popupContent,
                minWidth: 250,
                maxWidth: 350,
            });
            marker.bindPopup(popup);
        }

        if (estate.visible) {
            markers.addLayer(marker);
        }
    }

    map.addLayer(markers);

    map.fitBounds(markers.getBounds());
})();
</script>
<?php
});