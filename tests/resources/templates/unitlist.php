<?php

use onOffice\tests\EstateListMocker;
$i = 0;
/* @var $pEstates EstateListMocker */
while ($currentEstate = $pEstates->estateIterator()) {
    foreach ($currentEstate as $field => $value) {
        echo esc_html($field).': '.(is_array($value) ? esc_html(implode(', ', $value)) : esc_html($value))."\n";
    }
    echo "--\n";
}
