<?php

use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Language;

require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fields.php';

/** @var EstateDetail $pEstates */
/** @var $field */
/** @var $currentEstate */

$language = new Language();
$languageDefault = Language::getDefault();
$locale = $language->getLocale();
$value = $currentEstate->getValueRaw('multiParkingLot');
$result = renderParkingLot($value, $languageDefault, $locale);

echo '<div class="oo-detailslisttd">'.esc_html($pEstates->getFieldLabel( $field )).'</div>'."\n"
	.'<div class="oo-detailslisttd">'
	.(is_array($result) ? esc_html(implode(', ', $result)) : esc_html($result))
	.'</div>'."\n";
