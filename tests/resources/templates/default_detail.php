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

$pClosure = function(EstateDetail $pEstates): \Generator {
	$pEstates->resetEstateIterator();
	$currentEstate = $pEstates->estateIterator();
    yield $pEstates->getEstateUnits();
    foreach ($currentEstate as $field => $value) {
        if (is_numeric($value) && 0 == $value) {
            continue;
        }
        yield esc_html($pEstates->getFieldLabel($field)) . ': '
            . (is_array($value) ? implode(', ', $value) : $value) . "\n";
    };

    foreach ($pEstates->getEstateContacts() as $contactData) {
        yield '* ' . esc_html__('Contact person', 'onoffice') . ': '
            . esc_html($contactData['Vorname'] . ' ' . $contactData['Name']) . "\n";
    }

    foreach ($pEstates->getEstateMovieLinks() as $movieLink) {
        yield '<a href="' . esc_attr($movieLink['url']) . '" title="' . esc_attr($movieLink['title']) . '">'
            . esc_html($movieLink['title']) . '</a>' . "\n";
    }

    foreach ($pEstates->getMovieEmbedPlayers(['width' => 500]) as $movieInfos) {
        yield esc_html($movieInfos['title']) . $movieInfos['player'];
    }

    foreach ($pEstates->getEstatePictures() as $id) {
        yield $pEstates->getEstatePictureTitle($id) . ': '
            . $pEstates->getEstatePictureUrl($id, ['width' => 300, 'height' => 400]) . "\n";
    }

    if ($pEstates->getDocument() != '') {
        yield esc_html_e('Documents', 'onoffice') . "\n"
            . $pEstates->getDocument() . "\n";
    }
    yield $pEstates->getSimilarEstates();
};

/* @var $pEstates EstateDetail */
echo implode('', iterator_to_array($pClosure($pEstates)));
unset($pClosure);
