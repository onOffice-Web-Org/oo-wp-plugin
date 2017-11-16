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
use onOffice\WPlugin\SearchParameters;

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
	'ownerform' => array(
		'inputs' => array(
			'Vorname'		=> 'address',
			'Name'			=> 'address',
			'Strasse'		=> 'address',
			'Plz'			=> 'address',
			'Ort'			=> 'address',
			'Telefon1'		=> 'address',
			'Telefax1'		=> 'address',
			'Email'			=> 'address',
			'objektart'		=> 'estate',
			'nutzungsart'		=> 'estate',
			'vermarktungsart' 	=> 'estate',
			'plz'			=> 'estate',
			'ort'			=> 'estate',
			'kaufpreis'		=> 'estate',
			'kaltmiete'		=> 'estate',
			'anzahl_zimmer'		=> 'estate',
			'wohnflaeche'		=> 'estate',
			'grundstuecksflaeche'	=> 'estate',
			'sonstige_angaben'	=> 'estate',
			'Id'			=> 'estate',
		),
		'formtype'	=>	\onOffice\WPlugin\Form::TYPE_OWNER,
		'language'	=>	'DEU',
		'subject'	=>	'Eine Eigentümerangabe',
		'recipient' 	=> 	'a.ivanova@onoffice.de',
		'required'	=>	array('Vorname', 'Name', 'Telefon1', 'Email'),
		'checkduplicate'=> false, // optional, if createaddress
	),
	'ownerleadgeneratorform' => array(
		'inputs' => array(
			'Vorname'		=> 'address',
			'Name'			=> 'address',
			'Strasse'		=> 'address',
			'Plz'			=> 'address',
			'Ort'			=> 'address',
			'Telefon1'		=> 'address',
			'Telefax1'		=> 'address',
			'Email'			=> 'address',
			'objektart'		=> 'estate',
			'nutzungsart'	=> 'estate',
			'vermarktungsart' 	=> 'estate',
			'plz'			=> 'estate',
			'ort'			=> 'estate',
			'kaufpreis'		=> 'estate',
			'kaltmiete'		=> 'estate',
			'anzahl_zimmer'	=> 'estate',
			'wohnflaeche'	=> 'estate',
			'grundstuecksflaeche'	=> 'estate',
			'sonstige_angaben'	=> 'estate',
			'Id'			=> 'estate',
		),
		'formtype'	=>	\onOffice\WPlugin\Form::TYPE_OWNER,
		'pages'		=>	3,
		'language'	=>	'DEU',
		'subject'	=>	'Eine Eigentümerangabe',
		'recipient' 	=> 	'a.ivanova@onoffice.de',
		'required'	=>	array('Vorname', 'Name', 'Telefon1', 'Email'),
		'checkduplicate'=> false, // optional, if createaddress
	),
	'applicantform'	=> array(
		'inputs' => array(
			'Vorname'		=> 'address',
			'Name'			=> 'address',
			'Zusatz1'		=> 'address',
			'Strasse'		=> 'address',
			'Plz'			=> 'address',
			'Ort'			=> 'address',
			'Telefon1'		=> 'address',
			'Telefax1'		=> 'address',
			'Email'			=> 'address',
			'objektart'		=> 'searchcriteria',
			'nutzungsart'	=> 'searchcriteria',
			'vermarktungsart' => 'searchcriteria',
			'ort'			=> 'searchcriteria',
			'kaufpreis'		=> 'searchcriteria',
		),
		'formtype'	=>	\onOffice\WPlugin\Form::TYPE_INTEREST,
		'language'	=>	'DEU',
		'subject'	=>	'Neuer Interessent über Kontaktformular',
		'recipient' => 	'a.ivanova@onoffice.de',
		'required'	=>	array('Vorname', 'Name', 'Telefon1', 'Email'),
		'checkduplicate'=> false, // optional, if createaddress
	),

	'applicantsearchform'	=> array(
		'inputs' => array(
			'objektart'		=> 'searchcriteria',
			'vermarktungsart'	=> 'searchcriteria',
			'kaufpreis'		=> 'searchcriteria',
			'Umkreis'		=> 'searchcriteria',
		 ),
		'formtype'	=>	\onOffice\WPlugin\Form::TYPE_APPLICANT_SEARCH,
		'language'	=>	'DEU',
		'required'	=>	array(),
		'limitResults'	=>	100,
	),
);


$config['localemap'] = array(
	'de' => 'DEU',
	'de_DE' => 'DEU',
	'en' => 'ENG',
	'en_GB' => 'ENG',
	'en_US' => 'ENG',
	'fallback' => 'DEU',
);


// Search

// http://php.net/manual/de/filter.filters.sanitize.php

$maxPrice = FormPost::getGetValue('preis_bis', FILTER_VALIDATE_INT);
$vermarktungsart = isset( $_GET['vermarktungsart'] ) ? $_GET['vermarktungsart'] : array();
$regionaler_zusatz = FormPost::getGetValue('regionaler_zusatz', FILTER_SANITIZE_STRING);
$hkInNk = FormPost::getGetValue('heizkosten_in_nebenkosten', FILTER_SANITIZE_STRING);

SearchParameters::getInstance()->addAllowedGetParameter('vermarktungsart'); // not validated by FormPost::getGetValue

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

$config['api'] = array();