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

use onOffice\WPlugin\AddressDetail;
use onOffice\WPlugin\ViewFieldModifier\AddressViewFieldModifierTypes;


?>

<div class="oo-detailview">
	<?php
	$currentAddressArr = $pAddressList->getCurrentAddress();
	foreach ($currentAddressArr as $addressId => $escapedValues) {
	?>
	<div class="oo-detailsheadline">
		<h1><?php echo $escapedValues['Name']; ?></h1>
		<div class="oo-detailstable">
			<?php
			foreach ($escapedValues as $field => $value) {
				if($field === 'ind_2124_Feld_adressen4') {
					echo '<img width="350" src="'.$value.'"/>';
				} else {
					echo '<div class="oo-detailslisttd">' . esc_html($pAddressList->getFieldLabel($field)) . '</div>' . "\n"
						. '<div class="oo-detailslisttd">'
						. (is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value))
						. '</div>' . "\n";
				}
			}?>
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
		$shortCodeReferenceEstates = $pAddressList->getShortCodeReferenceEstates();
		if (!empty($shortCodeReferenceEstates)) {
		?>
			<div class="detail-contact-form">
				<?php echo do_shortcode($shortCodeReferenceEstates); ?>
			</div>
	<?php } ?>
	<?php
		$shortCodeActiveEstates = $pAddressList->getShortCodeActiveEstates();
		if (!empty($shortCodeActiveEstates)) {
		?>
			<div class="detail-contact-form">
				<?php echo do_shortcode($shortCodeActiveEstates); ?>
			</div>
	<?php } ?>
	<?php
		$shortCodeForm = $pAddressList->getShortCodeForm();
		if (!empty($shortCodeForm)) {
		?>
			<div class="detail-contact-form">
				<?php echo do_shortcode($shortCodeForm); ?>
			</div>
		<?php } ?>
</div>
