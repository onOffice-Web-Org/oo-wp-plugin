<?php

/**
 *
 *    Copyright (C) 2024  onOffice GmbH
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

$addressName = array('Anrede', 'Titel', 'Vorname', 'Name');
$addressPlace = array('Strasse', 'Plz', 'Ort', 'Plz-Ort');
/* @var $pAddressList AddressList */
$currentAddressArr = $pAddressList->getRows();
foreach ($currentAddressArr as $addressId => $escapedValues) {
		$imageUrl = $escapedValues['imageUrl'];
		unset($escapedValues['imageUrl']);
	?>
<div class="oo-address">
    <h2 class="oo-address-name">
        <?php
        $fullName = '';
        foreach ($addressName as $namePart) {
            $fullName .= !empty($escapedValues[$namePart]) ? $escapedValues[$namePart]. ' ' : '';
        }
        echo substr_replace($fullName, '', -1);
        ?>
    </h2>
    <?php
        if (!empty($imageUrl)) {
            $imageAlt = $pAddressList->generateImageAlt($addressId);
            echo '<picture class="oo-picture oo-address-picture">';
            echo '<img class="oo-responsive-image" ' .
                'src="' . esc_url($imageUrl) . '" ' .
                'alt="' . esc_html($imageAlt) . '" ' .
                'loading="lazy"/>';
            echo '</picture>';
        }
    ?>
    <div class="oo-address-fieldlist">
        <?php
            $addressPlaceDiv = '';
            foreach ($escapedValues as $field => $value) {
                if (in_array($field, $addressName) || empty($value)) {
                    continue;
                }
                if (in_array($field, $addressPlace)) {
                    $addressPlaceDiv .= '<div class="oo-address-placefield">'
                        . (is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value))
                        . '</div>';
                    continue;
                }
                echo '<div class="oo-address-field">'
                    . (is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value))
                    . '</div>';
            }
            if(!empty($addressPlaceDiv)) {
                echo '<div class="oo-address-placefieldlist">'.$addressPlaceDiv.'</div>';
            }
            ?>
    </div>
</div>
<?php } ?>
<!--
    to filter estates on address-detail-page you have to change the estate-list-template
    add this in while:
    if( !$pEstatesClone->isCurrentEstateContactsInAddressFilter() )
        continue;
-->
<?php
    $shortCodeActiveEstates = $pAddressList->getShortCodeActiveEstates();
    if (!empty($shortCodeActiveEstates)) {
    ?>
        <div class="oo-address-estatelist">
            <?php echo do_shortcode($shortCodeActiveEstates); ?>
        </div>
<?php } ?>
<?php
$shortCodeReferenceEstates = $pAddressList->getShortCodeReferenceEstates();
if (!empty($shortCodeReferenceEstates)) {
    ?>
    <div class="oo-address-estatelist">
        <?php echo do_shortcode($shortCodeReferenceEstates); ?>
    </div>
<?php } ?>
<?php
    $shortCodeForm = $pAddressList->getShortCodeForm();
    if (!empty($shortCodeForm)) {
    ?>
        <div class="oo-address-form">
            <?php echo do_shortcode($shortCodeForm); ?>
        </div>
<?php } ?>
