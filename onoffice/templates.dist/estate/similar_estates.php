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

			<?php echo esc_html($currentEstate['objektnr_extern'].' – '.$currentEstate['objekttitel']); ?>
		</a>
	</li>
<?php endwhile; ?>

</ul>
<br>