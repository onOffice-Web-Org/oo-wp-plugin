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

use onOffice\WPlugin\FormPost;
use onOffice\WPlugin\ImageType;
use onOffice\WPlugin\PdfDocumentType;


/* Estate data you want to fetch */

$config['estate'] = array(
	'miete' => array(
		'listpagename' => 'mietobjekte',
		'filter' => array(
			'vermarktungsart' => array(
				array('op' => '=', 'val' => 'miete'),
			),
		),
		'documents' => array(PdfDocumentType::EXPOSE_SHORT_DESIGN01),
		'views' => array(
			'list' => array(
				'data' => array(
					'Id',
					'objektnr_extern',
					'kaltmiete',
					'warmmiete',
					'heizkosten_in_nebenkosten',
					'energieverbrauchskennwert',
					'balkon_terrasse_flaeche',
					'grundstuecksflaeche',
					'warmwasserEnthalten',
					'objekttitel',
					'laengengrad',
					'breitengrad',
					'showGoogleMap',
					'virtualStreet',
					'virtualLongitude',
					'virtualLatitude',
					'virtualAddress',
				),
				'contactdata' => array(
					'Vorname',
					'Name',
					'defaultphone',
					'defaultfax',
					'defaultemail',
					'imageUrl',
				),
				'pictures' => array(
					ImageType::TITLE,
				),
				'language' => 'DEU',
				'records' => 20,
				'template' => 'default',
				'sortby' => 'objektnr_extern',
				'sortorder' => 'DESC',
			),
			'detail' => array(
				'data' => array(
					'Id',
					'objektnr_extern',
					'kaltmiete',
					'warmmiete',
					'heizkosten_in_nebenkosten',
					'energieverbrauchskennwert',
					'balkon_terrasse_flaeche',
					'grundstuecksflaeche',
					'warmwasserEnthalten',
					'objekttitel',
					'laengengrad',
					'breitengrad',
					'showGoogleMap',
					'virtualStreet',
					'virtualLongitude',
					'virtualLatitude',
					'virtualAddress',
				),
				'pictures' => array(
					ImageType::TITLE,
					ImageType::PHOTO_BIG,
					ImageType::PHOTO,
					ImageType::LOCATION_MAP,
					ImageType::GROUNDPLAN,
					ImageType::FINANCE_EXAMPLE,
					ImageType::ENERGY_PASS_RANGE,
					ImageType::CITY_MAP,
				),
				'language' => 'DEU',
				'pageid' => 24,
				'pagename' => 'Objektdetailansicht Mietobjekt',
				'template' => 'default_detail',
			),
		),
	),
	'kauf' => array(
		'listpagename' => 'kaufobjekte',
		'filter' => array(
			'vermarktungsart' => array(
				array('op' => '=', 'val' => 'kauf'),
			),
		),
		'documents' => array(PdfDocumentType::EXPOSE_SHORT_DESIGN01),
		'views' => array(
			'list' => array(
				'data' => array(
					'Id',
					'kaufpreis',
					'objektnr_extern',
					'energieverbrauchskennwert',
					'balkon_terrasse_flaeche',
					'grundstuecksflaeche',
					'warmwasserEnthalten',
					'objekttitel',
					'objektart',
					'laengengrad',
					'breitengrad',
					'showGoogleMap',
					'virtualStreet',
					'virtualLongitude',
					'virtualLatitude',
					'virtualAddress',
				),
				'contactdata' => array(
					'Vorname',
					'Name',
					'defaultphone',
					'defaultfax',
					'defaultemail',
					'imageUrl',
				),
				'pictures' => array(
					ImageType::TITLE,
				),
				'formname' => 'estatelistcontactform',
				'language' => 'DEU',
				'records' => 20,
				'template' => 'default',
			),
			'detail' => array(
				'data' => array(
					'Id',
					'objektnr_extern',
					'kaufpreis',
					'heizkosten_in_nebenkosten',
					'energieverbrauchskennwert',
					'balkon_terrasse_flaeche',
					'grundstuecksflaeche',
					'warmwasserEnthalten',
					'objekttitel',
					'objektart',
					'laengengrad',
					'breitengrad',
					'showGoogleMap',
					'strasse',
					'hausnummer',
					'plz',
					'ort',
					'virtualStreet',
					'virtualLongitude',
					'virtualLatitude',
					'virtualAddress',
				),
				'contactdata' => array(
					'Vorname',
					'Name',
					'defaultphone',
					'defaultfax',
					'defaultemail',
				),
				'pictures' => array(
					ImageType::TITLE,
					ImageType::PHOTO_BIG,
					ImageType::PHOTO,
					ImageType::LOCATION_MAP,
					ImageType::GROUNDPLAN,
					ImageType::FINANCE_EXAMPLE,
					ImageType::ENERGY_PASS_RANGE,
					ImageType::CITY_MAP,
				),
				'formname' => 'defaultform',
				'language' => 'ENG',
				'pageid' => 34,
				'pagename' => 'Objektdetailansicht Kaufobjekt',
				'template' => 'default_detail',
			),
		),
	),
	'unitlist' => array(
		'listpagename' => 'einheiten',
		'filter' => array(),
		'documents' => array(),
		'views' => array(
			'units' => array(
				'data' => array(
					'Id',
					'kaufpreis',
					'objektnr_extern',
					'objekttitel',
					'objektart',
					'laengengrad',
					'breitengrad',
				),
				'contactdata' => array(
					'Vorname',
					'Name',
					'defaultphone',
					'defaultfax',
					'defaultemail',
					'imageUrl',
				),
				'pictures' => array(
					ImageType::TITLE,
				),
				'formname' => '',
				'language' => 'DEU',
				'records' => 99999,
				'pageid' => 11,
				'template' => 'default_units',
			),
		),
	),
);

