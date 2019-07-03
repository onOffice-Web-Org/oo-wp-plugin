<h2><?php esc_html_e('More Estates like this', 'onoffice');?></h2>
<ul>

<?php
while ( $currentEstate = $pEstates->estateIterator() ) : ?>
	<li>
		<a href="<?php echo esc_url($pEstates->getEstateLink()); ?>">
			<?php
			$estatePictures = $pEstates->getEstatePictures();
			foreach ($estatePictures as $id) : ?>
				<img src="<?php echo $pEstates->getEstatePictureUrl
					($id, ['width' => 200, 'height' => 100]); ?>">
			<?php endforeach; ?>
		</a>
		<p>
			<?php
			$fieldsForOutput = ['objektnr_extern', 'wohnflaeche', 'ort'];
			echo $currentEstate['objekttitel'].'<br>';
			foreach ($fieldsForOutput as $field) {
				$value = $currentEstate[$field];
				echo esc_html($pEstates->getFieldLabel($field).': '
					.(is_array($value) ? implode(', ', $value) : $value)).'<br>';
			}
			?>
		</p>
	</li>
<?php endwhile; ?>

</ul>
<br>