<?php

/**
 *
 *    Copyright (C) 2020  onOffice GmbH
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

declare(strict_types=1);

namespace onOffice\tests\resources\templates;

use onOffice\WPlugin\EstateDetail;

/* @var $pEstates EstateDetail */
(function(EstateDetail $pEstates) {
	$pEstates->resetEstateIterator();
	$currentEstate = $pEstates->estateIterator();
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- getEstateUnits() returns escaped HTML
	echo $pEstates->getEstateUnits();
	foreach ($currentEstate as $field => $value) {
		if (is_numeric($value) && 0 == $value) {
			continue;
		}
		echo esc_html($pEstates->getFieldLabel($field)) . ': '
            . (is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value)) . "\n";
	};

	foreach ($pEstates->getEstateContacts() as $contactData) {
		echo '* ' . esc_html__('Contact person image', 'onoffice-for-wp-websites') . ': '
			. esc_html($contactData['Vorname'] . ' ' . $contactData['Name']) . "\n";
	}

	foreach ($pEstates->getEstateMovieLinks() as $movieLink) {
		echo '<a href="' . esc_attr($movieLink['url']) . '" title="' . esc_attr($movieLink['title']) . '">'
			. esc_html($movieLink['title']) . '</a>' . "\n";
	}

	foreach ($pEstates->getMovieEmbedPlayers(['width' => 500]) as $movieInfos) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- player HTML is escaped by WordPress embed
        echo esc_html($movieInfos['title']) . $movieInfos['player'];
    }

	foreach ($pEstates->getEstatePictures() as $id) {
        echo esc_html($pEstates->getEstatePictureTitle($id)) . ': '
            . esc_url($pEstates->getEstatePictureUrl($id, ['width' => 300, 'height' => 400])) . "\n";
    }

	if ($pEstates->getDocument() != '') {
        echo esc_html_e('Documents', 'onoffice-for-wp-websites') . "\n"
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- getDocument() returns esc_url()
            . $pEstates->getDocument() . "\n";
    }
	 // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- getSimilarEstates() returns escaped HTML
    echo $pEstates->getSimilarEstates();
})($pEstates);

include ONOFFICE_PLUGIN_DIR . '/templates.dist/estate/map/map.php';
