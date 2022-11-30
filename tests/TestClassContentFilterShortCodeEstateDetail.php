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
use onOffice\WPlugin\API\ApiClientException;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\Controller\EstateListEnvironmentDefault;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\EstateDetail;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\API\APIClientActionGeneric;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\Factory\EstateListFactory;
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
			'sonstige_angaben' => 'Vereinbaren Sie noch heute einen Besichtigungstermin'
		];

		$pArrayContainerEstateDetail = new ArrayContainerEscape($estateData);

		$pArrayContainerEstateMap = new ArrayContainerEscape([
			'breitengrad' => '48.8582345',
			'laengengrad' => '2.2944223',
			'showGoogleMap' => '1',
			'virtualAddress' => '0',
		]);

		$this->_pEstate->setEstateId(13);
		$this->_pEstate->method('estateIterator')
			->will($this->onConsecutiveCalls($pArrayContainerEstateDetail, $pArrayContainerEstateMap, false));

		$this->_pEstate->method('getFieldLabel')->with($this->anything())
			->will($this->returnCallback(function(string $field): string {
				return 'label-'.$field;
			}));

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

		$this->_pEstate->method('getEstateMovieLinks')->willReturn([$movielLink]);

		$this->_pEstate->method('getMovieEmbedPlayers')->willReturn([]);
		$this->_pEstate->method('getEstatePictures')->willReturn([362]);
		$this->_pEstate->method('getEstatePictureUrl')
			->with(362)->willReturn('https://image.onoffice.de/smart25/Objekte/index.php?'
				.'kunde=Ivanova&#038;datensatz=52&#038;filename=Titelbild_362.jpg');
		$this->_pEstate->method('getEstatePictureTitle')
			->with(362)->willReturn('Fotolia_3286409_Subscription_XL');

		$this->_pEstate->method('getDocument')->willReturn('');
		$this->_pEstate->method('getCurrentEstateId')->willReturn(52);
		$this->_pEstate->method('getSimilarEstates')->willReturn('');
	}

	// public function testRender()
	// {
	// 	$pTemplateMocker = new TemplateMocker();
	// 	$pDataDetailViewHandler = $this->getMockBuilder(DataDetailViewHandler::class)
	// 		->setMethods(['getDetailView'])
	// 		->getMock();

	// 	$pDataDetailView = new DataDetailView;
	// 	$pDataDetailView->setTemplate('resources/templates/default_detail.php');
	// 	$pDataDetailViewHandler
	// 		->expects($this->once())
	// 		->method('getDetailView')
	// 		->will($this->returnValue($pDataDetailView));

	// 	$pWPQuery = new \WP_Query;
	// 	$pWPQuery->query_vars['estate_id'] = 13;

	// 	$pWPQueryWrapper = $this->getMockBuilder(WPQueryWrapper::class)
	// 		->setMethods(['getWPQuery'])
	// 		->getMock();
	// 	$pWPQueryWrapper->expects($this->once())
	// 		->method('getWPQuery')
	// 		->will($this->returnValue($pWPQuery));
	// 	$pEstateDetailFactory = $this->getMockBuilder(EstateListFactory::class)
	// 		->disableOriginalConstructor()
	// 		->getMock();
	// 	$pEstateDetailFactory->expects($this->once())
	// 		->method('createEstateDetail')
	// 		->will($this->returnValue($this->_pEstate));

	// 	$this->_pContainer->set(Template::class, $pTemplateMocker);
	// 	$this->_pContainer->set(DataDetailView::class, $pDataDetailView);
	// 	$this->_pContainer->set(WPQueryWrapper::class, $pWPQueryWrapper);
	// 	$this->_pContainer->set(DataDetailViewHandler::class, $pDataDetailViewHandler);
	// 	$this->_pContainer->set(EstateListFactory::class, $pEstateDetailFactory);
	// 	$pSubject = $this->_pContainer->get(ContentFilterShortCodeEstateDetail::class);
	// 	$expectedFile = __DIR__.'/resources/templates/TestClassContentFilterShortCodeEstateDetail_expected.txt';
	// 	$this->assertStringEqualsFile($expectedFile, $pSubject->render(['units' => 'test_units']));
	// }


	// /**
	//  *
	//  */
	// public function testGetViewName()
	// {
	// 	$pSubject = $this->_pContainer->get(ContentFilterShortCodeEstateDetail::class);
	// 	$this->assertSame('detail', $pSubject->getViewName());
	// }


	/**
	 *
	 */
	public function testRenderHtmlHelperUserIfEmptyEstateId()
	{
		$pEstateDetailFactory = $this->getMockBuilder(ContentFilterShortCodeEstateDetail::class)
		->setMethods(['getRandomEstateDetail','getEstateLink'])
		->disableOriginalConstructor()
		->getMock();

		$this->assertEquals( '<div><p>You have opened the detail page, but we do not know which estate to show you, because there is no estate ID in the URL. Please go to an estate list and open an estate from there.</p></div>', $pEstateDetailFactory->renderHtmlHelperUserIfEmptyEstateId() );
	}


	/**
	 *
	 */	
	public function getDataEstateDetail(){
		return [
			'id' => 281,
			'type' => 'address',
			'elements' => [
				"objekttitel" => 'abc'
			],
		];
	}

	/**
	 *
	 */
	public function testGetEstateLink()
	{
		$url                  = 'http://example.org/detail/';
		$estateId             = 281;
		$title                = 'abc';
		$pInstance            = $this->_pContainer->get( EstateDetailUrl::class );
		$pEstateDetailFactory = $this->getMockBuilder( ContentFilterShortCodeEstateDetail::class )
		                             ->setMethods( [ 'getPageLink' ] )
		                             ->disableOriginalConstructor()
		                             ->getMock();
		$pEstateDetailFactory->method( 'getPageLink' )->willReturn( $url );
		$fullLink = $pInstance->createEstateDetailLink( $url, $estateId, $title ) . '/';
		$this->assertEquals( $pEstateDetailFactory->getEstateLink( $this->getDataEstateDetail() ), $fullLink );
	}

	/**
	 *
	 */
	public function testRenderHtmlHelperUserLoginIfEmptyEstateId()
	{
		wp_set_current_user( 1 );

		$pEstateDetailFactory = $this->getMockBuilder( ContentFilterShortCodeEstateDetail::class )
		                             ->disableOriginalConstructor()
		                             ->setMethods( [ 'getRandomEstateDetail', 'getEstateLink', 'is_user_logged_in' ] )
		                             ->getMock();

		$pEstateDetailFactory->method( 'getRandomEstateDetail' )->willReturn( $this->getDataEstateDetail() );
		$pEstateDetailFactory->method( 'getEstateLink' )->willReturn( 'http://example.org/detail/123649/' );
		$pEstateDetailFactory->method( 'is_user_logged_in' )->willReturn( is_user_logged_in() );

		$this->assertTrue( $pEstateDetailFactory->is_user_logged_in() );
		$this->assertEquals( '<div><p>You have opened the detail page, but we do not know which estate to show you, because there is no estate ID in the URL. Please go to an estate list and open an estate from there.</p><p>Since you are logged in, here is a link to a random estate so that you can preview the detail page:</p><a href=http://example.org/detail/123649/>abc</a></div>',
			$pEstateDetailFactory->renderHtmlHelperUserIfEmptyEstateId() );
	}

}