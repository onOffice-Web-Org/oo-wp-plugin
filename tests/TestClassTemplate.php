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
use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Template;
use WP_UnitTestCase;

/**
 *
 */

class TestClassTemplate
	extends WP_UnitTestCase
{
	/** @var EstateDetail */
	private $_pEstate = null;


	/**
	 *
	 * @before
	 *
	 */

	public function prepare()
	{
		$this->_pEstate = $this->getMockBuilder(EstateDetail::class)
				->setMethods(['getEstateUnits',
							'estateIterator',
							'getFieldLabel',
							'getEstateContacts',
							'getMovieEmbedPlayers',
							'getEstatePictures',
							'setEstateId',
							'getEstateMovieLinks',
							'getEstatePictureUrl',
							'getEstatePictureTitle',
							'getDocument',
							'getCurrentEstateId',
							'getSimilarEstates',
						])
				->disableOriginalConstructor()
				->getMock();

		$this->_pEstate->method('getEstateUnits')->willReturn('');

		$estateData = [
			'objekttitel' => 'flach begrüntes Grundstück',
			'objektart' => 'Grundstück',
			'objekttyp' => 'Wohnen',
			'vermarktungsart' => 'Kauf',
			'plz' => '52078',
			'ort' => 'Aachen',
			'objektnr_extern' => 'AP001',
			'grundstuecksflaeche' => 'ca. 5.400 m²',
			'kaufpreis' => '80.000,00 €',
			'objektbeschreibung' => 'große Freifläche',
			'lage' => 'Das Grundstück liegt am Waldrand und ist über einen geteerten Feldweg erreichbar.',
			'ausstatt_beschr' => 'teilweise mit einer alten Mauer aus Findlingen umgeben',
			'sonstige_angaben' => 'Vereinbaren sie noch heute einen Besichttgungstermin'
		];

		$pArrayContainerEstateDetail = new ArrayContainerEscape($estateData);

		$this->_pEstate->method('setEstateId')->with(52);
		$this->_pEstate->method('estateIterator')->willReturn($pArrayContainerEstateDetail);

		$this->_pEstate->method('getFieldLabel')->with('objekttitel')->willReturn('Objekttitel');
		$this->_pEstate->method('getFieldLabel')->with('objektart')->willReturn('Objektart');
		$this->_pEstate->method('getFieldLabel')->with('objekttyp')->willReturn('Objekttyp');
		$this->_pEstate->method('getFieldLabel')->with('vermarktungsart')->willReturn('Vermarktungsart');
		$this->_pEstate->method('getFieldLabel')->with('ort')->willReturn('Ort');
		$this->_pEstate->method('getFieldLabel')->with('plz')->willReturn('PLZ');
		$this->_pEstate->method('getFieldLabel')->with('objektnr_extern')->willReturn('externe Objnr');
		$this->_pEstate->method('getFieldLabel')->with('grundstuecksflaeche')->willReturn('Grundstücksgröße');
		$this->_pEstate->method('getFieldLabel')->with('kaufpreis')->willReturn('Kaufpreis');
		$this->_pEstate->method('getFieldLabel')->with('objektbeschreibung')->willReturn('Beschreibung');
		$this->_pEstate->method('getFieldLabel')->with('lage')->willReturn('Lage');
		$this->_pEstate->method('getFieldLabel')->with('ausstatt_beschr')->willReturn('Ausstattung Beschreibung');
		$this->_pEstate->method('getFieldLabel')->with('sonstige_angaben')->willReturn('Sonstige Angaben');

		$contactData = [
			'Name' => 'Petrova Ivanova',
			'Vorname' => 'Ana'];
		$pArrayContainerContact = new ArrayContainerEscape($contactData);
		$this->_pEstate->method('getEstateContacts')->willReturn([$pArrayContainerContact]);

		$movielLink = [
			'url' => 'https://asd.de',
			'title' => 'test movie',
		];

		$this->_pEstate->method('getEstateMovieLinks')->willReturn([$movielLink]);

		$this->_pEstate->method('getMovieEmbedPlayers')->willReturn([]);
		$this->_pEstate->method('getEstatePictures')->willReturn([362]);
		$this->_pEstate->method('getEstatePictureUrl')->with(362)->willReturn(
				'https://image.onoffice.de/smart25/Objekte/index.php?kunde=Ivanova&#038;datensatz=52&#038;filename=Titelbild_362.jpg');
		$this->_pEstate->method('getEstatePictureTitle')->with(362)->willReturn('Fotolia_3286409_Subscription_XL');

		$this->_pEstate->method('getDocument')->willReturn('');
		$this->_pEstate->method('getCurrentEstateId')->willReturn(52);
		$this->_pEstate->method('getSimilarEstates')->willReturn('');
	}


	/**
	 *
	 * @covers \onOffice\WPlugin\Template::render
	 *
	 */

	public function testRender()
	{
		$pTemplate = new Template('oo-wp-plugin/templates.dist/estate/default_detail.php');
		$pTemplate->setEstateList($this->_pEstate);
		$output = $pTemplate->render();
		$this->assertEquals('', $output);
	}
}
