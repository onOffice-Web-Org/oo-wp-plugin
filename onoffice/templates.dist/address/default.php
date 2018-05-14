<?php

/**
 *
 *    Copyright (C) 2018  onOffice GmbH
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
 *
 *  Default template for address lists
 *
 */

/* @var $pAddressList onOffice\WPlugin\AddressList */
foreach ($pAddressList->getRows() as $escapedValues) {
	$imageUrl = $escapedValues['imageUrl'];
	unset($escapedValues['imageUrl']);

	echo 'Bild: ', $imageUrl, '<br>';

	foreach ($escapedValues as $field => $value) {
		$fieldLabel = $pAddressList->getFieldLabel($field);
		echo $fieldLabel, ': ', $value, '<br>';
	}
}