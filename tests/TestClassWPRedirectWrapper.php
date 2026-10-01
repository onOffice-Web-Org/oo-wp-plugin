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

use WP_UnitTestCase;

class TestClassWPRedirectWrapper
	extends WP_UnitTestCase
{
	/**
	 * @return array
	 */
	public function dataUrlsOfTheCurrentRequest(): array
	{
		return [
			'identical' => [
				'/immobilien/123-wohnung-m2',
				'http://example.org/immobilien/123-wohnung-m2',
			],
			'missing trailing slash' => [
				'/immobilien/123-wohnung-m2/',
				'http://example.org/immobilien/123-wohnung-m2',
			],
			'percent encoded request' => [
				'/immobilien/123-wohnung-m%c2%b2',
				'http://example.org/immobilien/123-wohnung-m²',
			],
			'double encoded target' => [
				'/immobilien/123-wohnung-m%c2%b2',
				'http://example.org/immobilien/123-wohnung-m%25c2%25b2',
			],
			'upper case octets' => [
				'/immobilien/123-wohnung-m%C2%B2',
				'http://example.org/immobilien/123-wohnung-m%c2%b2',
			],
			'other host' => [
				'/immobilien/123-wohnung-m2',
				'https://www.example.com/immobilien/123-wohnung-m2',
			],
			'same query' => [
				'/immobilien/123-wohnung-m2?filter=1',
				'http://example.org/immobilien/123-wohnung-m2?filter=1',
			],
		];
	}

	/**
	 * @dataProvider dataUrlsOfTheCurrentRequest
	 * @covers \onOffice\WPlugin\WP\WPRedirectWrapper::redirect
	 *
	 * @param string $requestUri
	 * @param string $url
	 */
	public function testRedirectToTheCurrentRequestIsSuppressed(string $requestUri, string $url)
	{
		$_SERVER['REQUEST_URI'] = $requestUri;
		$pRedirectWrapper = new RedirectWrapperMocker();

		$pRedirectWrapper->redirect($url);

		$this->assertSame([], $pRedirectWrapper->getRedirects());
	}

	/**
	 * @return array
	 */
	public function dataUrlsOfOtherLocations(): array
	{
		return [
			'other title' => [
				'/immobilien/123-alter-titel',
				'http://example.org/immobilien/123-wohnung-m2',
			],
			'title added' => [
				'/immobilien/123',
				'http://example.org/immobilien/123-wohnung-m2',
			],
			'other estate' => [
				'/immobilien/123-wohnung-m2',
				'http://example.org/immobilien/124-wohnung-m2',
			],
			'other query' => [
				'/immobilien/123-wohnung-m2?filter=1',
				'http://example.org/immobilien/123-wohnung-m2',
			],
		];
	}

	/**
	 * @dataProvider dataUrlsOfOtherLocations
	 * @covers \onOffice\WPlugin\WP\WPRedirectWrapper::redirect
	 *
	 * @param string $requestUri
	 * @param string $url
	 */
	public function testRedirectToAnotherLocationIsPerformed(string $requestUri, string $url)
	{
		$_SERVER['REQUEST_URI'] = $requestUri;
		$pRedirectWrapper = new RedirectWrapperMocker();

		$pRedirectWrapper->redirect($url);

		$this->assertSame([$url], $pRedirectWrapper->getRedirects());
	}
}
