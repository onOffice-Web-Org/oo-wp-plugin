<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *
 *    Copyright (C) 2026  onOffice GmbH
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
 *  Fallback template for the standalone "complexunits" list type (list of the child estates
 *  of an explicitly configured parent/main estate). Modeled after default_units.php.
 *
 *  Renders as a CSS Grid "table" rather than a real <table> element: a single grid container
 *  (role="table") holds two rowgroups (role="rowgroup") whose rows (role="row") and cells
 *  (role="columnheader"/"cell") are all direct grid items via `display: contents` on the
 *  rowgroups/rows - this keeps the accessible table semantics (a screen reader still announces
 *  rows/columns exactly as it would for a real <table>) while letting CSS Grid handle column
 *  sizing/alignment instead of the browser's table layout engine, and lets the same markup
 *  collapse into a stacked card layout below the `--stacked` breakpoint by simply overriding
 *  `display` at that breakpoint - no separate mobile markup needed. Reuses the `data-label`
 *  attribute every cell already carries for that stacked view. Everything here (CSS + JS) is
 *  self-contained in this plugin template - no theme dependency, per the "kein theme-spezifisches"
 *  requirement.
 */

/* @var $pEstates onOffice\WPlugin\EstateList */
use onOffice\WPlugin\Favorites;
use onOffice\WPlugin\Pagination\ListPagination;
use onOffice\WPlugin\Types\ImageTypes;

$list_id = $pEstates->getDataView()->getId();

$dont_echo = ['vermarktungsstatus','objekttitel'];

$pEstatesClone = clone $pEstates;
$pEstatesClone->resetEstateIterator();
$rawValues = $pEstates->getRawValues();

// "Merkliste" column: same feature/cookie mechanism as the default list template
// (templates.dist/estate/default.php), just rendered as a compact icon button here.
$showFavoritesColumn = Favorites::isFavorizationEnabled();

// "Grundriss" column: only shown when this list view's "Grundriss in Liste anzeigen" setting
// (Fotoarten/picture types, see RecordManagerUpdateListViewEstate::updateSelectedPictureTypes())
// has the Grundriss picture type enabled.
$showFloorplanColumn = in_array(ImageTypes::GROUNDPLAN, $pEstates->getDataView()->getPictureTypes(), true);
?>

<?php if ($pEstates->getShowMapConfig()) { ?>
    <div class="oo-estate-map">
        <?php require('map/map.php'); ?>
    </div>
<?php } ?>

