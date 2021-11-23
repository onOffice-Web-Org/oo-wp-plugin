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

use DI\ContainerBuilder;
use onOffice\WPlugin\AddressList;
use onOffice\WPlugin\Controller\EstateDetailUrl;
use onOffice\WPlugin\Factory\AddressListFactory;
use onOffice\WPlugin\Utility\RedirectIfOldUrl;

class TestClassRedirectIfOldUrl
	extends \WP_UnitTestCase
{
	/**
	 * @var RedirectIfOldUrl
	 */
	private $_pRedirectIfOldUrl;

	/**
	 * @before
	 */
	public function prepare()
	{
		$this->_pRedirectIfOldUrl = new RedirectIfOldUrl();
		global $wp;
		$wp->request = 'detail-view/123';
	}

	/**
	 *
	 */
	public function testInstance()
	{
		$this->assertInstanceOf(RedirectIfOldUrl::class, $this->_pRedirectIfOldUrl);
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\RedirectIfOldUrl::getCurrentLink
	 */
	public function testGetCurrentLink()
	{
		$this->assertEquals('http://example.org/detail-view/123', $this->_pRedirectIfOldUrl->getCurrentLink());
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\RedirectIfOldUrl::getUri
	 */
	public function testGetUri()
	{
		$this->assertEquals('detail-view/123', $this->_pRedirectIfOldUrl->getUri());
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\RedirectIfOldUrl::redirectDetailView
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
		$this->assertTrue($this->_pRedirectIfOldUrl->redirectDetailView($pWPPost->ID, 123, 'Show Title Url'));
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\RedirectIfOldUrl::redirectDetailView
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
