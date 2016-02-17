<?php

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
				),
				'pictures' => array(
					ImageType::TITLE,
				),
				'language' => 'DEU',
				'records' => 20,
				'template' => 'default',
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
);

$config['forms'] = array(
	'estatelistcontactform' => array(
		'inputs' => array(
			'Vorname'	=> 'address',
			'Name'		=> 'address',
			'Strasse'	=> 'address',
			'Plz-Ort'	=> 'address',
			'message'	=> null,
			'Id'		=> 'estate',
		),
		'language'	=>	'ENG',
		'required'	=>	array('Vorname', 'Name', 'message'),
	),
	'defaultform' => array(
		'inputs' => array(
			'Vorname'		=> 'address',
			'Name'			=> 'address',
			'message'		=> null,
		),
		'language'	=>	'ENG',
		'subject'	=>	'Eine Kontaktanfrage.',
		'recipient' => 'you@my-onoffice.com',
		'required'	=>	array('Vorname', 'Name', 'message'),
	),
);


// Search

$maxPrice = FormPost::getPostValue('preis_bis', FILTER_VALIDATE_INT);
if ( ! is_null( $maxPrice ) ) {
	$config['estate']['kauf']['filter']['kaufpreis'][] = array('op' => '<', 'val' => $maxPrice);
	$config['estate']['kauf']['filter']['kaufpreis'][] = array('op' => '>', 'val' => 0);
}

unset($maxPrice);