<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *
 *    Copyright (C) 2016  onOffice Software AG
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
/**
 *
 *  Default template
 *
 */
use onOffice\WPlugin\Favorites;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\Pagination\ListPagination;

// display search form
require 'SearchForm.php';
/* @var $pEstates onOffice\WPlugin\EstateList */

// Listing ID for pagination query parameter
$list_id = $pEstates->getDataView()->getId();

$dontEcho = array("objekttitel", "objektbeschreibung", "lage", "ausstatt_beschr", "sonstige_angaben", "MPAreaButlerUrlWithAddress", "MPAreaButlerUrlNoAddress", "dreizeiler");

/*  responsive picture properties
 *  customizable widths and heights for individual layouts
 */
$image_width_xs = 382;
$image_width_sm = 355;
$image_width_md = 465;
$image_width_lg = 370;
$image_width_xl = 440;
$image_width_xxl = 500;
$image_width_xxxl = 600;
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

<style>
	.oo-listinfotableview {
		display: flex;
		flex-wrap: wrap;
	}
	ul.oo-listparking {
		padding: 0 10px;
	}
</style>

<div class="oo-estate-map">
    <?php require('map/map.php'); ?>
</div>
<div class="oo-listheadline" id="oo-listheadline-<?php echo esc_attr($list_id);?>">
	<h1><?php esc_html_e('Overview of Estates', 'onoffice-for-wp-websites'); ?></h1>
	<p>
		
		<?php /* translators: %d will be replaced with a number. */
		echo sprintf(esc_html_x('Found %d estates over all.', 'template', 'onoffice-for-wp-websites'), (int)$pEstates->getEstateOverallCount());
		?>
	</p>
</div>
<div class="oo-estate-sort">
	<?php 
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- generateSortDropDown() returns escaped HTML
	echo '<div class="col-lg-12">'.$generateSortDropDown().'</div>'; ?>
</div>
<div class="oo-listframe">
	<?php
	$pEstatesClone = clone $pEstates;
	$pEstatesClone->resetEstateIterator();
	while ( $currentEstate = $pEstatesClone->estateIterator() ) :
		$marketingStatus = $currentEstate['vermarktungsstatus'];
		unset($currentEstate['vermarktungsstatus']);
		$estateId = $pEstatesClone->getCurrentEstateId();
		$rawValues = $pEstatesClone->getRawValues();
		$referenz = $rawValues->getValueRaw($estateId)['elements']['referenz'];
	?>
		<div class="oo-listobject">
			<div class="oo-listobjectwrap">
				<?php
				$estatePictures = $pEstatesClone->getEstatePictures();
				foreach ( $estatePictures as $id ) {
					$pictureValues = $pEstatesClone->getEstatePictureValues( $id );

					if ( $referenz === "1" && $pEstatesClone->getViewRestrict() ) {
						echo '<div class="oo-listimage estate-status">';
					} else {
						echo '<a class="oo-listimage estate-status" href="' . esc_url($pEstatesClone->getEstateLink()) . '">';
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
                    echo $pEstatesClone->getResponsiveImageSource($id, 575, $dimensions['575']['w'], $dimensions['575']['h'], true);
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                    echo $pEstatesClone->getResponsiveImageSource($id, 1600, $dimensions['1600']['w'], $dimensions['1600']['h']);
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                    echo $pEstatesClone->getResponsiveImageSource($id, 1400, $dimensions['1400']['w'], $dimensions['1400']['h']);
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                    echo $pEstatesClone->getResponsiveImageSource($id, 1200, $dimensions['1200']['w'], $dimensions['1200']['h']);
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                    echo $pEstatesClone->getResponsiveImageSource($id, 992, $dimensions['992']['w'], $dimensions['992']['h']);
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                    echo $pEstatesClone->getResponsiveImageSource($id, 768, $dimensions['768']['w'], $dimensions['768']['h']);
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Returns escaped HTML source tags
                    echo $pEstatesClone->getResponsiveImageSource($id, 576, $dimensions['576']['w'], $dimensions['576']['h']);
                    echo '<img class="oo-responsive-image estate-status" ' .
                        'src="' . esc_url($pEstatesClone->getEstatePictureUrl($id, isset($dimensions['1600']['w']) || isset($dimensions['1600']['h']) ? ['width'=> $dimensions['1600']['w'], 'height'=>$dimensions['1600']['h']] : null)) . '" ' .
                        'alt="' . esc_attr($pEstatesClone->getEstatePictureTitle($id)?? __('Image of property', 'onoffice-for-wp-websites')) . '" ' .
                        'loading="lazy">';
                    echo '</picture>';
					if ($pictureValues['type'] === \onOffice\WPlugin\Types\ImageTypes::TITLE && $marketingStatus != '') {
						echo '<span>'.esc_html($marketingStatus).'</span>';
					}
					echo $referenz === "1" && $pEstatesClone->getViewRestrict() ? '</div>' : '</a>';
				} ?>
				<div class="oo-listinfo">
					<div class="oo-listtitle">
						<?php echo esc_html($currentEstate["objekttitel"]); ?>
					</div>
					<div class="oo-listinfotable oo-listinfotableview">
						<?php
							$keyfacts = array_flip($pEstatesClone->getHighlightedFields());
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
							if ( empty($value) ) {
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
                            echo '<div class="'.esc_attr($class).'">'.esc_html($pEstatesClone->getFieldLabel( $field )).'</div>'.
                                '<div class="'.esc_attr($class).'">'.(is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value)).'</div>';
						} ?>
					</div>
					<div class="oo-detailslink">
						<?php if ($referenz === "1") { ?>
                            <?php if (!$pEstatesClone->getViewRestrict()) { 
								/* translators: %d: real estate ID number */ ?>
                                <a class="oo-details-btn" href="<?php echo esc_url($pEstatesClone->getEstateLink()); ?>" aria-label="<?php echo esc_attr(sprintf(esc_html_x('Show Details for Real Estate No. %d', 'template', 'onoffice-for-wp-websites'), (int)$estateId)); ?>">
                                    <?php esc_html_e('Show Details', 'onoffice-for-wp-websites'); ?>
                                </a>
                            <?php } ?>
                        <?php } else { 
							/* translators: %d: real estate ID number */ ?>
                            <a class="oo-details-btn" href="<?php echo esc_url($pEstatesClone->getEstateLink()); ?>" aria-label="<?php echo esc_attr(sprintf(esc_html_x('Show Details for Real Estate No. %d', 'template', 'onoffice-for-wp-websites'), (int)$estateId)); ?>">
                                <?php esc_html_e('Show Details', 'onoffice-for-wp-websites'); ?>
                            </a>
                        <?php } ?>
                        <?php if (Favorites::isFavorizationEnabled()): 
                                    $setting = Favorites::getFavorizationLabel();
                                    if ($setting == 'Watchlist') {
                                        $FavorizationLabel = esc_html(
                                            __('Add to watchlist', 'onoffice-for-wp-websites')
                                        );
                                    } else if ($setting == 'Favorites') {
                                        $FavorizationLabel = esc_html(
                                            __('Add to favorites', 'onoffice-for-wp-websites')
                                        );
                                    }

                            /* translators: %d: real estate ID number */ ?>
                            <button data-onoffice-estateid="<?php echo esc_attr($pEstatesClone->getCurrentMultiLangEstateMainId()); ?>" class="onoffice favorize" aria-label="<?php echo esc_attr($FavorizationLabel.' '.sprintf(esc_html_x('Real Estate No. %d', 'template', 'onoffice-for-wp-websites'), (int)$estateId)); ?>">
                                <?php
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above
                                    echo $FavorizationLabel;
                                ?>
                            </button>
                        <?php endif ?>
					</div>
				</div>
			</div>
		</div>
	<?php endwhile; ?>
