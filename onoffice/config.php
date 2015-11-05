<?php


/* token of the API account... */
$config['token'] = 'xyz';

/** ... and its secret */
$config['secret'] = '123';

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
);

$config['estate']['miete']['filter'] = array(
	'vermarktungsart' => array
		(
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
);

$config['estate']['kauf']['filter'] = array(
	'vermarktungsart' => array
		(
			array('op' => '=', 'val' => 'kauf'),
		),
);