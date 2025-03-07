<?php

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
 *  Default template
 */

/* @var $pEstates onOffice\WPlugin\EstateList */

?>
<h2><?php esc_html_e('Entities', 'onoffice-for-wp-websites');?></h2>

<?php

$dont_echo = ['vermarktungsstatus','objekttitel'];

$pEstatesClone = clone $pEstates;
$pEstatesClone->resetEstateIterator();
?>

<?php if (
    (bool) $pEstates->estateIterator() == true &&
    !empty($pEstates->estateIterator())
) { ?>
    <div class="oo-units-table">
        <table class="oo-units__wrapper">
            <thead class="oo-units__head">
                <tr class="oo-units__row">
                    <?php
                    $empty_columns = [];
                    while (
                        $current_property = $pEstatesClone->estateIterator()
                       
                    ) {
                        if (!empty($current_property)) {
                            foreach ($current_property as $field => $value) {
                                if (in_array($field, $dont_echo)) {
                                    continue;
                                }

                                if (!isset($empty_columns[$field])) {
                                    $empty_columns[$field] = true;
                                }

                                if (
                                    !(
                                        (is_numeric($value) && 0 == $value) ||
                                        $value == '0000-00-00' ||
                                        $value == '0.00' ||
                                        $value == 'Nein' ||
                                        $value == 'No' ||
                                        $value == 'Ne' ||
                                        $value == '' ||
                                        empty($value)
                                    )
                                ) {
                                    $empty_columns[$field] = false;
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
                                (isset($empty_columns[$field]) &&
                                    $empty_columns[$field])
                            ) {
                                continue;
                            }

                            echo '<th class="oo-units__data">';
                            echo $pEstates->getFieldLabel($field);
                            echo '</th>';
                        }
                    }

                    echo '<th class="oo-units__data">';
                    echo esc_html__('Details', 'oo_theme');
                    echo '</th>';
                    ?>
                </tr>
            </thead>
            <tbody class="oo-units__body">
                <?php
                $pEstates->resetEstateIterator();
                while ($current_property = $pEstates->estateIterator()) {
                    echo '<tr class="oo-units__row">';
                    foreach ($current_property as $field => $value):
                        if (
                            in_array($field, $dont_echo) ||
                            (isset($empty_columns[$field]) &&
                                $empty_columns[$field])
                        ) {
                            continue;
                        }

                        if (
                            (is_numeric($value) && 0 == $value) ||
                            $value == '0000-00-00' ||
                            $value == '0.00' ||
                            $value == 'Nein' ||
                            $value == 'No' ||
                            $value == 'Ne' ||
                            $value == '' ||
                            empty($value)
                        ) {
                            $value = '-';
                            $class = ' --empty';
                        } else {
                            $value = $value;
                            $class = '';
                        }

                        echo '<td class="oo-units__data' .
                            $class.
                            '" data-label="' .
                            $pEstates->getFieldLabel($field) .
                            '">';
                        echo $value;
                        echo '</td>';
                    endforeach;

                    echo '<td class="oo-units__data oo-unitslink" data-label="' .
                        esc_html__('Details', 'oo_theme') .
                        '">';
                    if (!empty($pEstates->getEstateLink())) {
                        echo '<a class="oo-units-btn" title="'.esc_html__('Zur Einheit', 'oo_theme').': '.$current_property['objekttitel'].'" href="' .
                            esc_url($pEstates->getEstateLink()) .
                            '">';
                    }
                    echo esc_html__('Zur Einheit', 'oo_theme');
                    echo '</a>';
                    echo '</td>';

                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
<?php } ?>
<div>
	<?php
	if (get_option('onoffice-pagination-paginationbyonoffice')) {
		wp_link_pages();
	}
	?>
</div>