<?php
use onOffice\WPlugin\Types\ImageTypes;
use onOffice\WPlugin\AddressList;

use function DI\value;
$dontEcho = array("objekttitel", "objektbeschreibung", "lage", "ausstatt_beschr", "sonstige_angaben", "MPAreaButlerUrlWithAddress", "MPAreaButlerUrlNoAddress");

/** @var AddressList $pAddressList */
foreach ($pAddressList->getRows() as $addressId => $escapedValues) {
	$pAddressList->getEstateAddressOwner($addressId);
	while ( $currentEstate = $pAddressList->estateAddressOwnerIterator() ) :
        ?>
        <div class="oo-detailstable">
				<?php
				foreach ($currentEstate as $field => $value) {
					if (is_numeric($value) && 0 == $value) {
						continue;
					}
					if (in_array($field, $dontEcho)) {
						continue;
					}
					if (empty($value)) {
						continue;
					}
					echo '<div class="oo-detailslisttd">' . esc_html($pAddressList->getFieldLabel($field)) . '</div>' . "\n"
						. '<div class="oo-detailslisttd">'
						. (is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value))
						. '</div>' . "\n";
				} ?>
			</div>
    <?php endwhile; ?>

<?
}

