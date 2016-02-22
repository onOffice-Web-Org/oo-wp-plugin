<?php

/**
 *  Default template
 */

use onOffice\WPlugin\PdfDocumentType;

/* @var $pEstates onOffice\WPlugin\EstateList */

?>
<h1>Detailansicht!</h1>

<?php while ( $currentEstate = $pEstates->estateIterator() ) : ?>

	<?php foreach ( $currentEstate as $field => $value ) :
		if ( is_numeric( $value ) && 0 == $value ) {
			continue;
		}
	?>
		<?php echo $pEstates->getFieldLabel( $field ) .': '.$value; ?><br>

	<?php endforeach; ?>


	<?php
	foreach ( $pEstates->getEstateContacts() as $contactData ) : ?>
		<ul>
			<b>ASP: <?php echo $contactData['Vorname']; ?> <?php echo $contactData['Name']; ?></b>
			<li>Telefon: <?php echo $contactData['defaultphone']; ?></li>
			<li>Telefax: <?php echo $contactData['defaultfax']; ?></li>
			<li>E-Mail: <?php echo $contactData['defaultemail']; ?></li>
		</ul>

	<?php endforeach; ?>

	<?php
	$estatePictures = $pEstates->getEstatePictures();
	foreach ( $estatePictures as $id ) : ?>
	<a href="<?php echo $pEstates->getEstatePictureUrl( $id ); ?>">
		<img src="<?php echo $pEstates->getEstatePictureUrl( $id, array('width' => 300, 'height' => 400) ); ?>">
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
	<?php
		$document = $pEstates->getDocument( PdfDocumentType::EXPOSE_SHORT_DESIGN01 );
	?>
	<a href="<?php echo $document; ?>">PDF-Exposé</a>

<?php endwhile; ?>