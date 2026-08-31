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
use onOffice\WPlugin\Controller\AddressDetailUrl;
use onOffice\WPlugin\Controller\Redirector\AddressRedirector;
use WP_UnitTestCase;

/**
 * Address titles containing special characters must never produce a 301 redirect loop.
 */
class TestClassAddressRedirectSpecialCharacters
	extends WP_UnitTestCase
{
	use RedirectChainTrait;

	const ADDRESS_ID = 456;

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

		update_option('onoffice-address-detail-view-showInfoUserUrl', true);
		$this->set_permalink_structure('/%postname%/');
	}

	/**
	 * @dataProvider dataServerBehaviours
	 * @covers \onOffice\WPlugin\Controller\Redirector\AddressRedirector::redirectDetailView
	 *
	 * @param callable $serverBehaviour
	 */
	public function testDetailUrlOfCurrentTitleDoesNotRedirect(callable $serverBehaviour)
	{
		$title = 'Immobilienbüro Groß & Söhne';
		$requestUri = '/ansprechpartner/' . self::ADDRESS_ID
			. $this->_pContainer->get(AddressDetailUrl::class)->getSanitizeTitle($title);

		$this->assertSame([], $this->followAddressRedirects($requestUri, $title, $serverBehaviour));
	}

	/**
	 * @dataProvider dataServerBehaviours
	 * @covers \onOffice\WPlugin\Controller\Redirector\AddressRedirector::redirectDetailView
	 *
	 * @param callable $serverBehaviour
	 */
	public function testPercentEncodedLegacyUrlIsRedirectedOnce(callable $serverBehaviour)
	{
		$requestUri = '/ansprechpartner/' . self::ADDRESS_ID . '-buero-%c2%b2';

		$redirects = $this->followAddressRedirects($requestUri, 'Buero ²', $serverBehaviour);

		$this->assertSame([home_url('/ansprechpartner/' . self::ADDRESS_ID . '-buero-2')], $redirects);
	}

	/**
	 * @param string $requestUri
	 * @param string $title
	 * @param callable $serverBehaviour
	 *
	 * @return string[]
	 */
	private function followAddressRedirects(string $requestUri, string $title, callable $serverBehaviour): array
	{
		return $this->followRedirects($requestUri, $serverBehaviour, function () use ($title) {
			$pRedirectWrapper = new RedirectWrapperMocker();
			$pAddressRedirector = new AddressRedirector(
				$this->_pContainer->get(AddressDetailUrl::class),
				$pRedirectWrapper
			);
			$pAddressRedirector->redirectDetailView(self::ADDRESS_ID, $title, true);
			$redirects = $pRedirectWrapper->getRedirects();

			return empty($redirects) ? null : (string)end($redirects);
		});
	}
}
