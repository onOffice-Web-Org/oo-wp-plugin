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
use onOffice\WPlugin\WP\WPQueryWrapper;
use WP_UnitTestCase;

class TestClassEstateDetailUrl
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
		$estateId = 123;

		$pInstance = $this->_pContainer->get(EstateDetailUrl::class);
		$url = 'https://www.onoffice.de/';
		$expectedUrl = $url.$estateId;

		$this->assertEquals($expectedUrl, $pInstance->createEstateDetailLink($url, $estateId));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testUrlWithGetParameter()
	{
		$estateId = 123;
		$pInstance = $this->_pContainer->get(EstateDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/?lang=en';
		$expectedUrl = 'https://www.onoffice.de/detail/123?lang=en';

		$this->assertEquals($expectedUrl, $pInstance->createEstateDetailLink($url, $estateId));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testUrlWithTitle()
	{
		add_option('onoffice-detail-view-showTitleUrl', true);
		$estateId = 123;
		$pInstance = $this->_pContainer->get(EstateDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/';
		$title = 'String Slug';
		$expectedUrl = 'https://www.onoffice.de/detail/123-string-slug';

		$this->assertEquals($expectedUrl, $pInstance->createEstateDetailLink($url, $estateId, $title));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testUrlWithTitleLimitCharacter()
	{
		add_option('onoffice-detail-view-showTitleUrl', true);
		$estateId = 123;
		$pInstance = $this->_pContainer->get(EstateDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/';
		$title = 'Human readable URLs showing ID and title';
		$expectedUrl = 'https://www.onoffice.de/detail/123-human-readable-urls-showing-id';

		$this->assertEquals($expectedUrl, $pInstance->createEstateDetailLink($url, $estateId, $title));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testUrlWithTitleNotSetOptionShowUrlAndTitle()
	{
		add_option('onoffice-detail-view-showTitleUrl', false);

		$estateId = 123;
		$pInstance = $this->_pContainer->get(EstateDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/';
		$title = 'Human readable URLs showing ID and title';
		$expectedUrl = 'https://www.onoffice.de/detail/123';

		$this->assertEquals($expectedUrl, $pInstance->createEstateDetailLink($url, $estateId, $title));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testUrlWithTitleAndParameter()
	{
		add_option('onoffice-detail-view-showTitleUrl', true);
		$estateId = 123;
		$pInstance = $this->_pContainer->get(EstateDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/?lang=en';
		$title = 'String Slug';
		$expectedUrl = 'https://www.onoffice.de/detail/123-string-slug?lang=en';

		$this->assertEquals($expectedUrl, $pInstance->createEstateDetailLink($url, $estateId, $title));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testGetUrlWithFilterTrueAndEnableOptionShowTitleWithParameter()
	{
		$pEstateRedirection = apply_filters('oo_is_detailpage_redirection', true);
		add_option('onoffice-detail-view-showTitleUrl', true);
		$estateId = 123;
		$title = 'test-title';
		$pInstance = $this->_pContainer->get(EstateDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/123?lang=en';
		$expectedUrl = 'https://www.onoffice.de/detail/123-test-title';
		$this->assertEquals($expectedUrl, $pInstance->getUrlWithEstateTitle($estateId, $title, $url, false, $pEstateRedirection));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testGetUrlWithFilterTrueAndDisableOptionShowTitleWithParameter()
	{
		$pEstateRedirection = apply_filters('oo_is_detailpage_redirection', true);
		add_option('onoffice-detail-view-showTitleUrl', false);
		$estateId = 123;
		$title = 'test-title';
		$pInstance = $this->_pContainer->get(EstateDetailUrl::class);
		$url = 'https://www.onoffice.de/detail/123?lang=en';
		$expectedUrl = 'https://www.onoffice.de/detail/123';
		$this->assertEquals($expectedUrl, $pInstance->getUrlWithEstateTitle($estateId, $title, $url, false, $pEstateRedirection));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testGetUrlWithFilterFalseAndDisableOptionShowTitleWithParameter()
	{
		$pEstateRedirection = apply_filters('oo_is_detailpage_redirection', false);
		add_option('onoffice-detail-view-showTitleUrl', false);
		$estateId = 123;
		$title = 'test-title';
		$pInstance = $this->_pContainer->get(EstateDetailUrl::class);

		$urlHasTitleWithoutParamster = 'https://www.onoffice.de/detail/123';
		$expectedUrlHasTitleWithoutParamster = 'https://www.onoffice.de/detail/123';
		$this->assertEquals($expectedUrlHasTitleWithoutParamster, $pInstance->getUrlWithEstateTitle($estateId, $title, $urlHasTitleWithoutParamster, false, $pEstateRedirection));

		$urlWithoutTitle = 'https://www.onoffice.de/detail/123?lang=en';
		$expectedUrlWithoutTitle = 'https://www.onoffice.de/detail/123?lang=en';
		$this->assertEquals($expectedUrlWithoutTitle, $pInstance->getUrlWithEstateTitle($estateId, $title, $urlWithoutTitle, false, $pEstateRedirection));
	}

	/**
	 * @throws \DI\DependencyException
	 * @throws \DI\NotFoundException
	 */
	public function testGetUrlWithFilterFalseAndEnableOptionShowTitleWithParameter()
	{
		$pEstateRedirection = apply_filters('oo_is_detailpage_redirection', false);
		add_option('onoffice-detail-view-showTitleUrl', true);
		$estateId = 123;
		$title = 'test-title';
		$pInstance = $this->_pContainer->get(EstateDetailUrl::class);

		$urlHasTitleWithoutParamster = 'https://www.onoffice.de/detail/123';
		$expectedUrlHasTitleWithoutParamster = 'https://www.onoffice.de/detail/123-test-title';
		$this->assertEquals($expectedUrlHasTitleWithoutParamster, $pInstance->getUrlWithEstateTitle($estateId, $title, $urlHasTitleWithoutParamster, false, $pEstateRedirection));

		$urlWithoutTitle = 'https://www.onoffice.de/detail/123/?lang=en';
		$expectedUrlWithoutTitle = 'https://www.onoffice.de/detail/123?lang=en';
		$this->assertEquals($expectedUrlWithoutTitle, $pInstance->getUrlWithEstateTitle($estateId, $title, $urlWithoutTitle, false, $pEstateRedirection));

		$urlHasTitleHasParamster = 'https://www.onoffice.de/detail/123-test-title';
		$expectedUrlHasTitleHasParamster = 'https://www.onoffice.de/detail/123-test-title';
		$this->assertEquals($expectedUrlHasTitleHasParamster, $pInstance->getUrlWithEstateTitle($estateId, $title, $urlHasTitleHasParamster, true, $pEstateRedirection));

		$urlHasTitle = 'https://www.onoffice.de/detail/123-test-title/?lang=en';
		$expectedUrlHasTitle = 'https://www.onoffice.de/detail/123-test-title';
		$this->assertEquals($expectedUrlHasTitle, $pInstance->getUrlWithEstateTitle($estateId, $title, $urlHasTitle, true, $pEstateRedirection));
	}
}