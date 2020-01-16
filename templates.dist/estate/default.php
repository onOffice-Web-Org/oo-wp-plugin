<?php
/**
 *
 *    Copyright (C) 2016  onOffice Software AG
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
 *  Default template
 *
 */
use onOffice\WPlugin\Favorites;
// display search form
require 'SearchForm.php';
/* @var $pEstates onOffice\WPlugin\EstateList */
?>
<div class="oo-estate-map">
	<?php // require('map/map.php'); ?>
</div>
<div class="oo-listheadline">
	<h1><?php esc_html_e('Overview of Estates', 'onoffice'); ?></h1>
	<p>
		
		<?php /* translators: %d will be replaced with a number. */
		echo sprintf(esc_html_x('Found %d estates over all.', 'template', 'onoffice'), $pEstates->getEstateOverallCount());
		?>
	</p>
</div>
<div class="oo-listframe">
	<?php
	$pEstates->resetEstateIterator();
	while ( $currentEstate = $pEstates->estateIterator() ) :
		$marketingStatus = $currentEstate['vermarktungsstatus'];
		unset($currentEstate['vermarktungsstatus']);
		$estateId = $pEstates->getCurrentEstateId();
	?>
		<div class="oo-listobject">
			<div class="oo-listobjectwrap">
				<?php
				$estatePictures = $pEstates->getEstatePictures();
				foreach ( $estatePictures as $id ) {
					$pictureValues = $pEstates->getEstatePictureValues( $id );
					echo '<a href="'.$pEstates->getEstateLink().'" style="background-image: url('.$pEstates->getEstatePictureUrl( $id ).');" class="oo-listimage">';
					if ($pictureValues['type'] === \onOffice\WPlugin\Types\ImageTypes::TITLE && $marketingStatus != '') {
						echo '<span>'.esc_html($marketingStatus).'</span>';
					}
					echo '</a>';
				} ?>
				<div class="oo-listinfo">
					<div class="oo-listtitle">
						<?php echo $currentEstate["objekttitel"]; ?>
					</div>
					<div class="oo-listinfotable">
						<?php foreach ( $currentEstate as $field => $value ) {
							if ( is_numeric( $value ) && 0 == $value ) {
								continue;
							}
							if ( $field === "objekttitel" || $field === "objektbeschreibung" || $field === "lage" || $field === "ausstatt_beschr" || $field === "sonstige_angaben" ) {
								continue;
							}
							if ( $value == "" ) {
								continue;
							}
							echo '<div class="oo-listtd">'.esc_html($pEstates->getFieldLabel( $field )) .'</div><div class="oo-listtd">'.(is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value)).'</div>';
						} ?>
					</div>
					<div class="oo-detailslink">
						<a href="<?php echo $pEstates->getEstateLink(); ?>">
							<?php esc_html_e('Show Details', 'onoffice'); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php if (Favorites::isFavorizationEnabled()): ?>
			<button data-onoffice-estateid="<?php echo $pEstates->getCurrentMultiLangEstateMainId(); ?>" class="onoffice favorize">
				<?php esc_html_e('Add to '.Favorites::getFavorizationLabel(), 'onoffice'); ?>
			</button>
		<?php endif ?>
	<?php endwhile; ?>
</div>
<?php if (Favorites::isFavorizationEnabled()) { ?>
<script>
	$(document).ready(function() {
		onofficeFavorites = new onOffice.favorites(<?php echo json_encode(Favorites::COOKIE_NAME); ?>);
		onOffice.addFavoriteButtonLabel = function(i, element) {
			var estateId = $(element).attr('data-onoffice-estateid');
			if (!onofficeFavorites.favoriteExists(estateId)) {
				$(element).text('<?php echo esc_js(__('Add to '.Favorites::getFavorizationLabel(), 'onoffice')); ?>');
				$(element).on('click', function() {
					onofficeFavorites.add(estateId);
					onOffice.addFavoriteButtonLabel(0, element);
				});
			} else {
				$(element).text('<?php echo esc_js(__('Remove from '.Favorites::getFavorizationLabel(), 'onoffice')); ?>');
				$(element).on('click', function() {
					onofficeFavorites.remove(estateId);
					onOffice.addFavoriteButtonLabel(0, element);
				});
			}
		};
		$('button.onoffice.favorize').each(onOffice.addFavoriteButtonLabel);
	});
</script>
<?php } ?>