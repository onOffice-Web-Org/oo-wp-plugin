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
use onOffice\WPlugin\Controller\ContentFilter\ContentFilterShortCodeEstateList;
use onOffice\WPlugin\Controller\EstateListEnvironment;
use onOffice\WPlugin\DataView\DataListView;
use onOffice\WPlugin\DataView\DataListViewFactory;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\Factory\EstateListFactory;
use onOffice\WPlugin\Field\Collection\FieldsCollectionBuilderShort;
use onOffice\WPlugin\Template;

class TestClassContentFilterShortCodeEstateList
	extends \WP_UnitTestCase
{
	/** @var Container */
	private $_pContainer;

	/**
	 * @before
	 */
	public function prepare()
	{
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();

		$pDataListViewFactory = $this->getMockBuilder(DataListViewFactory::class)
			->setMethods(['getListViewByName'])
			->getMock();
		$pDataListView = new DataListView(13, 'test_view_list');
		$pDataListView->setTemplate('default_list.php');
		$pDataListViewFactory->method('getListViewByName')->with('test_view_list')
			->will($this->returnValue($pDataListView));

		$pFieldsCollectionBuilderShort = $this->getMockBuilder(FieldsCollectionBuilderShort::class)
			->disableOriginalConstructor()
			->setMethods(['addFieldsAddressEstate'])
			->getMock();

		$pEstateDetailFactory = $this->getMockBuilder(EstateListFactory::class)
			->disableOriginalConstructor()
			->setMethods(['createEstateList'])
			->getMock();
		$pEstateList = $this->generateEstateList($pDataListView);
		$pEstateDetailFactory->method('createEstateList')->will($this->returnValue($pEstateList));

		$pTemplate = new TemplateMocker('', __DIR__.'/resources/templates');

		$this->_pContainer->set(DataListViewFactory::class, $pDataListViewFactory);
		$this->_pContainer->set(FieldsCollectionBuilderShort::class, $pFieldsCollectionBuilderShort);
		$this->_pContainer->set(EstateListFactory::class, $pEstateDetailFactory);
		$this->_pContainer->set(Template::class, $pTemplate);
	}

	/**
	 * @param DataListView $pDataListView
	 * @return EstateList
	 */
	private function generateEstateList(DataListView $pDataListView): EstateList
	{
		$pEstateListEnvironment = $this->getMockBuilder(EstateListEnvironment::class)
			->getMock();
		$pEstate = $this->getMockBuilder(EstateList::class)
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
				'loadEstates',
			])
			->setConstructorArgs([$pDataListView, $pEstateListEnvironment])
			->getMock();

		$pEstate->method('getEstateUnits')->willReturn('');

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
			'sonstige_angaben' => 'Vereinbaren sie noch heute einen Besichtigungstermin'
		];

		$pArrayContainerEstateDetail = new ArrayContainerEscape($estateData);

		$pEstate->setEstateId(13);
		$pEstate->method('estateIterator')->will($this->onConsecutiveCalls($pArrayContainerEstateDetail, false));
		$pEstate->method('getFieldLabel')->with($this->anything())
			->will($this->returnCallback(function(string $field): string {
				return 'label-'.$field;
			}));

		$pArrayContainerContact = new ArrayContainerEscape([
			'Name' => 'Petrova',
			'Vorname' => 'Ana',
		]);
		$pEstate->method('getEstateContacts')->willReturn([$pArrayContainerContact]);
		$pEstate->method('getEstateMovieLinks')->willReturn([[
			'url' => 'https://asd.de',
			'title' => 'test movie',
		]]);

		$pEstate->method('getMovieEmbedPlayers')->willReturn([]);
		$pEstate->method('getEstatePictures')->willReturn([362]);
		$pEstate->method('getEstatePictureUrl')->with(362)->willReturn(
			'https://image.onoffice.de/smart25/Objekte/index.php?kunde=Ivanova&#038;datensatz=52&#038;filename=Titelbild_362.jpg');
		$pEstate->method('getEstatePictureTitle')->with(362)->willReturn('Fotolia_3286409_Subscription_XL');

		$pEstate->method('getDocument')->willReturn('');
		$pEstate->method('getCurrentEstateId')->willReturn(52);
		$pEstate->method('getSimilarEstates')->willReturn('');
		return $pEstate;
	}

	public function testRender()
	{
		$pSubject = $this->_pContainer->get(ContentFilterShortCodeEstateList::class);
		$result = $pSubject->render(['view' => 'test_view_list', 'units' => '']);
		$expectedFile = __DIR__.'/resources/templates/TestClassContentFilterShortCodeEstateList_expected.txt';
		$this->assertStringEqualsFile($expectedFile, $result);
	}
}