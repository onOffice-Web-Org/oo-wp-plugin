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

use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\Types\FieldTypes;

// display search form
require 'SearchFormAddress.php';

/**
 *
 *  Default template for address lists
 *
 */

/* @var $pAddressList AddressList */
foreach ($pAddressList->getRows() as $escapedValues) {
	$imageUrl = $escapedValues['imageUrl'];
	unset($escapedValues['imageUrl']);

	echo esc_html__('Picture: ', 'onoffice-for-wp-websites'), $imageUrl, '<br>';

	foreach ($escapedValues as $field => $value) {
		if ($pAddressList->getFieldType($field) === FieldTypes::FIELD_TYPE_BLOB) {
			continue;
		}
		$fieldLabel = $pAddressList->getFieldLabel($field);
		echo $fieldLabel, ': ', (is_array($value) ? implode(', ', array_filter($value)) : $value), '<br>';
	}
}