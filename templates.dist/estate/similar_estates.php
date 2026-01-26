<?php

if ( ! defined( 'ABSPATH' ) ) exit;

$dontEcho = array("objekttitel", "objektbeschreibung", "lage", "ausstatt_beschr", "sonstige_angaben", "MPAreaButlerUrlWithAddress", "MPAreaButlerUrlNoAddress");

/*  responsive picture properties
 *  customizable widths and heights for individual layouts
 */
$image_width_xs = 382;
$image_width_sm = 355;
$image_width_md = 465;
$image_width_lg = 370;
$image_width_xl = 440;
$image_width_xxl = 500;
$image_width_xxxl = 605;
$image_height_xs = null;
$image_height_sm = null;
$image_height_md = null;
$image_height_lg = null;
$image_height_xl = null;
$image_height_xxl = null;
$image_height_xxxl = null;
$dimensions = [
    '575' => [
        'w' => $image_width_xs,
        'h' => $image_height_xs
    ],
    '1600' => [
        'w' => $image_width_xxxl,
        'h' => $image_height_xxxl
    ],
    '1400' => [
        'w' => $image_width_xxl,
        'h' => $image_height_xxl
    ],
    '1200' => [
        'w' => $image_width_xl,
        'h' => $image_height_xl
    ],
    '992' => [
        'w' => $image_width_lg,
        'h' => $image_height_lg
    ],
    '768' => [
        'w' => $image_width_md,
        'h' => $image_height_md
    ],
    '576' => [
        'w' => $image_width_sm,
        'h' => $image_height_sm
    ]
];
?>

<?php if (!empty($pEstates->estateIterator())) { ?>
    <div class="oo-similar">
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
		                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                            echo $pEstates->getResponsiveImageSource($id, 575, $dimensions['575']['w'], $dimensions['575']['h'], true);
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                            echo $pEstates->getResponsiveImageSource($id, 1600, $dimensions['1600']['w'], $dimensions['1600']['h']);
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                            echo $pEstates->getResponsiveImageSource($id, 1400, $dimensions['1400']['w'], $dimensions['1400']['h']);
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                            echo $pEstates->getResponsiveImageSource($id, 1200, $dimensions['1200']['w'], $dimensions['1200']['h']);
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                            echo $pEstates->getResponsiveImageSource($id, 992, $dimensions['992']['w'], $dimensions['992']['h']);
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                            echo $pEstates->getResponsiveImageSource($id, 768, $dimensions['768']['w'], $dimensions['768']['h']);
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                            echo $pEstates->getResponsiveImageSource($id, 576, $dimensions['576']['w'], $dimensions['576']['h']);
		                    echo '<img class="oo-responsive-image estate-status" ' .
                                'src="' . esc_url($pEstates->getEstatePictureUrl($id, isset($dimensions['1600']['w']) || isset($dimensions['1600']['h']) ? ['width'=> $dimensions['1600']['w'], 'height'=>$dimensions['1600']['h']] : null)) . '" ' .
                                'alt="' . esc_attr($pEstates->getEstatePictureTitle($id) ?? __('Image of property', 'onoffice-for-wp-websites')) . '" ' .
                                'loading="lazy">';
		                    echo '</picture>';
							if ($pictureValues['type'] === \onOffice\WPlugin\Types\ImageTypes::TITLE && $marketingStatus != '') {
								echo '<span>'.esc_html($marketingStatus).'</span>';
							}
							echo $referenz === "1" && $pEstates->getViewRestrict() ? '</div>' : '</a>';
						} ?>
						<div class="oo-listinfo">
							<div class="oo-listtitle">
								<?php echo esc_html($currentEstate["objekttitel"]); ?>
							</div>
							<div class="oo-listinfotable">
								<?php
								$keyfacts = array_flip($pEstates->getHighlightedFields());
								$estateFacts = iterator_to_array($currentEstate);
								// keep order but float keyfacts to the top
								$estateFacts = array_merge(
									array_intersect_key($estateFacts, $keyfacts), // get only highlighted fields
									array_diff_key($estateFacts, $keyfacts) // get only non highlighted
								);
								foreach ( $estateFacts as $field => $value ) {
									if ( is_numeric( $value ) && 0 == $value ) {
										continue;
									}
									if ( in_array($field, $dontEcho) ) {
										continue;
									}
									if ( $value == "" || empty($value) ) {
										continue;
									}
									// skip negative boolean fields
									if (is_string($value) && $value !== '' && !is_numeric($value) && ($rawValues->getValueRaw($estateId)['elements'][$field] ?? null) === "0"){
										continue;
									}
									if (
										($rawValues->getValueRaw($estateId)['elements']['provisionsfrei'] ?? null) === "1" &&
										in_array($field,['innen_courtage', 'aussen_courtage'],true)
									) {
										continue;
									}

									$class = 'oo-listtd'. ($pEstates->isHighlightedField($field) ? ' --highlight' : '');
                                    echo '<div class="'.esc_attr($class).'">'.esc_html($pEstates->getFieldLabel( $field )).'</div>'.
                                        '<div class="'.esc_attr($class).'">'.(is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value)).'</div>';
								} ?>
							</div>
							<div class="oo-detailslink">
                                <?php if ($referenz === "1") { ?>
                                    <?php if (!$pEstates->getViewRestrict()) { 
										/* translators: %d: real estate ID number */ ?>
                                        <a class="oo-details-btn" href="<?php echo esc_url($pEstates->getEstateLink()); ?>" aria-label="<?php echo esc_attr(sprintf(esc_html_x('Show Details for Real Estate No. %d', 'template', 'onoffice-for-wp-websites'), (int)$estateId)); ?>">
                                            <?php esc_html_e('Show Details', 'onoffice-for-wp-websites'); ?>
                                        </a>
                                    <?php } ?>
                                <?php } else { 
									/* translators: %d: real estate ID number */ ?>
                                    <a class="oo-details-btn" href="<?php echo esc_url($pEstates->getEstateLink()); ?>" aria-label="<?php echo esc_attr(sprintf(esc_html_x('Show Details for Real Estate No. %d', 'template', 'onoffice-for-wp-websites'), (int)$estateId)); ?>">
                                        <?php esc_html_e('Show Details', 'onoffice-for-wp-websites'); ?>
                                    </a>
                                <?php } ?>
                            </div>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
<?php } ?>

