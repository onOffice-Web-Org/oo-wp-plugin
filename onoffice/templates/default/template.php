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