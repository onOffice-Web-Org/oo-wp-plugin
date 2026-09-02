<?php
/**
 *
 *    Copyright (C) 2026 onOffice GmbH
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
use onOffice\WPlugin\Controller\Redirector\EstateRedirector;
use onOffice\WPlugin\Utility\UrlHelper;
use WP_UnitTestCase;

/**
 * Estate titles containing special characters must never produce a 301 redirect loop, no matter
 * in which encoding the web server hands the requested path over to WordPress.
 */
class TestClassEstateRedirectSpecialCharacters
	extends WP_UnitTestCase
{
	use RedirectChainTrait;

	const ESTATE_ID = 123;

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

		update_option('onoffice-detail-view-showTitleUrl', true);
		$this->set_permalink_structure('/%postname%/');
	}

	/**
	 * @return array
	 */
	public function dataTitles(): array
	{
		return [
			'square meters' => ['Wohnung 120 m²'],
			'umlauts' => ['Schöne Wohnung am See'],
			'sharp s' => ['Große Straße'],
			'spaces only' => ['Haus am See'],
			'fraction' => ['Haus ½ Etage'],
			'non latin' => ['мой дом'],
		];
	}

	/**
	 * @return array
	 */
	public function dataTitlesAndServerBehaviours(): array
	{
		$dataSets = [];
		foreach ($this->dataTitles() as $titleName => $title) {
			foreach ($this->dataServerBehaviours() as $serverName => $serverBehaviour) {
				$dataSets[$titleName . ' / ' . $serverName] = [$title[0], $serverBehaviour[0]];
			}
		}

		return $dataSets;
	}

	/**
	 * @dataProvider dataTitlesAndServerBehaviours
	 * @covers \onOffice\WPlugin\Controller\Redirector\EstateRedirector::redirectDetailView
	 *
	 * @param string $title
	 * @param callable $serverBehaviour
	 */
	public function testDetailUrlOfCurrentTitleDoesNotRedirect(string $title, callable $serverBehaviour)
	{
		$requestUri = '/immobilien/' . self::ESTATE_ID . $this->getSanitizeTitle($title);

		$this->assertSame([], $this->followEstateRedirects($requestUri, $title, $serverBehaviour));
	}

	/**
	 * @dataProvider dataTitlesAndServerBehaviours
	 * @covers \onOffice\WPlugin\Controller\Redirector\EstateRedirector::redirectDetailView
	 *
	 * @param string $title
	 * @param callable $serverBehaviour
	 */
	public function testDetailUrlOfOutdatedTitleEndsUpAtTheCurrentTitle(string $title, callable $serverBehaviour)
	{
		$requestUri = '/immobilien/' . self::ESTATE_ID . '-alter-titel';
		$expectedUrl = home_url('/immobilien/' . self::ESTATE_ID . $this->getSanitizeTitle($title));

		$redirects = $this->followEstateRedirects($requestUri, $title, $serverBehaviour);

		$this->assertNotEmpty($redirects, 'the outdated title should be redirected');
		$this->assertTrue(
			UrlHelper::isSameLocation((string)end($redirects), $expectedUrl),
			'last redirect was ' . end($redirects) . ', expected ' . $expectedUrl
		);
	}

	/**
	 * The URLs of estates whose title used to be percent encoded are still in search indexes and
	 * must be redirected to the current URL exactly once.
	 *
	 * @dataProvider dataServerBehaviours
	 * @covers \onOffice\WPlugin\Controller\Redirector\EstateRedirector::redirectDetailView
	 *
	 * @param callable $serverBehaviour
	 */
	public function testPercentEncodedLegacyUrlIsRedirectedOnce(callable $serverBehaviour)
	{
		$requestUri = '/immobilien/' . self::ESTATE_ID . '-wohnung-120-m%c2%b2';

		$redirects = $this->followEstateRedirects($requestUri, 'Wohnung 120 m²', $serverBehaviour);

		$this->assertSame([home_url('/immobilien/' . self::ESTATE_ID . '-wohnung-120-m2')], $redirects);
	}

	/**
	 * @dataProvider dataServerBehaviours
	 * @covers \onOffice\WPlugin\Controller\Redirector\EstateRedirector::redirectDetailView
	 *
	 * @param callable $serverBehaviour
	 */
	public function testUrlWithoutTitleIsRedirectedToTheTitleUrl(callable $serverBehaviour)
	{
		$requestUri = '/immobilien/' . self::ESTATE_ID;

		$redirects = $this->followEstateRedirects($requestUri, 'Wohnung 120 m²', $serverBehaviour);

		$this->assertSame([home_url('/immobilien/' . self::ESTATE_ID . '-wohnung-120-m2')], $redirects);
	}

	/**
	 * @dataProvider dataServerBehaviours
	 * @covers \onOffice\WPlugin\Controller\Redirector\EstateRedirector::redirectDetailView
	 *
	 * @param callable $serverBehaviour
	 */
	public function testDetailUrlWithSearchParametersDoesNotRedirect(callable $serverBehaviour)
	{
		$requestUri = '/immobilien/' . self::ESTATE_ID . '-wohnung-120-m2?oldFilter=1';

		$this->assertSame([], $this->followEstateRedirects($requestUri, 'Wohnung 120 m²', $serverBehaviour));
	}

	/**
	 * @param string $title
	 *
	 * @return string
	 */
	private function getSanitizeTitle(string $title): string
	{
		return $this->_pContainer->get(EstateDetailUrl::class)->getSanitizeTitle($title);
	}

	/**
	 * @param string $requestUri
	 * @param string $title
	 * @param callable $serverBehaviour
	 *
	 * @return string[]
	 */
	private function followEstateRedirects(string $requestUri, string $title, callable $serverBehaviour): array
	{
		return $this->followRedirects($requestUri, $serverBehaviour, function () use ($title) {
			$pRedirectWrapper = new RedirectWrapperMocker();
			$pEstateRedirector = new EstateRedirector(
				$this->_pContainer->get(EstateDetailUrl::class),
				$pRedirectWrapper
			);
			$pEstateRedirector->redirectDetailView(self::ESTATE_ID, $title, true);
			$redirects = $pRedirectWrapper->getRedirects();

			return empty($redirects) ? null : (string)end($redirects);
		});
	}
}
