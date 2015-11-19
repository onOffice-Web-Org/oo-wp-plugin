<?php


/* token of the API account... */
$config['token'] = 'xyz';

/** ... and its secret */
$config['secret'] = '123';

$config['apiversion'] = 'trunk';

/* Estate data you want to fetch */

$config['estate']['miete']['data'] = array(
	'objektnr_extern',
	'kaltmiete',
	'warmmiete',
	'heizkosten_in_nebenkosten',
	'energieverbrauchskennwert',
	'balkon_terrasse_flaeche',
	'grundstuecksflaeche',
	'warmwasserEnthalten',
	'objekttitel',
);

$config['estate']['miete']['language'] = 'ENG';
$config['estate']['kauf']['language'] = 'ENG';

$config['estate']['miete']['filter'] = array(
	'vermarktungsart' => array(
		array('op' => '=', 'val' => 'miete'),
	),
);

$config['estate']['kauf']['data'] = array(
	'objektnr_extern',
	'kaufpreis',
	'heizkosten_in_nebenkosten',
	'energieverbrauchskennwert',
	'balkon_terrasse_flaeche',
	'grundstuecksflaeche',
	'objekttitel',
);

$config['estate']['miete']['detailpageid'] = 24;
$config['estate']['miete']['listpagename'] = 'mietobjekte';
$config['estate']['kauf']['detailpageid'] = 34;
$config['estate']['kauf']['listpagename'] = 'kaufobjekte';

$config['estate']['kauf']['filter'] = array(
	'vermarktungsart' => array(
		array('op' => '=', 'val' => 'kauf'),
	),
);

$config['estate']['miete']['contactdata'] = array(
	'Vorname',
	'Name',
	'defaultphone',
	'defaultfax',
	'defaultemail',
);

$config['estate']['kauf']['contactdata'] = array(
	'Vorname',
	'Name',
	'defaultphone',
	'defaultfax',
	'defaultemail',
);