<?php
$hasUnits = (bool) $pEstates->estateIterator();
$pEstates->resetEstateIterator();
if ($hasUnits) {
    $visible_columns = [];
    while (
        $current_property = $pEstatesClone->estateIterator()

    ) {
        $estateId = $pEstatesClone->getCurrentEstateId();
        if (!empty($current_property)) {
            foreach ($current_property as $field => $value) {
                if (in_array($field, $dont_echo)) {
                    continue;
                }
                if (
                    !(
                        (is_numeric($value) && 0 == $value) ||
                        $value == '0000-00-00' ||
                        $value == '0.00' ||
                        (is_string($value) && $value !== '' && !is_numeric($value) && ($rawValues->getValueRaw($estateId)['elements'][$field] ?? null) === "0") || // skip negative boolean fields
                        $value == '' ||
                        empty($value)
                    )
                ) {
                    $visible_columns [$field]= true;
                }
            }
        }
    }

    $pEstates->resetEstateIterator();
    $first_property = $pEstates->estateIterator();

    // Whether the detail page is configured/published at all (the marketplace's "Detailseite
    // aktivieren" toggle) is a site-wide setting, not something that varies estate by estate -
    // getEstateLink() falls back to '#' identically for every row when it's off, so checking it
    // once here (rather than per row) is enough to decide whether the whole "Details" column has
    // anything to show at all. Computed up front (rather than only while rendering the header, as
    // the table-based version of this template used to) so the grid's column-track list below can
    // already account for it.
    $showDetailsColumn = $first_property && $pEstates->getEstateLink() !== '#';

    // Grid column tracks, in the exact order cells are emitted below: icon columns (Merkliste,
    // Grundriss) and the Details column size to their content, every real data field shares the
    // remaining space equally - this is what a real <table>'s automatic column sizing used to give
    // us for free, so the grid container needs to be told explicitly instead.
    $gridColumns = [];
    if ($showFavoritesColumn) {
        $gridColumns[] = 'auto';
    }
    if ($showFloorplanColumn) {
        $gridColumns[] = 'auto';
    }
    if ($first_property) {
        foreach ($first_property as $field => $value) {
            if (in_array($field, $dont_echo) || !isset($visible_columns[$field])) {
                continue;
            }
            $gridColumns[] = 'minmax(0, 1fr)';
        }
    }
    if ($showDetailsColumn) {
        $gridColumns[] = 'auto';
    }
    ?>
    <style>
        .oo-complexunits-table {
            overflow-x: auto;
        }
        .oo-complexunits__wrapper {
            display: grid;
            width: 100%;
            font-size: 0.95em;
        }
        .oo-complexunits__head,
        .oo-complexunits__body,
        .oo-complexunits__row {
            display: contents;
        }
        .oo-complexunits__data {
            padding: 0.75em 1em;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }
        .oo-complexunits__head .oo-complexunits__data {
            font-weight: 600;
            border-bottom-width: 2px;
        }
        .oo-complexunits__row:hover .oo-complexunits__data {
            background-color: rgba(0, 0, 0, 0.03);
        }
        .oo-complexunits__data.--empty {
            color: rgba(0, 0, 0, 0.4);
        }
        .oo-complexunits__data--icon {
            white-space: nowrap;
            text-align: center;
        }
        .oo-complexunits-iconbtn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2em;
            height: 2em;
            padding: 0;
            border: 0;
            background: transparent;
            cursor: pointer;
            color: currentColor;
            line-height: 0;
        }
        .oo-complexunits-iconbtn svg {
            width: 1.25em;
            height: 1.25em;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.6;
        }
        button.onoffice.favorize.oo-complexunits-iconbtn[data-favorized="true"] svg {
            fill: currentColor;
        }
        .oo-complexunits-btn {
            display: inline-block;
            padding: 0.5em 1.1em;
            border-radius: 999px;
            background-color: rgba(0, 0, 0, 0.85);
            color: #fff;
            text-decoration: none;
            white-space: nowrap;
        }
        .oo-complexunits-btn:hover {
            background-color: rgba(0, 0, 0, 0.65);
        }

        /* Responsive stacked layout - reuses the data-label attribute every cell already carries. */
        @media (max-width: 640px) {
            .oo-complexunits__wrapper,
            .oo-complexunits__head,
            .oo-complexunits__body,
            .oo-complexunits__row {
                display: block;
            }
            .oo-complexunits__head {
                position: absolute;
                width: 1px;
                height: 1px;
                overflow: hidden;
                clip: rect(0 0 0 0);
            }
            .oo-complexunits__body .oo-complexunits__row {
                margin-bottom: 1em;
                border: 1px solid rgba(0, 0, 0, 0.08);
                border-radius: 0.5em;
                padding: 0.25em 0;
            }
            .oo-complexunits__data {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1em;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }
            .oo-complexunits__body .oo-complexunits__row .oo-complexunits__data:last-child {
                border-bottom: 0;
            }
            .oo-complexunits__data::before {
                content: attr(data-label);
                font-weight: 600;
                padding-right: 1em;
            }
        }

        /* Grundriss lightbox */
        .oo-complexunits-lightbox {
            position: fixed;
            inset: 0;
            z-index: 100000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 2em;
            background-color: rgba(0, 0, 0, 0.75);
        }
        .oo-complexunits-lightbox.--open {
            display: flex;
        }
        .oo-complexunits-lightbox__image {
            max-width: 100%;
            max-height: 100%;
            border-radius: 0.25em;
            background-color: #fff;
        }
        .oo-complexunits-lightbox__close {
            position: absolute;
            top: 1em;
            right: 1em;
            width: 2.5em;
            height: 2.5em;
            border: 0;
            border-radius: 999px;
            background-color: rgba(255, 255, 255, 0.9);
            cursor: pointer;
            font-size: 1.25em;
            line-height: 1;
        }
        .oo-visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
    <div class="oo-complexunits">
        <h2><?php esc_html_e('Units', 'onoffice-for-wp-websites');?></h2>
        <div class="oo-complexunits-table">
            <div class="oo-complexunits__wrapper" role="table" aria-label="<?php esc_attr_e('Units', 'onoffice-for-wp-websites'); ?>" style="grid-template-columns: <?php echo esc_attr(implode(' ', $gridColumns)); ?>;">
                <div class="oo-complexunits__head" role="rowgroup">
                    <div class="oo-complexunits__row" role="row">
                        <?php
                        if ($showFavoritesColumn) {
                            echo '<div class="oo-complexunits__data oo-complexunits__data--icon" role="columnheader">';
                            echo '<span class="oo-visually-hidden">' . esc_html__('Watchlist', 'onoffice-for-wp-websites') . '</span>';
                            echo '</div>';
                        }

                        if ($showFloorplanColumn) {
                            echo '<div class="oo-complexunits__data oo-complexunits__data--icon" role="columnheader">';
                            echo esc_html__('Floor plan', 'onoffice-for-wp-websites');
                            echo '</div>';
                        }

                        if ($first_property) {
                            foreach ($first_property as $field => $value) {
                                if (
                                    in_array($field, $dont_echo) ||
                                    !isset($visible_columns[$field])
                                ) {
                                    continue;
                                }

                                echo '<div class="oo-complexunits__data" role="columnheader">';
                                echo esc_html($pEstates->getFieldLabel($field));
                                echo '</div>';
                            }
                        }

                        if ($showDetailsColumn) {
                            echo '<div class="oo-complexunits__data" role="columnheader">';
                            echo esc_html__('Details', 'onoffice-for-wp-websites');
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
                <div class="oo-complexunits__body" role="rowgroup">
                    <?php
                    $pEstates->resetEstateIterator();
                    while ($current_property = $pEstates->estateIterator()) {
						$estateId = $pEstates->getCurrentEstateId();
                        echo '<div class="oo-complexunits__row" role="row">';

                        if ($showFavoritesColumn) {
                            $favorizationLabel = Favorites::getFavorizationLabel() === 'Watchlist'
                                ? esc_html__('Add to watchlist', 'onoffice-for-wp-websites')
                                : esc_html__('Add to favorites', 'onoffice-for-wp-websites');
                            $estateLabel = sprintf(
                                /* translators: %d: real estate ID number */
                                esc_html_x('Real Estate No. %d', 'template', 'onoffice-for-wp-websites'),
                                (int) $estateId
                            );

                            echo '<div class="oo-complexunits__data oo-complexunits__data--icon" role="cell" data-label="' .
                                esc_attr__('Watchlist', 'onoffice-for-wp-websites') . '">';
                            echo '<button type="button" data-onoffice-estateid="' .
                                esc_attr($pEstates->getCurrentMultiLangEstateMainId()) .
                                '" class="onoffice favorize oo-complexunits-iconbtn" data-favorized="false" aria-label="' .
                                esc_attr($favorizationLabel . ' ' . $estateLabel) . '">' .
                                '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 21s-7.5-4.7-10-9C.4 8.4 2 4.5 6 4c2.2-.3 4 .8 6 3 2-2.2 3.8-3.3 6-3 4 .5 5.6 4.4 4 8-2.5 4.3-10 9-10 9z"/></svg>' .
                                '</button>';
                            echo '</div>';
                        }

                        if ($showFloorplanColumn) {
                            $floorplanIds = $pEstates->getEstatePictures([ImageTypes::GROUNDPLAN]);
                            $floorplanId = $floorplanIds[0] ?? null;
                            $floorplanUrl = $floorplanId !== null ? $pEstates->getEstatePictureUrl($floorplanId) : null;

                            echo '<div class="oo-complexunits__data oo-complexunits__data--icon" role="cell" data-label="' .
                                esc_attr__('Floor plan', 'onoffice-for-wp-websites') . '">';
                            if (!empty($floorplanUrl)) {
                                echo '<button type="button" class="oo-complexunits-iconbtn oo-complexunits-floorplan-btn" data-floorplan-url="' .
                                    esc_url($floorplanUrl) . '" aria-label="' .
                                    esc_attr__('Show floor plan', 'onoffice-for-wp-websites') . '">' .
                                    '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>' .
                                    '</button>';
                            } else {
                                echo '-';
                            }
                            echo '</div>';
                        }

                        foreach ($current_property as $field => $value):
                            if (
                                in_array($field, $dont_echo) ||
                                !isset($visible_columns[$field])
                                ) {
                                continue;
                            }

                            if (
                                (is_numeric($value) && 0 == $value) ||
                                $value == '0000-00-00' ||
                                $value == '0.00' ||
                                $value == '' ||
                                empty($value) ||
                                (is_string($value) && $value !== '' && !is_numeric($value) && ($rawValues->getValueRaw($estateId)['elements'][$field] ?? null) === "0") || // skip negative boolean fields
                                (($rawValues->getValueRaw($estateId)['elements']['provisionsfrei'] ?? null) === "1" &&
                                    in_array($field,['innen_courtage', 'aussen_courtage'],true))
                            ) {
                                $value = '-';
                                $class = ' --empty';
                            } else {
                                $value = $value;
                                $class = '';
                            }

                            echo '<div class="oo-complexunits__data' .
                                esc_attr($class).
                                '" role="cell" data-label="' .
                                esc_attr($pEstates->getFieldLabel($field)) .
                                '">';
                            echo is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value);
                            echo '</div>';
                        endforeach;

                        // getEstateLink() never returns an empty string - it falls back to '#' when
                        // no detail page is configured/published (e.g. the marketplace's
                        // "Detailseite aktivieren" toggle is off). Whether that's the case doesn't
                        // vary per row, so the whole column is skipped via $showDetailsColumn
                        // (computed once, above) rather than emptying each row's cell individually.
                        if ($showDetailsColumn) {
                            echo '<div class="oo-complexunits__data oo-complexunitslink" role="cell" data-label="' .
                                esc_html__('Details', 'onoffice-for-wp-websites') .
                                '">';
                            echo '<a class="oo-complexunits-btn" title="'.esc_attr__('To the unit', 'onoffice-for-wp-websites').': '.esc_attr($current_property['objekttitel']).'" href="' .
                                esc_url($pEstates->getEstateLink()) .
                                '">' .
                                esc_html__('To the unit', 'onoffice-for-wp-websites') .
                                '</a>';
                            echo '</div>';
                        }

                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($showFloorplanColumn) { ?>
    <div class="oo-complexunits-lightbox" id="oo-complexunits-lightbox" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__('Floor plan', 'onoffice-for-wp-websites'); ?>">
        <button type="button" class="oo-complexunits-lightbox__close" aria-label="<?php echo esc_attr__('Close', 'onoffice-for-wp-websites'); ?>">&times;</button>
        <img class="oo-complexunits-lightbox__image" src="" alt="<?php echo esc_attr__('Floor plan', 'onoffice-for-wp-websites'); ?>">
    </div>
    <script>
        (function () {
            var lightbox = document.getElementById('oo-complexunits-lightbox');
            if (!lightbox) {
                return;
            }
            var image = lightbox.querySelector('.oo-complexunits-lightbox__image');
            var closeBtn = lightbox.querySelector('.oo-complexunits-lightbox__close');

            function openLightbox(url) {
                image.setAttribute('src', url);
                lightbox.classList.add('--open');
                closeBtn.focus();
            }

            function closeLightbox() {
                lightbox.classList.remove('--open');
                image.setAttribute('src', '');
            }

            document.querySelectorAll('.oo-complexunits-floorplan-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var url = btn.getAttribute('data-floorplan-url');
                    if (url) {
                        openLightbox(url);
                    }
                });
            });

            closeBtn.addEventListener('click', closeLightbox);
            lightbox.addEventListener('click', function (event) {
                if (event.target === lightbox) {
                    closeLightbox();
                }
            });
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && lightbox.classList.contains('--open')) {
                    closeLightbox();
                }
            });
        })();
    </script>
    <?php } ?>
    <?php