</div>
<?php
if (get_option('onoffice-pagination-paginationbyonoffice')) {
	?>
	<div class="oo-listpagination">
		<?php
	
		$ListPagination = new ListPagination([
			'class' => 'oo-post-nav-links',
			'type' => 'property',
			'anchor' => 'oo-listheadline-'.$list_id ,
			'list_id' => $list_id
		]);
		
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render() method outputs escaped HTML
        echo $ListPagination->render();
		?>
	</div>
<?php
}
?>

<?php if (Favorites::isFavorizationEnabled()) { ?>
<script>
	jQuery(document).ready(function($) {
		onofficeFavorites = new onOffice.favorites(<?php echo json_encode(Favorites::COOKIE_NAME); ?>);
		onOffice.addFavoriteButtonLabel = function(i, element) {
			var estateId = $(element).attr('data-onoffice-estateid');
			var estateLabel = '<?php echo esc_js(__('Real Estate No.', 'onoffice-for-wp-websites')); ?> ' + estateId;
			if (!onofficeFavorites.favoriteExists(estateId)) {
				$(element).text('<?php
						$setting = Favorites::getFavorizationLabel();
						if ($setting == 'Watchlist') {
							echo esc_js(
								__('Add to watchlist', 'onoffice-for-wp-websites')
							);
						} else if ($setting == 'Favorites') {
							echo esc_js(
								__('Add to favorites', 'onoffice-for-wp-websites')
							);
						}
					?>');
				$(element).attr('aria-label', $(element).text() + ' ' + estateLabel);
				$(element).on('click', function() {
					onofficeFavorites.add(estateId);
					onOffice.addFavoriteButtonLabel(0, element);
				});
			} else {
				$(element).text('<?php
						$setting = Favorites::getFavorizationLabel();
						if ($setting == 'Watchlist') {
							echo esc_js(
								__('Remove from watchlist', 'onoffice-for-wp-websites')
							);
						} else if ($setting == 'Favorites') {
							echo esc_js(
								__('Remove from favorites', 'onoffice-for-wp-websites')
							);
						}
					?>');
				$(element).attr('aria-label', $(element).text() + ' ' + estateLabel);
				$(element).on('click', function() {
					onofficeFavorites.remove(estateId);
					onOffice.addFavoriteButtonLabel(0, element);
				});
			}
		};
		$('button.onoffice.favorize').each(onOffice.addFavoriteButtonLabel);
	});
</script>
<?php } ?>
