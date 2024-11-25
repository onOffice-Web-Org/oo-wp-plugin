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
?>
<?php if ($pAddressList->getShowMapConfig()) { ?>
<div class="oo-address-map">
    <?php require('map/map.php'); ?>
</div>
<?php } ?>
<div class="oo-address-listframe oo-listframe">
	<?php
	/* @var $pAddressList AddressList */
	foreach ($pAddressList->getRows() as $addressId => $escapedValues) {
		$imageUrl = $escapedValues['imageUrl'];
		unset($escapedValues['imageUrl']);
	?>
		<div class="oo-listobject">
			<div class="oo-listobjectwrap">
				<?php
				if (!empty($imageUrl)) {
					$imageAlt = $pAddressList->generateImageAlt($addressId);
					echo '<img src="' . esc_url($imageUrl) . '" class="oo-address-image" alt="' . esc_html($imageAlt) . '" loading="lazy">';
				}
				?>
				<div class="oo-listinfo">
					<div class="oo-listinfotable oo-listinfotableview">
						<?php
						foreach ($escapedValues as $field => $value) {
							if ($pAddressList->getFieldType($field) === FieldTypes::FIELD_TYPE_BLOB) {
								continue;
							}

							if (empty($value)) {
								continue;
							}

							$fieldLabel = $pAddressList->getFieldLabel($field);
							echo '<div class="oo-listtd">' . esc_html($fieldLabel) . '</div><div class="oo-listtd">' . (is_array($value) ? implode(', ', array_filter($value)) : $value) . '</div>';
						}
						?>
					</div>
					<div class="oo-detailslink">
						<a class="oo-details-btn" href="<?php echo esc_url($pAddressList->getAddressLink($addressId)) ?>">
								<?php esc_html_e('Show Details', 'onoffice-for-wp-websites'); ?>
						</a>
					</div>
				</div>
			</div>
    </div>
	<?php } ?>
</div>
<div>
	<?php
	if (get_option('onoffice-pagination-paginationbyonoffice')) {
		wp_link_pages();
	}
	?>
</div>