if (get_option('onoffice-pagination-paginationbyonoffice')) {
	?>
	<div class="oo-listpagination">
		<?php

		$ListPagination = new ListPagination([
			'class' => 'oo-post-nav-links',
			'type' => 'property',
			'anchor' => 'oo-listheadline',
			'list_id' => $list_id
		]);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render() method outputs escaped HTML
        echo $ListPagination->render();
		?>
	</div>
<?php
}
?>
<?php if ($showFavoritesColumn) { ?>
<script>
	jQuery(document).ready(function($) {
		if (typeof onOffice === 'undefined' || typeof onOffice.favorites === 'undefined') {
			return;
		}
		var onofficeComplexunitsFavorites = new onOffice.favorites(<?php echo json_encode(Favorites::COOKIE_NAME); ?>);
		var updateComplexunitsFavoriteButton = function(i, element) {
			var estateId = $(element).attr('data-onoffice-estateid');
			var estateLabel = '<?php echo esc_js(__('Real Estate No.', 'onoffice-for-wp-websites')); ?> ' + estateId;
			var isFavorized = onofficeComplexunitsFavorites.favoriteExists(estateId);
			$(element).attr('data-favorized', isFavorized ? 'true' : 'false');

			var label = isFavorized
				? '<?php echo esc_js(Favorites::getFavorizationLabel() === 'Watchlist' ? __('Remove from watchlist', 'onoffice-for-wp-websites') : __('Remove from favorites', 'onoffice-for-wp-websites')); ?>'
				: '<?php echo esc_js(Favorites::getFavorizationLabel() === 'Watchlist' ? __('Add to watchlist', 'onoffice-for-wp-websites') : __('Add to favorites', 'onoffice-for-wp-websites')); ?>';
			$(element).attr('aria-label', label + ' ' + estateLabel);
		};

		$(document).on('click', '.oo-complexunits-iconbtn.favorize', function() {
			var estateId = $(this).attr('data-onoffice-estateid');
			if (onofficeComplexunitsFavorites.favoriteExists(estateId)) {
				onofficeComplexunitsFavorites.remove(estateId);
			} else {
				onofficeComplexunitsFavorites.add(estateId);
			}
			updateComplexunitsFavoriteButton(0, this);
		});

		$('.oo-complexunits-iconbtn.favorize').each(updateComplexunitsFavoriteButton);
	});
</script>
<?php } ?>
<?php } ?>
