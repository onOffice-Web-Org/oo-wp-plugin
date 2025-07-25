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

use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Controller\EstateListEnvironment;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataView;
use onOffice\WPlugin\EstateDetail;
use WP_UnitTestCase;

class TestTemplateEstateDefaultDetail
	extends WP_UnitTestCase
{
	/** @var EstateDetail */
	private $_pEstate = null;

	/** @var EstateListEnvironment */
	private $_pEnvironment = null;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pDataView = new DataDetailView();

		$this->_pEstate = $this->getMockBuilder(EstateDetail::class)
			->setMethods([
				'getEstateUnits',
				'estateIterator',
				'getFieldLabel',
				'getEstateContacts',
				'getMovieEmbedPlayers',
				'getEstatePictures',
				'setEstateId',
				'getEstateMovieLinks',
				'getShortCodeForm',
				'getEstatePictureUrl',
				'getEstatePictureTitle',
				'getEstatePictureValues',
				'getDocument',
				'getCurrentEstateId',
				'getSimilarEstates',
				'getViewRestrict',
				'getEstateLinks',
				'getLinkEmbedPlayers',
				'getDetailView',
				'getTotalCostsData',
				'getShowEnergyCertificate',
				'getPermittedValues',
				'getRawValues',
			])
			->setConstructorArgs([$pDataView])
			->getMock();

		$this->_pEstate->method('getEstateUnits')->willReturn('Estate Units here');

		$estateData = [
			'objekttitel' => 'flach begrüntes Grundstück',
			'objektart' => 'Grundstück',
			'objekttyp' => 'Wohnen',
			'vermarktungsart' => 'Kauf',
			'vermarktungsstatus' => 'zzz',
			'plz' => '52078',
			'ort' => 'Aachen',
			'objektnr_extern' => 'AP001',
			'grundstuecksflaeche' => 'ca. 5.400 m²',
			'kaufpreis' => '80.000,00 €',
			'objektbeschreibung' => 'große Freifläche',
			'lage' => 'Das Grundstück liegt am Waldrand und ist über einen geteerten Feldweg erreichbar.',
			'ausstatt_beschr' => 'teilweise mit einer alten Mauer aus Findlingen umgeben',
			'sonstige_angaben' => 'Vereinbaren sie noch heute einen Besichtigungstermin',
			'MPAreaButlerUrlWithAddress' => 'areabutler.de',
			'energyClass' => 'A',
			'baujahr' => 'testField',
			'dreizeiler' => 'Tolle Immobilie in 3 Zeilen',
		];

		$totalCostsData = [
			'kaufpreis' => [
				'raw' => 123456.56,
				'default' => '123.456,56 €'
			],
			'bundesland' => [
				'raw' => 4321,
				'default' => '4.321 €'
			],
			'aussen_courtage' => [
				'raw' => 22222,
				'default' => '22.222 €'
			],
			'notary_fees' => [
				'raw' => 1852,
				'default' => '1.852 €'
			],
			'land_register_entry' => [
				'raw' => 617,
				'default' => '617 €'
			],
			'total_costs' => [
				'raw' => 152468.56,
				'default' => '152.468,56 €'
			]
		];

		$estateDataRaw = [
			52 => [
				'id' => 52,
				'type' => 'estate',
				'elements' => [
					'energieausweistyp' => 'Endenergiebedarf',
					'energyClass' => 'A',
				]
			]
		];
		$pArrayContainerEstateDetail = new ArrayContainerEscape($estateData);
		$pArrayContainerEstateDetailRaw = new ArrayContainerEscape($estateDataRaw);

		$this->_pEstate->setEstateId(52);
		$this->_pEstate->method('estateIterator')
			->will($this->onConsecutiveCalls($pArrayContainerEstateDetail, false));
		$this->_pEstate->method('getFieldLabel')->with($this->anything())
			->will($this->returnCallback(function(string $field): string {
				return 'label-'.$field;
			}));
		$this->_pEstate->method('getTotalCostsData')->willReturn($totalCostsData);
		$this->_pEstate->method('getRawValues')
			->will($this->onConsecutiveCalls($pArrayContainerEstateDetailRaw, false));

		$contactData = [
			'Name' => 'Parker',
			'Vorname' => 'Peter',
		];
		$pArrayContainerContact = new ArrayContainerEscape($contactData);
		$this->_pEstate->method('getEstateContacts')->willReturn([$pArrayContainerContact]);

		$movielLink = [
			'url' => 'https://asd.de',
			'title' => 'test movie',
		];

		$oguloLink = [
			'url' => 'https://ogulo.de',
			'title' => 'test ogulo link',
		];

		$this->_pEstate->method('getEstateMovieLinks')->willReturn([$movielLink]);
		$this->_pEstate->method('getShortCodeForm')->willReturn('Contact Form here');
		$this->_pEstate->method('getMovieEmbedPlayers')->willReturn([]);
		$this->_pEstate->method('getEstatePictures')->willReturn([362]);
		$this->_pEstate->method('getEstatePictureUrl')->with(362)
			->willReturn('https://image.onoffice.de/smart25/Objekte/index.php?kunde=Ivanova&'
			.'#038;datensatz=52&#038;filename=Titelbild_362.jpg');
		$this->_pEstate->method('getEstatePictureTitle')->with(362)
			->willReturn('Fotolia_3286409_Subscription_XL');
		$this->_pEstate->method('getEstatePictureValues')->with(362)
			->willReturn([
				'id' => 362,
				'url' => 'https://image.onoffice.de/smart25/Objekte/index.php?kunde=Ivanova&filename=Titelbild_362.jpg',
				'title' => 'Fotolia_3286409_Subscription_XL',
				'type' => \onOffice\WPlugin\Types\ImageTypes::TITLE
			]);
		$this->_pEstate->method('getDocument')->willReturn('Document here');
		$this->_pEstate->method('getCurrentEstateId')->willReturn(52);
		$this->_pEstate->method('getSimilarEstates')->willReturn('Similar Estates here');
		$this->_pEstate->method('getViewRestrict')->willReturn(true);
		$this->_pEstate->method('getEstateLinks')->willReturn([$oguloLink]);
		$this->_pEstate->method('getLinkEmbedPlayers')->willReturn([]);
		$this->_pEstate->method('getDetailView')->willReturn('1');
		$this->_pEstate->method('getShowEnergyCertificate')->willReturn(true);
		$this->_pEstate->method('getPermittedValues')->willReturn(['A', 'B', 'C']);
	}

	/**
	 * @covers \onOffice\WPlugin\Template::render
	 */
	public function testRender()
	{
		$pTemplate = (new TemplateMocker(getcwd()))
			->withTemplateName('templates.dist/estate/default_detail.php')
			->withEstateList($this->_pEstate);
		$output = $pTemplate->render();
		file_put_contents(__DIR__.'/resources/templates/output_default_detail.html', $output);
		$this->assertStringEqualsFile
			(__DIR__.'/resources/templates/output_default_detail.html', $output);
	}
}
