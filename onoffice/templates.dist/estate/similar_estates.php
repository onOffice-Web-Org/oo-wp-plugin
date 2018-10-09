<h2><?php esc_html_e('Similar Estates', 'onoffice');?></h2>
<?php while ( $currentEstate = $pEstates->estateIterator() ) : ?>
	<?php foreach ( $currentEstate as $field => $value ) :
		if ( is_numeric( $value ) && 0 == $value ) {
			continue;
		}
	?>
		<?php echo $pEstates->getFieldLabel( $field ) .': '.(is_array($value) ? implode(', ', $value) : $value); ?><br>

	<?php endforeach; ?>
<?php endwhile; ?>
<br>