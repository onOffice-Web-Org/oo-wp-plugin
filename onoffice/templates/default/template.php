<?php

/**
 *  Default template
 */

/* @var $pEstates onOffice\WPlugin\EstateList */

?>

<?php while ( $currentEstate = $pEstates->estateIterator() ) : ?>

<p>
	<?php foreach ( $currentEstate as $field => $value ) :
		if ( is_numeric( $value ) && 0 == $value ) {
			continue;
		}
	?>

		<?php echo wptexturize( $pEstates->getFieldLabel( $field ) .': '.$value ); ?><br>

	<?php endforeach; ?>


	<?php
	foreach ( $pEstates->getEstateContacts() as $contactData ) : ?>
	<p>
		<ul>
			<b>ASP: <?php echo $contactData['Vorname']; ?> <?php echo $contactData['Name']; ?></b>
			<li>Telefon: <?php echo $contactData['defaultphone']; ?></li>
			<li>Telefax: <?php echo $contactData['defaultfax']; ?></li>
			<li>E-Mail: <?php echo $contactData['defaultemail']; ?></li>
		</ul>
	</p>
	<?php endforeach; ?>

	<?php
	$estatePictures = $pEstates->getEstatePicturesSmall();
	foreach ( $estatePictures as $id => $picture ) : ?>
		<a href="<?php echo $pEstates->getEstatePictureBig( $id ); ?>">
			<img src="<?php echo $picture ?>">
		</a>
	<?php endforeach; ?>
</p>

<?php
endwhile;
?>