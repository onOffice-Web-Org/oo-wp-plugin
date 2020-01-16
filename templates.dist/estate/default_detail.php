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
use onOffice\WPlugin\EstateDetail;
/**
 *
 *  Default template
 *
 */
?>
<div class="oo-detailview">
	<?php
	$pEstates->resetEstateIterator();
	while ( $currentEstate = $pEstates->estateIterator() ) { ?>
		<div class="oo-detailsheadline">
			<h1><?php echo $currentEstate["objekttitel"]; ?></h1>
		</div>
		<div class="oo-details-main">
			<div class="oo-detailsgallery" id="oo-galleryslide">
				<?php $estatePictures = $pEstates->getEstatePictures();
				foreach ( $estatePictures as $id ) { ?>
				<div class="oo-detailspicture" style="background-image: url('<?php echo $pEstates->getEstatePictureUrl( $id ); ?>');"></div>
				<?php } ?>
			</div>
			<div class="oo-detailstable">	
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
					echo '<div class="oo-detailslisttd">'.esc_html($pEstates->getFieldLabel( $field )) .'</div><div class="oo-detailslisttd">'.(is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value)).'</div>';
				} ?>
			</div>
			<?php if ( $currentEstate["objektbeschreibung"] !== "" ) { ?>
				<div class="oo-detailsfreetext">	
					<h2><?php esc_html_e('Description', 'onoffice') ?></h2>
					<?php echo $currentEstate["objektbeschreibung"]; ?>
				</div>
			<?php } ?>
			<?php if ( $currentEstate["lage"] !== "" ) { ?>
				<div class="oo-detailsfreetext">
					<h2><?php esc_html_e('Location', 'onoffice') ?></h2>
					<?php echo $currentEstate["lage"]; ?>
				</div>
			<?php } ?>

			<div class="oo-detailsmap">
				<?php // require('map/map.php'); ?>
			</div>
			<?php if ( $currentEstate["ausstatt_beschr"] !== "" ) { ?>
				<div class="oo-detailsfreetext">	
					<h2><?php esc_html_e('Equipment', 'onoffice') ?></h2>
					<?php echo $currentEstate["ausstatt_beschr"]; ?>
				</div>
			<?php } ?>
			<?php if ( $currentEstate["sonstige_angaben"] !== "" ) { ?>
				<div class="oo-detailsfreetext">
					<h2><?php esc_html_e('Other Information', 'onoffice') ?></h2>
					<?php echo $currentEstate["sonstige_angaben"]; ?>
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
				foreach ( $pEstates->getEstateContacts() as $contactData ) : ?>
					<div class="oo-aspname">
						<strong><?php echo $contactData['Anrede'].'&nbsp;'.$contactData['Vorname'].'&nbsp;'.$contactData['Name']; ?></strong>
					</div>
					<div class="oo-asplocation">
						<span><?php echo $contactData['Strasse']; ?></span>
						<span><?php echo $contactData['Plz'].'&nbsp;'.$contactData['Ort']; ?></span>
					</div>
					<div class="oo-aspcontact">
						<span><?php echo $contactData['Telefon1']; ?></span>					
					</div>
				<?php endforeach; ?>
			</div>
			<div class="oo-detailsform">
				<?php
					try {
						$estateId = $pEstates->getCurrentEstateId();
						$pForm = new \onOffice\WPlugin\Form('contact', \onOffice\WPlugin\Form::TYPE_CONTACT);
						include( __DIR__ . "/../form/defaultform.php" );
					} catch (\onOffice\WPlugin\DataFormConfiguration\UnknownFormException $pE) {
						echo esc_html__('(Form is not available)', 'onoffice');
					}
				?>
			</div>
			<div class="oo-detailsexpose">
				<?php if ($pEstates->getDocument() != ''): ?>
					<h2><?php esc_html_e('Documents', 'onoffice'); ?></h2>
					<a href="<?php echo $pEstates->getDocument(); ?>">
						<?php esc_html_e('PDF expose', 'onoffice'); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<div class="oo-similar">
			<?php echo $pEstates->getSimilarEstates(); ?>
		</div>
	<?php } ?>
</div>