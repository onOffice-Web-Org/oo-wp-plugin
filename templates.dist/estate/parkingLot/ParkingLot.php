<?php
	

use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Language;
use onOffice\WPlugin\DataView\DataDetailView;
require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'fields.php';
/** @var EstateDetail $pEstates */
/** @var $field */
/** @var $currentEstate */

$language = new Language();
$languageDefault = Language::getDefault();
$locale = $language->getLocale();
$locale = !empty($locale) ? $locale : 'de_DE';
$value = $currentEstate->getValueRaw('multiParkingLot');
$currency = $currentEstate->getValueRaw('waehrung');
$codeCurrency = $currentEstate->getValueRaw('codeWaehrung');
unset($currentEstate['codeWaehrung']);
$currency = !empty($currency) ? $currency : '€';
$codeCurrency = !empty($codeCurrency) ? $codeCurrency : 'EUR';
$result = renderParkingLot($value, $languageDefault, $locale, $codeCurrency, $currency);
$pDataView = $pEstates->getDataView();
$class =  ($pDataView instanceof DataDetailView) ? 'oo-detailslisttd' : 'oo-listtd';
$detailParkingLot = '';

if (!empty($result)) {
	$detailParkingLot .= '<div class="' . $class . '">' . count( $result );
	$item = 0;
	foreach ( $result as $detail ) {
		$item ++;
		if ( $item == count( $result ) ) {
			$detailParkingLot .= esc_html( $detail );
		} else {
			$detailParkingLot .= esc_html( $detail ) . ', ';
		}
	}
	$detailParkingLot .= '</div>';
} else {
	return;
}
$elementParkingLot =  '<div class="'.$class.'">'.esc_html($pEstates->getFieldLabel( $field )).'</div>'."\n";
if (!empty($detailParkingLot))
{
	$elementParkingLot .= $detailParkingLot;
} else {
	$elementParkingLot .= '<div class="clear"></div>';
}
echo $elementParkingLot;