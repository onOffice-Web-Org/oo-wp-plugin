<?php
/**
 *
 *    Copyright (C) 2020  onOffice GmbH
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

use onOffice\WPlugin\EstateDetail;

/**
 *
 *  Default template
 *
 */

$dontEcho = array("objekttitel", "objektbeschreibung", "lage", "ausstatt_beschr", "sonstige_angaben");
/** @var EstateDetail $pEstates */
?>
<div class="oo-detailview">
	<?php
	$pEstates->resetEstateIterator();
	while ( $currentEstate = $pEstates->estateIterator() ) { ?>
		<div class="oo-detailsheadline">
			<h1><?php echo $currentEstate["objekttitel"]; ?></h1>
			<?php if (!empty($currentEstate['vermarktungsstatus'])) { ?>
				<span style="padding:0 15px"><?php echo ucfirst($currentEstate['vermarktungsstatus']); ?></span>
				<?php unset($currentEstate['vermarktungsstatus']); ?>
			<?php } ?>
		</div>
		<div class="oo-details-main">
			<div class="oo-detailsgallery" id="oo-galleryslide">
				<?php
				$estatePictures = $pEstates->getEstatePictures();
				foreach ( $estatePictures as $id ) {
					printf('<div class="oo-detailspicture" style="background-image: url(\'%s\');"></div>'."\n",
						esc_url($pEstates->getEstatePictureUrl($id)));
				}
			?>
			</div>
			<div class="oo-detailstable">
				<?php
				foreach ( $currentEstate as $field => $value ) {
					if ( is_numeric( $value ) && 0 == $value ) {
						continue;
					}
					if ( in_array($field, $dontEcho) ) {
						continue;
					}
					if ( $value == "" ) {
						continue;
					}
                    if ($field == 'multiParkingLot') {
                        include 'ParkingLot.php';
                        continue;
                    }
					echo '<div class="oo-detailslisttd">'.esc_html($pEstates->getFieldLabel( $field )).'</div>'."\n"
						.'<div class="oo-detailslisttd">'
							.(is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value))
							.'</div>'."\n";
				}
                ?>
			</div>

			<?php if ( $currentEstate["objektbeschreibung"] !== "" ) { ?>
				<div class="oo-detailsfreetext">
					<h2><?php esc_html_e('Description', 'onoffice'); ?></h2>
					<?php echo nl2br($currentEstate["objektbeschreibung"]); ?>
				</div>
			<?php } ?>

			<?php if ( $currentEstate["lage"] !== "" ) { ?>
				<div class="oo-detailsfreetext">
					<h2><?php esc_html_e('Location', 'onoffice'); ?></h2>
					<?php echo nl2br($currentEstate["lage"]); ?>
				</div>
			<?php }

            ob_start();
            require('map/map.php');
            $mapContent = ob_get_clean();
            if ($mapContent != '') { ?>
            <div class="oo-detailsmap">
                <h2><?php esc_html_e('Map', 'onoffice'); ?></h2>
                <?php echo $mapContent; ?>
            </div>
            <?php } ?>

			<?php if ( $currentEstate["ausstatt_beschr"] !== "" ) { ?>
				<div class="oo-detailsfreetext">
					<h2><?php esc_html_e('Equipment', 'onoffice'); ?></h2>
					<?php echo nl2br($currentEstate["ausstatt_beschr"]); ?>
				</div>
			<?php } ?>

			<?php if ( $currentEstate["sonstige_angaben"] !== "" ) { ?>
				<div class="oo-detailsfreetext">
					<h2><?php esc_html_e('Other Information', 'onoffice'); ?></h2>
					<?php echo nl2br($currentEstate["sonstige_angaben"]); ?>
				</div>
			<?php } ?>

			<div class="oo-units">
				<?php echo $pEstates->getEstateUnits( ); ?>
			</div>
		</div>
		<div class="oo-details-sidebar">
			<div class="oo-asp">
				<h2><?php echo esc_html__('Contact person', 'onoffice'); ?></h2>
				<?php
				$addressFields = $pEstates->getAddressFields();
				foreach ( $pEstates->getEstateContacts() as $contactData ) : ?>
					<?php
					foreach ($addressFields as $field) {
						if (empty($contactData[$field])) {
							continue;
						}

						if ($field === 'imageUrl') {
							echo '<div class="oo-aspinfo oo-contact-info"><img src="' . esc_html($contactData[$field]) . '" height="150px"></div>';
						} elseif (is_array($contactData[$field])) {
							echo '<div class="oo-aspinfo oo-contact-info">';
							foreach ($contactData[$field] as $item) {
								echo '<p>' . esc_html($item) . '</p>';
							}
							echo '</div>';
						} else {
							echo '<div class="oo-aspinfo oo-contact-info"><p>' . esc_html($contactData[$field]) . '</p></div>';
						}
					} ?>
				<?php endforeach; ?>
			</div>
			<div class="oo-detailsexpose">
				<?php if ($pEstates->getDocument() != ''): ?>
					<h2><?php esc_html_e('Documents', 'onoffice'); ?></h2>
					<a href="<?php echo $pEstates->getDocument(); ?>">
						<?php esc_html_e('PDF expose', 'onoffice'); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php $estateMovieLinks = $pEstates->getEstateMovieLinks();
			foreach ($estateMovieLinks as $movieLink) {
				echo '<div class="oo-video"><a href="'.esc_attr($movieLink['url']).'" title="'.esc_attr($movieLink['title']).'">'
					.esc_html($movieLink['title']).'</a></div>';
			}

			$movieOptions = array('width' => 500); // optional

			foreach ($pEstates->getMovieEmbedPlayers($movieOptions) as $movieInfos) {
				echo '<div class="oo-video"><h2>'.esc_html($movieInfos['title']).'</h2>';
				echo $movieInfos['player'];
				echo '</div>';
			} ?>

		</div>
		<?php
		if (get_option('onoffice-pagination-paginationbyonoffice')){ ?>
            <div>
				<?php
				wp_link_pages();
				?>
            </div>
		<?php }?>
		<div class="oo-similar">
			<?php echo $pEstates->getSimilarEstates(); ?>
		</div>
	<?php } ?>

</div>

<?php
$shortCodeForm = $pEstates->getShortCodeForm();
if (!empty($shortCodeForm)) {
	?>
	<div class="detail-contact-form">
		<?php echo do_shortcode($shortCodeForm); ?>
	</div>
<?php } ?>
