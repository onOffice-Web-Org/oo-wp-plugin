<?php


/* token of the API account... */
$config['token'] = 'xyz';

/** ... and its secret */
$config['secret'] = '123';

/* Estate data you want to fetch */
$config['estate']['data'] = array(
	'objektnr_extern',
	'kaufpreis',
	'kaltmiete',
	'warmmiete',
	'heizkosten_in_nebenkosten',
	'energieverbrauchskennwert',
	'balkon_terrasse_flaeche',
	'grundstuecksflaeche',
	'warmwasserEnthalten',
);

$config['estate']['filter'] = array(
	'vermarktungsart' => array
		(
			array('op' => '=', 'val' => 'miete'),
		),
);