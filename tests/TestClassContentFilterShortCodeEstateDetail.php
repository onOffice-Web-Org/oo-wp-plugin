<?php

/**
 *
 *    Copyright (C) 2020 onOffice GmbH
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

use DI\Container;
use DI\ContainerBuilder;
use onOffice\WPlugin\ArrayContainerEscape;
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeEstateDetail;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\EstateDetail;
use onOffice\WPlugin\Factory\EstateDetailFactory;
use onOffice\WPlugin\Template;
use onOffice\WPlugin\WP\WPQueryWrapper;

class TestClassContentFilterShortCodeEstateDetail
	extends \WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/** @var EstateDetail */
	private $_pEstate;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$this->generateEstateDetail();
	}

	private function generateEstateDetail()
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
				'loadSingleEstate',
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

		$this->_pEstate->setEstateId(13);
		$this->_pEstate->method('estateIterator')->will($this->onConsecutiveCalls($pArrayContainerEstateDetail, false));

		$this->_pEstate->method('getFieldLabel')->with($this->anything())
			->will($this->returnCallback(function(string $field): string {
				return 'label-'.$field;
			}));

		$contactData = [
			'Name' => 'Petrova',
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

	public function testRender()
	{
		$pTemplateMocker = new TemplateMocker('');
		$pDataDetailViewHandler = $this->getMockBuilder(DataDetailViewHandler::class)
			->setMethods(['getDetailView'])
			->getMock();

		$pDataDetailView = new DataDetailView;
		$pDataDetailView->setTemplate('resources/templates/default_detail.php');
		$pDataDetailViewHandler
			->expects($this->once())
			->method('getDetailView')
			->will($this->returnValue($pDataDetailView));

		$pWPQuery = new \WP_Query;
		$pWPQuery->query_vars['estate_id'] = 13;

		$pWPQueryWrapper = $this->getMockBuilder(WPQueryWrapper::class)
			->setMethods(['getWPQuery'])
			->getMock();
		$pWPQueryWrapper->expects($this->once())
			->method('getWPQuery')
			->will($this->returnValue($pWPQuery));
		$pEstateDetailFactory = $this->getMockBuilder(EstateDetailFactory::class)
			->disableOriginalConstructor()
			->getMock();
		$pEstateDetailFactory->expects($this->once())
			->method('createEstateDetail')
			->will($this->returnValue($this->_pEstate));

		$this->_pContainer->set(Template::class, $pTemplateMocker);
		$this->_pContainer->set(DataDetailView::class, $pDataDetailView);
		$this->_pContainer->set(WPQueryWrapper::class, $pWPQueryWrapper);
		$this->_pContainer->set(DataDetailViewHandler::class, $pDataDetailViewHandler);
		$this->_pContainer->set(EstateDetailFactory::class, $pEstateDetailFactory);
		$pSubject = $this->_pContainer->get(ContentFilterShortCodeEstateDetail::class);
		$expectedFile = __DIR__.'/resources/templates/TestClassContentFilterShortCodeEstateDetail_expected.txt';
		$this->assertStringEqualsFile($expectedFile, $pSubject->render(['units' => 'test_units']));
	}
}