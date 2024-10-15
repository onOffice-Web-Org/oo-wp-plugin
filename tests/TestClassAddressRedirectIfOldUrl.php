<?php
/**
 *
 *    Copyright (C) 2024 onOffice GmbH
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
use onOffice\WPlugin\Utility\AddressRedirector;

class TestClassAddressRedirectIfOldUrl
	extends \WP_UnitTestCase
{
	/**
	 * @var AddressRedirector
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
		$wp->request       = 'address-detail-view/123';
		$pContainerBuilder = new ContainerBuilder;
		$pContainerBuilder->addDefinitions( ONOFFICE_DI_CONFIG_PATH );
		$this->_pContainer        = $pContainerBuilder->build();
		$this->_pRedirectIfOldUrl = $this->_pContainer->get( AddressRedirector::class );
		$_SERVER['REQUEST_URI'] = '/address-detail-view/123';
	}

	/**
	 *
	 */
	public function stestInstance()
	{
		$this->assertInstanceOf( AddressRedirector::class, $this->_pRedirectIfOldUrl );
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\AddressRedirector::getCurrentLink
	 */
	public function testGetCurrentLink()
	{
		$this->assertEquals( 'http://example.org/address-detail-view/123', $this->_pRedirectIfOldUrl->getCurrentLink() );
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\AddressRedirector::getUri
	 */
	public function testGetUri()
	{
		$this->assertEquals( 'address-detail-view/123', $this->_pRedirectIfOldUrl->getUri() );
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\AddressRedirector::redirectDetailView
	 */
	public function testRedirectDetailViewSameUrl()
	{
		add_option( 'onoffice-address-detail-view-showInfoUserUrl', true );
		global $wp;
		$wp->request = 'address-detail-view/123-vorname-name-company';
		$_SERVER['REQUEST_URI'] = '/address-detail-view/123-vorname-name-company';
		$this->set_permalink_structure( '/%postname%/' );
		$this->assertNull($this->_pRedirectIfOldUrl->redirectDetailView(123, 'Vorname Name Company', true));
	}

	/**
	 * @covers \onOffice\WPlugin\Utility\AddressRedirector::redirectDetailView
	 */
	public function testRedirectDetailViewNotMatchRule()
	{
		global $wp;
		$wp->request = 'address-detail-view/abc-123-vorname-name-company-url';
		$_SERVER['REQUEST_URI'] = '/address-detail-view/abc-123-vorname-name-company-url';
		$this->set_permalink_structure( '/%postname%/' );
		$this->assertTrue($this->_pRedirectIfOldUrl->redirectDetailView(123, 'Vorname Name Company', true));
	}


	/**
	 * @return void
	 */

	public function testRedirectDetailViewWithParrentPageAndUrlNotMatchRule()
	{
		global $wp;
		$wp->request = 'e1/address-detail-view/abc-123-vorname-name-company-url';
		$_SERVER['REQUEST_URI'] = '/e1/address-detail-view/abc-123-vorname-name-company-url';
		$this->set_permalink_structure( '/%postname%/' );
		$this->assertTrue($this->_pRedirectIfOldUrl->redirectDetailView(123, 'Vorname Name Company', true));
	}
}
