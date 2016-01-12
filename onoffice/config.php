<?php

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
				'formname' => 'defaultform',
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
				'language' => 'DEU',
				'pageid' => 34,
				'pagename' => 'Objektdetailansicht Kaufobjekt',
				'template' => 'default_detail',
			),
		),
	),
);

$config['forms'] = array(
	'defaultform' => array(
		'inputs' => array(
			'Vorname'	=>	'address',
			'name'		=>	'address',
		),
		'language'	=>	'DEU',
	),
);