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

declare ( strict_types=1 );

namespace onOffice\tests;

use DI\ContainerBuilder;
use onOffice\SDK\onOfficeSDK;
use onOffice\WPlugin\Controller\DetailViewPostSaveController;
use onOffice\WPlugin\Controller\EstateListEnvironmentDefault;
use onOffice\WPlugin\Controller\RewriteRuleBuilder;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\EstateList;
use onOffice\WPlugin\SDKWrapper;
use onOffice\WPlugin\WP\WPPageWrapper;
use WP_UnitTestCase;

/**
 *
 * @url http://www.onoffice.de
 * @copyright 2003-2017, onOffice(R) GmbH
 *
 * DO NOT MOVE OR RENAME - NAME AND/OR NAMESPACE MAY BE USED IN SERIALIZED DATA
 *
 */
class TestClassDetailViewPostSaveController extends WP_UnitTestCase
{
	/**
	 * @var DataDetailViewHandler
	 */
	private $_pDataDetailViewHandler;

	/**
	 * @var DataDetailView
	 */
	private $_pDataDetailView;

	/**
	 * @var DetailViewPostSaveController
	 */
	private $_pDetailViewPostSaveController;

	/**
	 * @var WPPageWrapper
	 */
	private $_pWPPageWrapper;

	/**
	 *
	 */
	private $_wp_filter;

	/**
	 * @before
	 */
	public function prepare()
	{
		global $wp_filter;
		$this->_wp_filter              = $wp_filter;
		$this->_pDataDetailViewHandler = new DataDetailViewHandler();
		$this->_pDataDetailView        = $this->_pDataDetailViewHandler->getDetailView();

		$this->_pWPPageWrapper = $this->getMockBuilder( WPPageWrapper::class )
		                              ->setMethods( [ 'getPageUriByPageId' ] )
		                              ->getMock();

		$pSubject = $this->getMockBuilder( RewriteRuleBuilder::class )
		                 ->setMethods( [ 'addDynamicRewriteRules' ] )
		                 ->setConstructorArgs( [ $this->_pDataDetailViewHandler, $this->_pWPPageWrapper ] )
		                 ->getMock();
		$pSubject->method( 'addDynamicRewriteRules' );
		$this->_pDetailViewPostSaveController = new DetailViewPostSaveController( $pSubject );

	}

	public function testReturnNullForTrashStatus()
	{
		$this->_pDataDetailView->setPageId( 13 );
		$this->_pDataDetailViewHandler->saveDetailView( $this->_pDataDetailView );

		$this->set_permalink_structure( '/%postname%/' );
		$savePostBackup                = $this->_wp_filter['save_post'];
		$this->_wp_filter['save_post'] = new \WP_Hook;

		$pWPPost                       = self::factory()->post->create_and_get( [
			'post_author'  => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title'   => 'Details',
			'post_type'    => 'page',
			'post_status'  => 'trash',
		] );
		$this->_wp_filter['save_post'] = $savePostBackup;
		$this->assertNull( $this->_pDetailViewPostSaveController->onSavePost( $pWPPost->ID ) );
	}
//
//	public function testOtherShortCodeInContent()
//	{
//		$this->set_permalink_structure( '/%postname%/' );
//		$savePostBackup                = $this->_wp_filter['save_post'];
//		$this->_wp_filter['save_post'] = new \WP_Hook;
//
//		$pWPPost                       = self::factory()->post->create_and_get( [
//			'post_author'  => 1,
//			'post_content' => '[oo_estate view="list"]',
//			'post_title'   => 'Details',
//			'post_type'    => 'page',
//		] );
//		$this->_wp_filter['save_post'] = $savePostBackup;
//
//		$pRevision = self::factory()->post->create_and_get( [
//			'post_parent'  => $pWPPost->ID,
//			'post_author'  => 1,
//			'post_content' => '[oo_estate view="list"]',
//			'post_title'   => 'Details',
//			'post_type'    => 'revision',
//			'post_status'  => 'inherit',
//		] );
//
//		$this->_pDataDetailView->setPageId( $pRevision->ID );
//		$this->_pDataDetailViewHandler->saveDetailView( $this->_pDataDetailView );
//
//		$this->_pDetailViewPostSaveController->onSavePost( $pWPPost->ID );
//		$detailViewOptions = get_option( DataDetailViewHandler::DEFAULT_VIEW_OPTION_KEY );
//		$this->assertEquals( 0, $detailViewOptions->getPageId() );
//	}
//
//	public function testShortCodeInMetaKey()
//	{
//		$this->_pDataDetailView->setPageId( 13 );
//		$this->_pDataDetailViewHandler->saveDetailView( $this->_pDataDetailView );
//
//		$this->set_permalink_structure( '/%postname%/' );
//		$savePostBackup                = $this->_wp_filter['save_post'];
//		$this->_wp_filter['save_post'] = new \WP_Hook;
//		$pWPPost                       = self::factory()->post->create_and_get( [
//			'post_author'  => 1,
//			'post_content' => '[oo_estate view="detail"]',
//			'post_title'   => 'Test Post',
//			'post_type'    => 'page',
//		] );
//
//		add_post_meta( $pWPPost->ID, 'view', '[oo_estate view="detail"]' );
//		$this->_wp_filter['save_post'] = $savePostBackup;
//		$this->_pWPPageWrapper->method( 'getPageUriByPageId' )
//		                      ->with( $pWPPost->ID )
//		                      ->willReturn( 'test-post' );
//
//		$this->_pDetailViewPostSaveController->onSavePost( $pWPPost->ID );
//
//		$detailViewOptions = get_option( DataDetailViewHandler::DEFAULT_VIEW_OPTION_KEY );
//		$this->assertEquals( $pWPPost->ID, $detailViewOptions->getPageId() );
//	}

