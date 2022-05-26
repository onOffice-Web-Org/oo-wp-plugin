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

use onOffice\WPlugin\Controller\DetailViewPostSaveController;
use onOffice\WPlugin\Controller\RewriteRuleBuilder;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
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
	 * @before
	 */
	public function prepare()
	{
		$this->_pDataDetailViewHandler = new DataDetailViewHandler();
		$this->_pDataDetailView        = new DataDetailView();

		$this->_pWPPageWrapper = $this->getMockBuilder( WPPageWrapper::class )
		                              ->setMethods( [ 'getPageUriByPageId' ] )
		                              ->getMock();

		$pSubject = new RewriteRuleBuilder( $this->_pDataDetailViewHandler,
			$this->_pWPPageWrapper );

		$this->_pDetailViewPostSaveController = new DetailViewPostSaveController( $pSubject );
	}

	public function testReturnNullForTrashStatus()
	{
		$this->_pDataDetailView->setPageId( 13 );
		$this->_pDataDetailViewHandler->saveDetailView( $this->_pDataDetailView );

		$pWPPost = self::factory()->post->create_and_get( [
			'post_author'  => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title'   => 'Details',
			'post_type'    => 'page',
			'post_status'  => 'trash',
		] );

		$this->assertNull( $this->_pDetailViewPostSaveController->onSavePost( $pWPPost->ID ) );
	}

	public function testOtherShortCodeInContent()
	{
		$pWPPost = self::factory()->post->create_and_get( [
			'post_author'  => 1,
			'post_content' => '[oo_estate view="list"]',
			'post_title'   => 'Details',
			'post_type'    => 'page',
		] );

		$pRevision = self::factory()->post->create_and_get( [
			'post_parent'  => $pWPPost->ID,
			'post_author'  => 1,
			'post_content' => '[oo_estate view="list"]',
			'post_title'   => 'Details',
			'post_type'    => 'revision',
			'post_status'  => 'inherit',
		] );

		$this->_pDataDetailView->setPageId( $pRevision->ID );
		$this->_pDataDetailViewHandler->saveDetailView( $this->_pDataDetailView );

		$this->_pDetailViewPostSaveController->onSavePost( $pWPPost->ID );
		$detailViewOptions = get_option( DataDetailViewHandler::DEFAULT_VIEW_OPTION_KEY );
		$this->assertEquals( 0, $detailViewOptions->getPageId() );
	}

	public function testShortCodeInMetaKey()
	{
		$this->_pDataDetailView->setPageId( 13 );
		$this->_pDataDetailViewHandler->saveDetailView( $this->_pDataDetailView );

		$pWPPost = self::factory()->post->create_and_get( [
			'post_author'  => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title'   => 'Test Post',
			'post_type'    => 'page',
		] );

		add_post_meta( $pWPPost->ID, 'view', '[oo_estate view="detail"]' );

		$this->_pWPPageWrapper->method( 'getPageUriByPageId' )
		                      ->with( $pWPPost->ID )
		                      ->willReturn( 'test-post' );

		$this->_pDetailViewPostSaveController->onSavePost( $pWPPost->ID );

		$detailViewOptions = get_option( DataDetailViewHandler::DEFAULT_VIEW_OPTION_KEY );
		$this->assertEquals( $pWPPost->ID, $detailViewOptions->getPageId() );
	}
}