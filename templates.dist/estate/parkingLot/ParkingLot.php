<?php
	
use DI\ContainerBuilder;
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
if (empty($locale))
{
	$locale = 'de_DE';
}
$value = $currentEstate->getValueRaw('multiParkingLot');
$currency = $currentEstate->getValueRaw('waehrung');
$codeCurrency = $currentEstate->getValueRaw('codeWaehrung');
unset($currentEstate['codeWaehrung']);
$result = renderParkingLot($value, $languageDefault, $locale, $codeCurrency, $currency);
$pDataView = $pEstates->getDataView();
$class =  ($pDataView instanceof DataDetailView) ? 'oo-detailslisttd' : 'oo-listtd';
$detailParkingLot = '';

if (!empty($result))
{
	$detailParkingLot .= '<div class="'.$class.'">';
		$detailParkingLot .= '<ul class="oo-listparking">';
		foreach ($result as $detail)
		{
			$detailParkingLot .= '<li>' .  esc_html($detail) . '</li>';
		}
		$detailParkingLot .= '</ul>';
	$detailParkingLot .= '</div>';
}
$elementParkingLot =  '<div class="'.$class.' parking">'.esc_html($pEstates->getFieldLabel( $field )).'</div>'."\n";
if (!empty($detailParkingLot))
{
	$elementParkingLot .= $detailParkingLot;
}
echo $elementParkingLot;