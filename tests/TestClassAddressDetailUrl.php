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
use onOffice\WPlugin\Controller\AddressDetailUrl;
use onOffice\WPlugin\WP\WPQueryWrapper;
use WP_UnitTestCase;

class TestClassAddressDetailUrl
	extends WP_UnitTestCase
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
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testUrlWithFolder()
	{
		$addressId = 123;

		$pInstance = $this->_pContainer->get(AddressDetailUrl::class);
		$url = 'https://www.onoffice.de/';
		$expectedUrl = $url.$addressId;

		$this->assertEquals($expectedUrl, $pInstance->createAddressDetailLink($url, $addressId));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testUrlWithGetParameter()
	{
		$addressId = 123;
		$pInstance = $this->_pContainer->get(AddressDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/?lang=en';
		$expectedUrl = 'https://www.onoffice.de/detail/123?lang=en';

		$this->assertEquals($expectedUrl, $pInstance->createAddressDetailLink($url, $addressId));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testAddressDetailUrl()
	{
		add_option('onoffice-address-detail-view-showInfoUserUrl', true);
		$addressId = 123;
		$pInstance = $this->_pContainer->get(AddressDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/';
		$title = 'vorname name company';
		$expectedUrl = 'https://www.onoffice.de/detail/123-vorname-name-company';

		$this->assertEquals($expectedUrl, $pInstance->createAddressDetailLink($url, $addressId, $title));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testAddressDetailUrlNotSetOptionShowUrlAndTitle()
	{
		add_option('onoffice-address-detail-view-showInfoUserUrl', false);

		$addressId = 123;
		$pInstance = $this->_pContainer->get(AddressDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/';
		$title = 'Human readable URLs showing ID and title';
		$expectedUrl = 'https://www.onoffice.de/detail/123';

		$this->assertEquals($expectedUrl, $pInstance->createAddressDetailLink($url, $addressId, $title));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testAddressDetailUrlAndParameter()
	{
		add_option('onoffice-address-detail-view-showInfoUserUrl', true);
		$addressId = 123;
		$pInstance = $this->_pContainer->get(AddressDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/?lang=en';
		$title = 'String Slug';
		$expectedUrl = 'https://www.onoffice.de/detail/123-string-slug?lang=en';

		$this->assertEquals($expectedUrl, $pInstance->createAddressDetailLink($url, $addressId, $title));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testGetUrlWithFilterTrueAndEnableOptionShowTitleWithParameter()
	{
		$pAddressRedirection = apply_filters('oo_is_address_detail_page_redirection', true);
		add_option('onoffice-address-detail-view-showInfoUserUrl', true);
		$addressId = 123;
		$title = 'test-title';
		$pInstance = $this->_pContainer->get(AddressDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/123?lang=en';
		$expectedUrl = 'https://www.onoffice.de/detail/123-test-title?lang=en';
		$this->assertEquals($expectedUrl, $pInstance->getUrlWithAddressTitle($addressId, $title, $url, false, $pAddressRedirection));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testGetUrlWithFilterTrueAndDisableOptionShowTitleWithParameter()
	{
		$pAddressRedirection = apply_filters('oo_is_address_detail_page_redirection', true);
		add_option('onoffice-address-detail-view-showInfoUserUrl', false);
		$addressId = 123;
		$title = 'test-title';
		$pInstance = $this->_pContainer->get(AddressDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/123?lang=en';
		$expectedUrl = 'https://www.onoffice.de/detail/123?lang=en';
		$this->assertEquals($expectedUrl, $pInstance->getUrlWithAddressTitle($addressId, $title, $url, false, $pAddressRedirection));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testGetUrlWithFilterFalseAndDisableOptionShowTitleWithParameter()
	{
		$pAddressRedirection = apply_filters('oo_is_address_detail_page_redirection', false);
		add_option('onoffice-address-detail-view-showInfoUserUrl', false);
		$addressId = 123;
		$title = 'test-title';
		$pInstance = $this->_pContainer->get(AddressDetailUrl::class);

		$urlHasTitleWithoutParamster = 'https://www.onoffice.de/detail/123';
		$expectedUrlHasTitleWithoutParamster = 'https://www.onoffice.de/detail/123';
		$this->assertEquals($expectedUrlHasTitleWithoutParamster, $pInstance->getUrlWithAddressTitle($addressId, $title, $urlHasTitleWithoutParamster, false, $pAddressRedirection));

		$urlWithoutTitle = 'https://www.onoffice.de/detail/123?lang=en';
		$expectedUrlWithoutTitle = 'https://www.onoffice.de/detail/123?lang=en';
		$this->assertEquals($expectedUrlWithoutTitle, $pInstance->getUrlWithAddressTitle($addressId, $title, $urlWithoutTitle, false, $pAddressRedirection));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testGetUrlWithFilterFalseAndEnableOptionShowTitleWithParameter()
	{
		$pAddressRedirection = apply_filters('oo_is_address_detail_page_redirection', false);
		add_option('onoffice-address-detail-view-showInfoUserUrl', true);
		$addressId = 123;
		$title = 'test-title';
		$pInstance = $this->_pContainer->get(AddressDetailUrl::class);

		$urlHasTitleWithoutParamster = 'https://www.onoffice.de/detail/123';
		$expectedUrlHasTitleWithoutParamster = 'https://www.onoffice.de/detail/123-test-title';
		$this->assertEquals($expectedUrlHasTitleWithoutParamster, $pInstance->getUrlWithAddressTitle($addressId, $title, $urlHasTitleWithoutParamster, false, $pAddressRedirection));

		$urlWithoutTitle = 'https://www.onoffice.de/detail/123?lang=en';
		$expectedUrlWithoutTitle = 'https://www.onoffice.de/detail/123?lang=en';
		$this->assertEquals($expectedUrlWithoutTitle, $pInstance->getUrlWithAddressTitle($addressId, $title, $urlWithoutTitle, false, $pAddressRedirection));

		$urlHasTitleHasParameter = 'https://www.onoffice.de/detail/123-test-title?lang=en';
		$expectedUrlHasTitleHasParameter = 'https://www.onoffice.de/detail/123-test-title?lang=en';
		$this->assertEquals($expectedUrlHasTitleHasParameter, $pInstance->getUrlWithAddressTitle($addressId, $title, $urlHasTitleHasParameter, true, $pAddressRedirection));

		$urlHasTitle = 'https://www.onoffice.de/detail/123-test-title?lang=en';
		$expectedUrlHasTitle = 'https://www.onoffice.de/detail/123-test-title?lang=en';
		$this->assertEquals($expectedUrlHasTitle, $pInstance->getUrlWithAddressTitle($addressId, $title, $urlHasTitle, true, $pAddressRedirection));
	}
}
