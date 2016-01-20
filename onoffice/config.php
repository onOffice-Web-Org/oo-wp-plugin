<?php

use \onOffice\WPlugin\FormPost;


/* Estate data you want to fetch */

$config['estate'] = array(
	'miete' => array(
		'listpagename' => 'mietobjekte',
		'filter' => array(
			'vermarktungsart' => array(
				array('op' => '=', 'val' => 'miete'),
			),
		),
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
				),
				'contactdata' => array(
					'Vorname',
					'Name',
					'defaultphone',
					'defaultfax',
					'defaultemail',
				),
				'pictures' => array(
					'Titelbild',
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
				),
				'pictures' => array(
					'Titelbild',
					'Foto',
					'Foto_gross',
					'Grundriss',
					'Lageplan',
					'Stadtplan',
					'Anzeigen',
					'Epass_Skala',
					'Finanzierungsbeispiel',
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
				),
				'contactdata' => array(
					'Vorname',
					'Name',
					'defaultphone',
					'defaultfax',
					'defaultemail',
				),
				'pictures' => array(
					'Titelbild',
				),
				'formname' => 'estatelistcontactform',
				'language' => 'DEU',
				'records' => 20,
				'template' => 'default'
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
				),
				'contactdata' => array(
					'Vorname',
					'Name',
					'defaultphone',
					'defaultfax',
					'defaultemail',
				),
				'pictures' => array(
					'Titelbild',
					'Foto',
					'Foto_gross',
					'Grundriss',
					'Lageplan',
					'Stadtplan',
					'Anzeigen',
					'Epass_Skala',
					'Finanzierungsbeispiel',
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