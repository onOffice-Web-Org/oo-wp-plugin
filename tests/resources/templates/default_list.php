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

use onOffice\WPlugin\EstateList;

/** @var $pEstates EstateList */
(function (EstateList $pEstates) {
	$pEstates->resetEstateIterator();
	while ($currentEstate = $pEstates->estateIterator()) {
		unset($currentEstate['vermarktungsstatus']);
		echo $pEstates->getEstateLink() . ': ' . esc_html('Show Details', 'onoffice-for-wp-websites')."\n";
		foreach ($currentEstate as $field => $value) {
			echo esc_html($pEstates->getFieldLabel($field)) . ': '
				. (is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value)) . "\n";
		}
		echo $pEstates->getEstateUnits();
		if ($pEstates->getDocument() !== '') {
			echo $pEstates->getDocument();
		}
	}
})($pEstates);