	/**
	 *
	 */

	public function testGetEstateLink()
	{
		global $wp_filter;
		$this->_pSDKWrapperMocker = new SDKWrapperMocker();

		$dataReadEstateFormatted = json_decode
		(file_get_contents(__DIR__.'/resources/ApiResponseReadEstatesPublishedENG.json'), true);
		$responseReadEstate = $dataReadEstateFormatted['response'];
		$parametersReadEstate = $dataReadEstateFormatted['parameters'];
		$dataReadEstateRaw = json_decode
		(file_get_contents(__DIR__.'/resources/ApiResponseReadEstatesPublishedENGRaw.json'), true);
		$responseReadEstateRaw = $dataReadEstateRaw['response'];
		$parametersReadEstateRaw = $dataReadEstateRaw['parameters'];
		$responseGetIdsFromRelation = json_decode
		(file_get_contents(__DIR__.'/resources/ApiResponseGetIdsFromRelation.json'), true);
		$responseGetEstatePictures = json_decode
		(file_get_contents(__DIR__.'/resources/ApiResponseGetEstatePictures.json'), true);

		$this->_pSDKWrapperMocker->addResponseByParameters
		(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parametersReadEstate, null, $responseReadEstate);
		$this->_pSDKWrapperMocker->addResponseByParameters
		(onOfficeSDK::ACTION_ID_READ, 'estate', '', $parametersReadEstateRaw, null, $responseReadEstateRaw);
		$this->_pSDKWrapperMocker->addResponseByParameters
		(onOfficeSDK::ACTION_ID_GET, 'idsfromrelation', '', [
			'parentids' => [15, 1051, 1082, 1193, 1071],
			'relationtype' => 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:contactPerson'
		], null, $responseGetIdsFromRelation);

		$this->_pSDKWrapperMocker->addResponseByParameters
		(onOfficeSDK::ACTION_ID_GET, 'estatepictures', '', [
			'estateids' => [15,1051,1082,1193,5448],
			'categories' => ['Titelbild', "Foto"],
			'language' => 'ENG'
		], null, $responseGetEstatePictures);

		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$_pContainer = $pContainerBuilder->build();
		$_pContainer->set(SDKWrapper::class, $this->_pSDKWrapperMocker);
		$_pEnvironment = $this->getMockBuilder(EstateListEnvironmentDefault::class)
		                            ->setConstructorArgs([$_pContainer])
		                            ->setMethods([
			                            'getDefaultFilterBuilder',
			                            'getGeoSearchBuilder',
			                            'getEstateStatusLabel',
			                            'setDefaultFilterBuilder',
			                            'getEstateFiles',
			                            'getFieldnames',
			                            'getAddressList',
			                            'getEstateUnitsByName',
			                            'getDataDetailViewHandler',
		                            ])
		                            ->getMock();
		$pEstateList = new EstateList($this->_pDataDetailView, $_pEnvironment);

		$pDataDetailView = new DataDetailView();
		$pDataDetailView->setPageId(0);
		$pDataDetailViewHandler = $this->getMockBuilder(DataDetailViewHandler::class)
		                               ->disableOriginalConstructor()
		                               ->setMethods(['getDetailView'])
		                               ->getMock();
		$pDataDetailViewHandler->method('getDetailView')->willReturn($pDataDetailView);
		$_pEnvironment->method('getDataDetailViewHandler')->willReturn($pDataDetailViewHandler);

		$this->assertEquals('#', $pEstateList->getEstateLink());

		$this->set_permalink_structure('/%postname%/');
		$savePostBackup = $wp_filter['save_post'];
		$wp_filter['save_post'] = new \WP_Hook;
		$pWPPost = self::factory()->post->create_and_get([
			'post_author'  => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title'   => 'Details',
			'post_type'    => 'page',
			'post_status'  => 'trash',
		]);
		$wp_filter['save_post'] = $savePostBackup;
		$pDataDetailView->setPageId($pWPPost->ID);

		// slash missing at the end, which WP inserts in production
		$this->assertNull( $this->_pDetailViewPostSaveController->onSavePost( $pWPPost->ID ) );
	}
}