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
 *
 */

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

	'curl_options' => array
		(
			'CURLOPT_SSL_VERIFYPEER'	=> true,
			'CURLOPT_PROTOCOLS'		=> 'CURLPROTO_HTTPS',
		),
);