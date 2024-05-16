<?php
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\AddressList;

/** @var AddressList $pAddressList */
foreach ($pAddressList->getRows() as $addressId => $escapedValues) {
	$imageUrl = $escapedValues['imageUrl'];
	unset($escapedValues['imageUrl']);
	foreach ($escapedValues as $field => $value) {
		$fieldLabel = $pAddressList->getFieldLabel($field);
		echo $fieldLabel, ': ', (is_array($value) ? implode(', ', array_filter($value)) : $value), '<br>';
	}

	if ($pAddressList->isValidPictureType(ImageTypes::USERPHOTO)) {
		$userId = $pAddressList->getUserId();
		$userPhoto = $pAddressList->getUserPhoto();
		echo esc_html_e('User photo', 'onoffice-for-wp-websites') . ': ' . $userPhoto . '<br>';
	}

	if ($pAddressList->isValidPictureType(ImageTypes::PASSPORTPHOTO)) {
		echo esc_html_e('Passport photo', 'onoffice-for-wp-websites') . ': ' . $imageUrl . '<br>';
	}
}