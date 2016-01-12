<?php

$config = array(
	/* token of the API account... */
	'token' => '',

	/* ... and its secret */
	'secret' => '',

	/* API branch */
	'apiversion' => 'wp',

	/* register cache classes (with their namespaces) here: */
	'cache' => array(
		new \onOffice\WPlugin\Cache\DBCache( array('ttl' => 3600) ),
	),
);