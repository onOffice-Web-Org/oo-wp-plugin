<?php

/**
 *
 *    Copyright (C) 2020 onOffice Software
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

use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\Types\MapProvider;

/** @var EstateList $pEstates */
(function (MapProvider $pMapProvider, EstateList $pEstates) {
	$pCallback = null;
	switch ($pMapProvider->getActiveMapProvider()) {
		case MapProvider::GOOGLE_MAPS:
			$pCallback = require 'map-google.php';
			break;

		case MapProvider::OPEN_STREET_MAPS:
			$pCallback = require 'map-osm.php';
			break;
	}

	if ($pCallback !== null) {
		$pCallback(clone $pEstates);
	}
})(new MapProvider(), $pEstates);