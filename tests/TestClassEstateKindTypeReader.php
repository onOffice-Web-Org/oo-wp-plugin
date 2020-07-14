<?php

/**
 *
 *    Copyright (C) 2019 onOffice GmbH
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare (strict_types=1);

namespace onOffice\tests;

use Generator;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Field\DistinctFieldsHandler;
use onOffice\WPlugin\Field\DistinctFieldsHandlerModelBuilder;
use onOffice\WPlugin\Field\EstateKindTypeReader;
use onOffice\WPlugin\Filter\DefaultFilterBuilderFactory;
use onOffice\WPlugin\Record\EstateIdRequestGuard;
use WP_UnitTestCase;

class TestClassEstateKindTypeReader
	extends WP_UnitTestCase
{
	const EXPECTED_RESULT = [
		'zimmer' => [
			'zimmer',
		],
		'haus' => [
			'bungalow',
			'reihenhaus',
			'reihenend',
			'reihenmittel',
			'reiheneck',
			'doppelhaushaelfte',
			'einfamilienhaus',
			'stadthaus',
			'villa',
			'resthof',
			'bauernhaus',
			'landhaus',
			'schloss',
			'zweifamilienhaus',
			'mehrfamilienhaus',
			'ferienhaus',
			'berghuette',
			'chalet',
			'strandhaus',
			'wohn_und_geschaeftshaus',
			'geschaeftshaus',
			'wohnanlage',
		],
		'wohnung' => [
			'rohdachboden',
			'hochparterre',
			'dachgeschoss',
			'maisonette',
			'loft-studio-atelier',
			'penthouse',
			'terrassen',
			'etage',
			'erdgeschoss',
			'souterrain',
		],
		'grundstueck' => [
			'seeliegenschaft',
			'gewerbepark',
			'wohnen',
			'gewerbe',
			'industrie',
			'land_forstwirtschaft',
			'freizeit',
			'gemischt',
			'sondernutzung',
		],
		'buero_praxen' => [
			'praxisflaeche',
			'buerozentrum',
			'loft',
			'atelier',
			'bueroetage',
			'buero_und_lager',
			'praxisetage',
			'praxishaus',
			'gewerbezentrum',
			'bueroflaeche',
			'buerohaus',
			'praxis',
			'ausstellungsflaeche',
		],
		'einzelhandel' => [
			'ausstellungsflaeche',
			'factory_outlet',
			'kaufhaus',
			'kiosk',
			'sb_markt',
			'verkaufssflaeche',
			'verkaufshalle',
			'ladenlokal',
			'einzelhandelsladen',
			'verbrauchermarkt',
			'einkaufszentrum',
			'sonstige',
		],
		'gastgewerbe' => [
			'einraumlokal',
			'raucherlokal',
			'barbetrieb',
			'cafe',
			'diskothek',
			'gaestehaus',
			'gaststaette',
			'hotelanwesen',
			'hotel_garni',
			'restaurant',
			'gastronomie',
			'gastronomie_und_wohnung',
			'pensionen',
			'hotels',
			'weitere_beherbergungsbetriebe',
		],
		'hallen_lager_prod' => [
			'ausstellungsflaeche',
			'industriehalle',
			'industriehalle_und_freiflaeche',
			'kuehlhaus',
			'kuehlregallager',
			'lager_mit_freiflaeche',
			'lagerflaeche',
			'lagerhalle',
			'speditionslager',
			'halle',
			'lager',
			'produktion',
			'werkstatt',
			'hochregallager',
			'service',
			'freiflaechen',
			'industrie',
		],
		'land_und_forstwirtschaft' => [
			'jagdrevier',
			'anwesen',
			'landwirtschaftliche_betriebe',
			'bauernhof',
			'aussiedlerhof',
			'viehwirtschaft',
			'jagd_und_forstwirtschaft',
			'teich_und_fischwirtschaft',
			'scheunen',
			'reiterhoefe',
			'sonstige_landwirtschaftsimmobilien',
		],
		'freizeitimmbilien_gewerblich' => [
			'freizeitanlage',
			'sportanlage',
			'vergnuegungsparks_und_center',
		],
		'sonstige' => [
			'parkhaus',
			'tankstelle',
			'sonstige',
			'gewerbeeinheit',
			'gewerbeanwesen',
		],
	];

	/**
	 * @return EstateKindTypeReader
	 */
	private function buildSubject(): EstateKindTypeReader
	{
		$pSDKWrapperMocker = new SDKWrapperMocker;
		$responseEstatesOThisField = file_get_contents
			(__DIR__.'/resources/Field/ApiResponseGetEstateCategories.json');
		$responseEstatesFields = json_decode($responseEstatesOThisField, true);
		$pSDKWrapperMocker->addResponseByParameters(onOfficeSDK::ACTION_ID_GET, 'estateCategories',
			'', [], null, $responseEstatesFields);

		$pApiClientAction = new APIClientActionGeneric($pSDKWrapperMocker, '', '');
		return new EstateKindTypeReader($pApiClientAction);
	}

	public function testRead()
	{
		$pSubject = $this->buildSubject();
		$this->assertEquals(self::EXPECTED_RESULT, $pSubject->read());
	}
}