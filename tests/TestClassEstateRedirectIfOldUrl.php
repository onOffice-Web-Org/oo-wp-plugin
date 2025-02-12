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
use onOffice\WPlugin\Controller\Redirector\EstateRedirector;

class TestClassEstateRedirectIfOldUrl
	extends \WP_UnitTestCase
{
	/**
	 * @var EstateRedirector
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
		$wp->request       = 'detail-view/123';
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions( ONOFFICE_DI_CONFIG_PATH );
		$this->_pContainer        = $pContainerBuilder->build();
		$this->_pRedirectIfOldUrl = $this->_pContainer->get( EstateRedirector::class );
		$_SERVER['REQUEST_URI'] = '/detail-view/123';
	}

	/**
	 *
	 */
	public function stestInstance()
	{
		$this->assertInstanceOf( EstateRedirector::class, $this->_pRedirectIfOldUrl );
	}

	/**
	 * @covers \onOffice\WPlugin\Controller\Redirector\EstateRedirector::redirectDetailView
	 */
	public function testRedirectDetailViewSameUrl()
	{
		add_option( 'onoffice-detail-view-showTitleUrl', true );
		global $wp;
		global $wp_filter;
		$wp->request = 'detail-view/123-show-title-url';
		$_SERVER['REQUEST_URI'] = '/detail-view/123-show-title-url';
		$this->set_permalink_structure( '/%postname%/' );
		$savePostBackup         = $wp_filter['save_post'];
		$wp_filter['save_post'] = new \WP_Hook;
		$pWPPost                = self::factory()->post->create_and_get( [
			'post_author'  => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title'   => 'Detail View',
			'post_type'    => 'page',
		] );
		$wp_filter['save_post'] = $savePostBackup;
		$this->assertNull($this->_pRedirectIfOldUrl->redirectDetailView(123, 'Show Title Url', true));
	}

	/**
	 * @covers \onOffice\WPlugin\Controller\Redirector\EstateRedirector::redirectDetailView
	 */
	public function testRedirectDetailViewNotMatchRule()
	{
		global $wp;
		global $wp_filter;
		$wp->request = 'detail-view/abc-123-show-title-difference-url';
		$_SERVER['REQUEST_URI'] = '/detail-view/abc-123-show-title-difference-url';
		$this->set_permalink_structure( '/%postname%/' );
		$savePostBackup         = $wp_filter['save_post'];
		$wp_filter['save_post'] = new \WP_Hook;
		$pWPPost                = self::factory()->post->create_and_get( [
			'post_author'  => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title'   => 'Detail View',
			'post_type'    => 'page',
		] );
		$wp_filter['save_post'] = $savePostBackup;
		$this->assertTrue($this->_pRedirectIfOldUrl->redirectDetailView(123, 'Show Title Url', true));
	}


	/**
	 * @return void
	 */

	public function testRedirectDetailViewWithParrentPageAndUrlNotMatchRule()
	{
		global $wp;
		global $wp_filter;
		$wp->request = 'e1/detail-view/abc-123-show-title-difference-url';
		$_SERVER['REQUEST_URI'] = '/e1/detail-view/abc-123-show-title-difference-url';
		$this->set_permalink_structure( '/%postname%/' );
		$savePostBackup         = $wp_filter['save_post'];
		$wp_filter['save_post'] = new \WP_Hook;
		$pWPPost                = self::factory()->post->create_and_get( [
			'post_author'  => 1,
			'post_content' => '[oo_estate view="detail"]',
			'post_title'   => 'Detail View',
			'post_type'    => 'page',
		] );
		$wp_filter['save_post'] = $savePostBackup;
		$this->assertTrue($this->_pRedirectIfOldUrl->redirectDetailView(123, 'Show Title Url', true));
	}
}
