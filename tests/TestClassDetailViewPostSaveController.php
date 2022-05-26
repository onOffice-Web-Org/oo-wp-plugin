<?php

namespace onOffice\tests;

use onOffice\WPlugin\Controller\DetailViewPostSaveController;
use onOffice\WPlugin\Controller\RewriteRuleBuilder;
use onOffice\WPlugin\DataView\DataDetailView;
use onOffice\WPlugin\DataView\DataDetailViewHandler;
use onOffice\WPlugin\WP\WPPageWrapper;
use WP_UnitTestCase;

class TestClassDetailViewPostSaveController extends WP_UnitTestCase
{
	private $_pDataDetailViewHandler;
	private $_pDataDetailView;
	private $_pDetailViewPostSaveController;
	private $_pWPPageWrapper;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pDataDetailViewHandler = new DataDetailViewHandler();
		$this->_pDataDetailView = new DataDetailView();

		$this->_pWPPageWrapper = $this->getMockBuilder(WPPageWrapper::class)
			->setMethods(['getPageUriByPageId'])
			->getMock();

		$pSubject = new RewriteRuleBuilder($this->_pDataDetailViewHandler, $this->_pWPPageWrapper);
		$this->_pDetailViewPostSaveController = new DetailViewPostSaveController($pSubject);
	}

	public function testReturnNullForTrashStatus() {
		$this->_pDataDetailView->setPageId(13);
		$this->_pDataDetailViewHandler->saveDetailView($this->_pDataDetailView);

		$pWPPost = self::factory()->post->create_and_get([
			'post_author' => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title' => 'Details',
			'post_type' => 'page',
			'post_status' => 'trash',
		]);

		$this->assertNull($this->_pDetailViewPostSaveController->onSavePost($pWPPost->ID));
	}

	public function testOtherShortCodeInContent() {
		$pWPPost = self::factory()->post->create_and_get([
			'post_author' => 1,
			'post_content' => '[oo_estate view="list"]',
			'post_title' => 'Details',
			'post_type' => 'page',
		]);

		$pRevision = self::factory()->post->create_and_get([
			'post_parent' => $pWPPost->ID,
			'post_author' => 1,
			'post_content' => '[oo_estate view="list"]',
			'post_title' => 'Details',
			'post_type' => 'revision',
			'post_status' => 'inherit',
		]);

		$this->_pDataDetailView->setPageId($pRevision->ID);
		$this->_pDataDetailViewHandler->saveDetailView($this->_pDataDetailView);

		$this->_pDetailViewPostSaveController->onSavePost($pWPPost->ID);
		$detailViewOptions = get_option(DataDetailViewHandler::DEFAULT_VIEW_OPTION_KEY);
		$this->assertEquals(0, $detailViewOptions->getPageId());
	}

	public function testShortCodeInMetaKey() {
		$this->_pDataDetailView->setPageId(13);
		$this->_pDataDetailViewHandler->saveDetailView($this->_pDataDetailView);

		$pWPPost = self::factory()->post->create_and_get([
			'post_author' => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title' => 'Test Post',
			'post_type' => 'page',
		]);

		add_post_meta($pWPPost->ID, 'view', '[oo_estate view="detail"]');

		$this->_pWPPageWrapper->method('getPageUriByPageId')
			->with($pWPPost->ID)
			->willReturn('test-post');

		$this->_pDetailViewPostSaveController->onSavePost($pWPPost->ID);

		$detailViewOptions = get_option(DataDetailViewHandler::DEFAULT_VIEW_OPTION_KEY);
		$this->assertEquals($pWPPost->ID, $detailViewOptions->getPageId());
	}
}