$config['forms'] = array(
	'estatelistcontactform' => array(
		'inputs' => array(
			'Vorname'	=> 'address',
			'Name'		=> 'address',
			'Strasse'	=> 'address',
			'Plz'		=> 'address',
			'Ort'		=> 'address',
			'message'	=> null,
			'Id'		=> 'estate',
		),
		'formtype'	=>	\onOffice\WPlugin\Form::TYPE_CONTACT,
		'language'	=>	'ENG',
		'createaddress' => false, // no compounding fields possible, if true
		// 'checkduplicate'=> false, // optional, if createaddress
		'required'	=>	array('Vorname', 'Name', 'message'),
	),
	'defaultform' => array(
		'inputs' => array(
			'Vorname'		=> 'address',
			'Name'			=> 'address',
			'message'		=> null,
		),
		'formtype'	=>	\onOffice\WPlugin\Form::TYPE_CONTACT,
		'language'	=>	'ENG',
		'subject'	=>	'Eine Kontaktanfrage.',
		'recipient' => 'you@my-onoffice.com',
		'required'	=>	array('Vorname', 'Name', 'message'),
	),
	'searchform' => array(
		'inputs' => array(
            'regionaler_zusatz' => 'estate',
			'vermarktungsart' => 'estate',
            'heizkosten_in_nebenkosten' => 'estate',
		),
		'formtype'	=>	\onOffice\WPlugin\Form::TYPE_FREE,
		'language'	=>	'ENG',
        'required'	=>	array(),
	),
);


// Search

// http://php.net/manual/de/filter.filters.sanitize.php

$maxPrice = FormPost::getPostValue('preis_bis', FILTER_VALIDATE_INT);
$vermarktungsart = isset( $_POST['vermarktungsart'] ) ? $_POST['vermarktungsart'] : array();
$regionaler_zusatz = FormPost::getPostValue('regionaler_zusatz', FILTER_SANITIZE_STRING);
$hkInNk = FormPost::getPostValue('heizkosten_in_nebenkosten', FILTER_SANITIZE_STRING);

if ( ! is_null( $maxPrice ) ) {
	$config['estate']['kauf']['filter']['kaufpreis'][] = array('op' => '<', 'val' => $maxPrice);
	$config['estate']['kauf']['filter']['kaufpreis'][] = array('op' => '>', 'val' => 0);
}

if ( $vermarktungsart != "" ) {
	if ( is_array( $vermarktungsart ) && count( $vermarktungsart ) > 0 ) {
		$config['estate']['kauf']['filter']['vermarktungsart'][0] = array('op' => 'in', 'val' => $vermarktungsart);
	} elseif (!is_array( $vermarktungsart )) {
		$config['estate']['kauf']['filter']['vermarktungsart'][0] = array('op' => '=', 'val' => $vermarktungsart);
	}
}

if ( $regionaler_zusatz != "" ) {
	$config['estate']['kauf']['filter']['regionaler_zusatz'][0] = array('op' => 'like', 'val' => $regionaler_zusatz);
}

if ( ( $hkInNk != "" ) ) {
	$config['estate']['kauf']['filter']['heizkosten_in_nebenkosten'][0] = array('op' => '=', 'val' => ( $hkInNk == "on" ? "J" : "N" ));
}

unset($maxPrice);
unset($vermarktungsart);
unset($regionaler_zusatz);
unset($hkInNk);