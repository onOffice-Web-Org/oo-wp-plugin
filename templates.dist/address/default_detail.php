<?php

use onOffice\WPlugin\AddressDetail;

foreach ($pAddressList->getRows() as $addressId => $escapedValues) {
	$imageUrl = $escapedValues['imageUrl'];
	unset($escapedValues['imageUrl']);
	foreach ($escapedValues as $field => $value) {
		$fieldLabel = $pAddressList->getFieldLabel($field);
		echo $fieldLabel, ': ', (is_array($value) ? implode(', ', array_filter($value)) : $value), '<br>';
	}

	if ($pAddressList->getPictureTypesOption(AddressDetail::SHOW_PICTURE_TYPE_USER_PHOTO)) {
		$userId = $pAddressList->getUserId();
		$userPhoto = $pAddressList->getUserPhoto($userId);
		echo esc_html_e('User photo', 'onoffice-for-wp-websites') . ': ' . $userPhoto . '<br>';
	}

	if ($pAddressList->getPictureTypesOption(AddressDetail::SHOW_PICTURE_TYPE_PASSPORT_PHOTO)) {
		echo esc_html_e('Passport photo', 'onoffice-for-wp-websites') . ': ' . $imageUrl . '<br>';
	}
}