<h2><?php esc_html_e('More Estates like this', 'onoffice');?></h2>
<ul>

<?php
while ( $currentEstate = $pEstates->estateIterator() ) : ?>
	<li>
		<a href="<?php echo esc_url($pEstates->getEstateLink()); ?>">
			<?php esc_html_e($currentEstate['objekttitel']); ?>
		</a>
	</li>
<?php endwhile; ?>

</ul>
<br>