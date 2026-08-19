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
 */

/* @var $pEstates onOffice\WPlugin\EstateList */
use onOffice\WPlugin\Pagination\ListPagination;
$list_id = $pEstates->getDataView()->getId();

$dont_echo = ['vermarktungsstatus','objekttitel'];

$pEstatesClone = clone $pEstates;
$pEstatesClone->resetEstateIterator();
$rawValues = $pEstates->getRawValues();
?>

<?php if (
    (bool) $pEstates->estateIterator() == true &&
    !empty($pEstates->estateIterator())
) { ?>
    <div class="oo-complexunits">
        <h2><?php esc_html_e('Units', 'onoffice-for-wp-websites');?></h2>
        <div class="oo-complexunits-table">
            <table class="oo-complexunits__wrapper">
                <thead class="oo-complexunits__head">
                    <tr class="oo-complexunits__row">
                        <?php
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

                        if ($first_property) {
                            foreach ($first_property as $field => $value) {
                                if (
                                    in_array($field, $dont_echo) ||
                                    !isset($visible_columns[$field])
                                ) {
                                    continue;
                                }

                                echo '<th class="oo-complexunits__data">';
                                echo esc_html($pEstates->getFieldLabel($field));
                                echo '</th>';
                            }
                        }

                        echo '<th class="oo-complexunits__data">';
                        echo esc_html__('Details', 'onoffice-for-wp-websites');
                        echo '</th>';
                        ?>
                    </tr>
                </thead>
                <tbody class="oo-complexunits__body">
                    <?php
                    $pEstates->resetEstateIterator();
                    while ($current_property = $pEstates->estateIterator()) {
						$estateId = $pEstates->getCurrentEstateId();
                        echo '<tr class="oo-complexunits__row">';
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
                        
                            echo '<td class="oo-complexunits__data' .
                                esc_attr($class).
                                '" data-label="' .
                                esc_attr($pEstates->getFieldLabel($field)) .
                                '">';
                            echo is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value);
                            echo '</td>';
                        endforeach;
                    
                        echo '<td class="oo-complexunits__data oo-complexunitslink" data-label="' .
                            esc_html__('Details', 'onoffice-for-wp-websites') .
                            '">';
                        if (!empty($pEstates->getEstateLink())) {
                            echo '<a class="oo-complexunits-btn" title="'.esc_attr__('To the unit', 'onoffice-for-wp-websites').': '.esc_attr($current_property['objekttitel']).'" href="' .
                                esc_url($pEstates->getEstateLink()) .
                                '">';
                        }
                        echo esc_html__('To the unit', 'onoffice-for-wp-websites');
                        echo '</a>';
                        echo '</td>';
                    
                        echo '</tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
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
} ?>
