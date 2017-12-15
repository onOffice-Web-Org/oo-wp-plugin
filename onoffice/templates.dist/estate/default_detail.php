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
 *  Default template
 */

/* @var $pEstates onOffice\WPlugin\EstateList */

?>
<h1>Detailansicht!</h1>

<?php while ( $currentEstate = $pEstates->estateIterator() ) : ?>
	<?php echo $pEstates->getEstateUnits( ); ?>
	<?php foreach ( $currentEstate as $field => $value ) :
		if ( is_numeric( $value ) && 0 == $value ) {
			continue;
		}
	?>
		<?php echo $pEstates->getFieldLabel( $field ) .': '.(is_array($value) ? implode(', ', $value) : $value); ?><br>

	<?php endforeach; ?>


	<?php
	foreach ( $pEstates->getEstateContacts() as $contactData ) : ?>
		<ul>
			<b>ASP: <?php echo $contactData['Vorname']; ?> <?php echo $contactData['Name']; ?></b>
			<?php // either use the phone number flagged as default (add `default*` to config) ... ?>
			<!--<li>Telefon: <?php // echo $contactData['defaultphone']; ?></li>-->
			<!--<li>Telefax: <?php // echo $contactData['defaultfax']; ?></li>-->
			<!--<li>E-Mail: <?php // echo $contactData['defaultemail']; ?></li>-->


			<?php // ... or the specific one (add `mobile`, `phone`, `email` to config): ?>
			<?php
			$mobilePhoneNumbers = $contactData->offsetExists('mobile') ? $contactData->getValueRaw('mobile') : array();
			if (count($mobilePhoneNumbers) > 0) :
			?>
				<li>Mobil: <?php echo esc_html(array_shift($mobilePhoneNumbers)); ?></li>
			<?php endif; ?>
			<?php
			$businessPhoneNumbers = $contactData->offsetExists('phonebusiness') ?
				$contactData->getValueRaw('phonebusiness') : array();
			if (count($businessPhoneNumbers) > 0) :
			?>
				<li>phone business: <?php echo esc_html(array_shift($businessPhoneNumbers)); ?></li>
			<?php endif; ?>
			<?php
			$businessEmailAddresses = $contactData->offsetExists('emailbusiness') ?
				$contactData->getValueRaw('emailbusiness') : array();
			if (count($businessEmailAddresses) > 0) :
			?>
				<li>email business: <?php echo esc_html(array_shift($businessEmailAddresses)); ?></li>
			<?php endif; ?>
		</ul>

	<?php endforeach; ?>

	<?php
	$estatePictures = $pEstates->getEstatePictures();
	foreach ( $estatePictures as $id ) : ?>
	<a href="<?php echo $pEstates->getEstatePictureUrl( $id ); ?>">
		<img src="<?php echo $pEstates->getEstatePictureUrl( $id, array('width' => 300, 'height' => 400) ); ?>">
		<?php echo $pEstates->getEstatePictureTitle($id).'<br>'; ?>
	</a>
	<?php endforeach; ?>

		<?php
			$position = array(
				'lat' => (float) $currentEstate['breitengrad'],
				'lng' => (float) $currentEstate['laengengrad'],
			);
			$title = $currentEstate['objekttitel'];

			if ( 1 == $currentEstate['showGoogleMap'] ) {
				$pMaps = new onOffice\WPlugin\Maps\GoogleMap();

				// if you want the marker to be always visible, change the 3rd arg to true
				$pMaps->addNewMarker(
					$position['lng'], $position['lat'], ! $currentEstate['virtualAddress'], $title );
				$pMaps->setZoom(16);
				echo $pMaps->render();
			}
		?>

	<h2>Dokumente</h2>
	<a href="<?php echo $pEstates->getDocument(); ?>">PDF-Exposé</a>

<?php endwhile; ?>