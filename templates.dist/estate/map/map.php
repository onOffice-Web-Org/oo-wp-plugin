<?php

/**
 *
 *    Copyright (C) 2019  onOffice Software
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

use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Types\MapProvider;

/**
 *
 *  Map template
 *
 */

$pMapProvider = new MapProvider();

switch ($pMapProvider->getActiveMapProvider()) {
	case MapProvider::GOOGLE_MAPS:
		if (get_class($pEstates) == EstateDetail::class)
		{
			include 'map-google-detail.php';
		}
		else
		{
			include 'map-google.php';
		}
		break;

	case MapProvider::OPEN_STREET_MAPS:
		if (get_class($pEstates) == EstateDetail::class)
		{
			include 'map-osm-detail.php';
		}
		else
		{
			include('map-osm.php');
		}
		break;
}