<?php

if ( ! defined( 'ABSPATH' ) ) exit;

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
use onOffice\WPlugin\Pagination\ListPagination;

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
					$altText = $pAddressList->generateImageAlt($addressId);
					$imageAlt = !empty($altText) ? $altText : esc_html__('Contact person image', 'onoffice-for-wp-websites');					
					echo  '<img src="' . esc_url($imageUrl) . '" class="oo-address-image" alt="' . esc_attr($imageAlt) . '" loading="lazy">';
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
							echo '<div class="oo-listtd">' . esc_html($fieldLabel) . '</div><div class="oo-listtd">' . 
                                (is_array($value) ? esc_html(implode(', ', array_filter($value))) : esc_html($value)) . '</div>';
						}
						?>
					</div>
					<div class="oo-detailslink">
						<?php /* translators: %d: address ID number */ ?>
						<a class="oo-details-btn" href="<?php echo esc_url($pAddressList->getAddressLink($addressId)) ?>" aria-label="<?php echo sprintf(esc_attr__('Show Details for Address No. %d', 'onoffice-for-wp-websites'), (int)$addressId); ?>">
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
		?>
		<div class="oo-listpagination">
			<?php
		
			$ListPagination = new ListPagination([
				'class' => 'oo-post-nav-links',
				'type' => 'address',
				'anchor' => 'oo-listheadline',
			]);
			
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ListPagination::render() returns escaped HTML
			echo $ListPagination->render();
			?>
		</div>
	<?php
	}
	?>
</div>
