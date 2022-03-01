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
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\Utility\Redirector;
use onOffice\WPlugin\WP\WPPageWrapper;
use onOffice\tests\RedirectWrapperMocker;

class TestClassRedirectIfOldUrl
	extends \WP_UnitTestCase
{
	/**
	 * @var Redirector
	 */
	private $_pRedirectIfOldUrl;

	/** @var Container */
	private $_pContainer;

	/**
	 * @before
	 */
	public function prepare()
	{
		global $wp;
		$wp->request = 'detail-view/123';
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions(ONOFFICE_DI_CONFIG_PATH);
		$this->_pContainer = $pContainerBuilder->build();
		$this->_pRedirectIfOldUrl = $this->_pContainer->get(Redirector::class);

	}

	/**
	 *
	 */
	public function stestInstance()
	{
		$this->assertInstanceOf(Redirector::class, $this->_pRedirectIfOldUrl);
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\Redirector::getCurrentLink
	 */
	public function testGetCurrentLink()
	{
		$this->assertEquals('http://example.org/detail-view/123', $this->_pRedirectIfOldUrl->getCurrentLink());
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\Redirector::getUri
	 */
	public function testGetUri()
	{
		$this->assertEquals('detail-view/123', $this->_pRedirectIfOldUrl->getUri());
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\Redirector::redirectDetailView
	 */
	public function testRedirectDetailViewSameUrl()
	{
		add_option('onoffice-detail-view-showTitleUrl', true);
		global $wp;
		global $wp_filter;
		$wp->request = 'detail-view/123-show-title-url';
		$this->set_permalink_structure('/%postname%/');
		$savePostBackup = $wp_filter['save_post'];
		$wp_filter['save_post'] = new \WP_Hook;
		$pWPPost = self::factory()->post->create_and_get([
			'post_author' => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title' => 'Detail View',
			'post_type' => 'page',
		]);
		$wp_filter['save_post'] = $savePostBackup;
		$this->assertNull($this->_pRedirectIfOldUrl->redirectDetailView($pWPPost->ID, 123, 'Show Title Url'));
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\Redirector::redirectDetailView
	 */
	public function testRedirectDetailViewSameUrlWithoutOption()
	{
		global $wp;
		$wp->request = 'detail-view/3333';
		$pWPPost = self::factory()->post->create_and_get([
			'post_author' => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title' => 'Detail View',
			'post_type' => 'page',
		]);
		$pLanguageSwitcher = new EstateDetailUrl();
		$wpRedirectWrapperMocker = new RedirectWrapperMocker();
		$wpPageWrapper = new WPPageWrapper();
		$redirectIfOldUrl = new Redirector($pLanguageSwitcher, $wpPageWrapper, $wpRedirectWrapperMocker);
		$this->assertNull($redirectIfOldUrl->redirectDetailView($pWPPost->ID, 1, 'tes post'));
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\Redirector::redirectDetailView
	 */
	public function testRedirectDetailViewNotMatchRule()
	{
		global $wp;
		global $wp_filter;
		$wp->request = 'detail-view/abc-123-show-title-difference-url';
		$this->set_permalink_structure('/%postname%/');
		$savePostBackup = $wp_filter['save_post'];
		$wp_filter['save_post'] = new \WP_Hook;
		$pWPPost = self::factory()->post->create_and_get([
			'post_author' => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title' => 'Detail View',
			'post_type' => 'page',
		]);
		$wp_filter['save_post'] = $savePostBackup;
		$this->assertTrue($this->_pRedirectIfOldUrl->redirectDetailView($pWPPost->ID, 123, 'Show Title Url'));
	}
}
