<?php $dontEcho = array("objekttitel", "objektbeschreibung", "lage", "ausstatt_beschr", "sonstige_angaben", "MPAreaButlerUrlWithAddress", "MPAreaButlerUrlNoAddress");
,
    // picture properties
$image_width_xs = 539;
$image_width_sm = 508;
$image_width_md = 690;
$image_width_lg = 444;
$image_width_xl = 540;
$image_width_xxl = 412;
$image_width_xxxl = 456;
$dimensions = [
    '575' => [
        'w' => $image_width_xs,
        'h' => round(
            ($image_width_xs * 2) / 3
        )
    ],
    '1600' => [
        'w' => $image_width_xxxl,
        'h' => round(
            ($image_width_xxxl * 2) / 3
        )
    ],
    '1400' => [
        'w' => $image_width_xxl,
        'h' => round(
            ($image_width_xxl * 2) / 3
        )
    ],
    '1200' => [
        'w' => $image_width_xl,
        'h' => round(
            ($image_width_xl * 2) / 3
        )
    ],
    '992' => [
        'w' => $image_width_lg,
        'h' => round(
            ($image_width_lg * 2) / 3
        )
    ],
    '768' => [
        'w' => $image_width_md,
        'h' => round(
            ($image_width_md * 2) / 3
        )
    ],
    '576' => [
        'w' => $image_width_sm,
        'h' => round(
            ($image_width_sm * 2) / 3
        )
    ]
];
?>

<style>
	.oo-details-btn:focus {
		opacity: 0.8;
		text-decoration: none !important;
		background: #80acd3 !important;
	}
</style>

<div class="oo-detailsheadline">
	<h2><?php esc_html_e('More Estates like this', 'onoffice-for-wp-websites');?></h2>
</div>
<div class="oo-listframe" id="oo-similarframe">
	<?php
	while ( $currentEstate = $pEstates->estateIterator() ) {
		$marketingStatus = $currentEstate['vermarktungsstatus'];
		unset($currentEstate['vermarktungsstatus']);
        $estateId = $pEstates->getCurrentEstateId();
        $rawValues = $pEstates->getRawValues();
		$referenz = $rawValues->getValueRaw($estateId)['elements']['referenz'];
		?>
		<div class="oo-listobject">
			<div class="oo-listobjectwrap">
				<?php
				$estatePictures = $pEstates->getEstatePictures();
				foreach ( $estatePictures as $id ) {
					$pictureValues = $pEstates->getEstatePictureValues( $id );
					if ( $referenz === "1" && $pEstates->getViewRestrict() ) {
						echo '<div class="oo-listimage">';
					} else {
						echo '<a class="oo-listimage estate-status" href="' . esc_url($pEstates->getEstateLink()) . '">';
					}
					echo '<picture class="oo-picture">';
                    /**
                     * getResponsiveImageSource(
                     * @param int $imageId
                     * @param int $mediaBreakPoint
                     * @param float|null $imageWidth for media breakpoint, optional
                     * @param float|null $imageHeight for media breakpoint, optional
                     * @param bool $maxWidth, default = false)
                     * @return string for image source on various media
                     */
                    echo $pEstates->getResponsiveImageSource($id, 575, $dimensions['575']['w'], $dimensions['575']['h'], true);
                    echo $pEstates->getResponsiveImageSource($id, 1600, $dimensions['1600']['w'], $dimensions['1600']['h']);
                    echo $pEstates->getResponsiveImageSource($id, 1400, $dimensions['1400']['w'], $dimensions['1400']['h']);
                    echo $pEstates->getResponsiveImageSource($id, 1200, $dimensions['1200']['w'], $dimensions['1200']['h']);
                    echo $pEstates->getResponsiveImageSource($id, 992, $dimensions['992']['w'], $dimensions['992']['h']);
                    echo $pEstates->getResponsiveImageSource($id, 768, $dimensions['768']['w'], $dimensions['768']['h']);
                    echo $pEstates->getResponsiveImageSource($id, 576, $dimensions['576']['w'], $dimensions['576']['h']);
                    echo '<img class="oo-responsive-image estate-status" ' .
                        'src="' . esc_url($pEstates->getEstatePictureUrl($id, ['width'=> $dimensions['1600']['w'], 'height'=>$dimensions['1600']['h']])) . '" ' .
                        'alt="' . esc_html($pEstates->getEstatePictureTitle($id) ?? __('Image of property', 'onoffice-for-wp-websites')) . '" ' .
                        'loading="lazy"/>';
                    echo '</picture>';
					if ($pictureValues['type'] === \onOffice\WPlugin\Types\ImageTypes::TITLE && $marketingStatus != '') {
						echo '<span>'.esc_html($marketingStatus).'</span>';
					}
					echo $referenz === "1" && $pEstates->getViewRestrict() ? '</div>' : '</a>';
				} ?>
				<div class="oo-listinfo">
					<div class="oo-listtitle">
						<?php echo $currentEstate["objekttitel"]; ?>
					</div>
					<div class="oo-listinfotable">
						<?php foreach ( $currentEstate as $field => $value ) {
							if ( is_numeric( $value ) && 0 == $value ) {
								continue;
							}
							if ( in_array($field, $dontEcho) ) {
								continue;
							}
							if ( $value == "" || empty($value) ) {
								continue;
							}
							echo '<div class="oo-listtd">'.esc_html($pEstates->getFieldLabel( $field )) .'</div><div class="oo-listtd">'.(is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value)).'</div>';
						} ?>
					</div>
					<div class="oo-detailslink">
						<?php if ($referenz === "1") { ?>
							<?php if (!$pEstates->getViewRestrict()) { ?>
								<a class="oo-details-btn" href="<?php echo $pEstates->getEstateLink(); ?>">
									<?php esc_html_e('Show Details', 'onoffice-for-wp-websites'); ?>
								</a>
							<?php } ?>
						<?php } else { ?>
							<a class="oo-details-btn" href="<?php echo $pEstates->getEstateLink(); ?>">
								<?php esc_html_e('Show Details', 'onoffice-for-wp-websites'); ?>
							</a>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
</div>