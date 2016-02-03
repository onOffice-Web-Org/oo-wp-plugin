<?php

/**
 *  Default template
 */

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
endwhile;
?>